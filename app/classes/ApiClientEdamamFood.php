<?php
namespace App\Classes;
use App\Helpers\Env;
class ApiClientEdamamFood{
    private $baseUrl;
    private $appId;
    private $appKey;
    private $cachePath;
    private $ttl;

    public function __construct(){
        Env::load();
        $this->baseUrl = "https://api.edamam.com/api/food-database/v2/parser";
        $this->appId  = Env::get('EDAMAM_FOOD_APP_ID', Env::get('EDAMAM_RECIPE_APP_ID'));
        $this->appKey = Env::get('EDAMAM_FOOD_APP_KEY', Env::get('EDAMAM_RECIPE_APP_KEY'));
        $this->ttl = (int) Env::get('CACHE_TTL', 3600);
        $this->cachePath = __DIR__ . "/../../cache/";
        if (!is_dir($this->cachePath)) @mkdir($this->cachePath, 0755, true);
    }

    private function cacheFile($q) {
        return $this->cachePath . 'food_' . md5($q) . '.json';
    }
    public function searchFoodRaw(string $query): array{
        $q = trim($query);
        if ($q === '') return [];

        $cache = $this->cacheFile($q);
        if (file_exists($cache) && ($this->ttl <= 0 || time() - filemtime($cache) < $this->ttl)) {
            $c = @file_get_contents($cache);
            $d = json_decode($c, true);
            if (is_array($d)) return $d;
        }
        if (empty($this->appId) || empty($this->appKey)) {
            return [];
        }

        $url = $this->baseUrl . '?app_id=' . urlencode($this->appId)
             . '&app_key=' . urlencode($this->appKey)
             . '&ingr=' . urlencode($q);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_USERAGENT => 'Mealify-FoodClient/1.0',
        ]);
        $resp = curl_exec($ch);
        $err = curl_error($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($err || $code >= 400 || !$resp) {
            return [];
        }

        $json = json_decode($resp, true);
        if (is_array($json)) {
            @file_put_contents($cache, json_encode($json));
            return $json;
        }
        return [];
    }

    public function getHints(string $query): array{
        $raw = $this->searchFoodRaw($query);
        $hints = [];

        if (!empty($raw['hints']) && is_array($raw['hints'])) {
            foreach ($raw['hints'] as $h) {
                $food = $h['food'] ?? null;
                if (!$food) continue;
                $label = $food['label'] ?? '';
                $nut = $food['nutrients'] ?? [];
                $hints[] = [
                    'label' => $label,
                    'nutrients' => $nut,
                    'foodId' => $food['foodId'] ?? null,
                    'category' => $food['category'] ?? null,
                ];
            }
        }

        if (!empty($raw['parsed']) && is_array($raw['parsed'])) {
            foreach ($raw['parsed'] as $p) {
                if (!empty($p['food'])) {
                    $f = $p['food'];
                    $label = $f['label'] ?? '';
                    $nut = $f['nutrients'] ?? [];
                    $hints[] = [
                        'label' => $label,
                        'nutrients' => $nut,
                        'foodId' => $f['foodId'] ?? null,
                        'category' => $f['category'] ?? null,
                    ];
                }
            }
        }

        return $hints;
    }

    public function chooseBest(string $query, array $hints): array{
        $q = mb_strtolower(trim($query));
        if ($q === '' || empty($hints)) {
            return ['label' => null, 'nutrients' => [], 'confidence' => 0];
        }
        $best = null;
        $bestScore = -1;
        foreach ($hints as $h) {
            $label = mb_strtolower($h['label'] ?? '');
            if ($label === '') continue;
            $score = 0;
            if ($label === $q) {
                $score = 100;
            } elseif (mb_strpos($label, $q) !== false || mb_strpos($q, $label) !== false) {
                $score = 80;
            } else {
                similar_text($q, $label, $percent);
                $lev = levenshtein($q, $label);
                $maxLen = max(mb_strlen($q), mb_strlen($label));
                $levScore = ($maxLen > 0) ? (1 - ($lev / $maxLen)) * 100 : 0;
                $score = (0.7 * $percent) + (0.3 * $levScore);
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $h;
            }
        }

        $confidence = (int) round($bestScore);
        return [
            'label' => $best['label'] ?? null,
            'nutrients' => $best['nutrients'] ?? [],
            'confidence' => $confidence
        ];
    }

    public function getNutrientsBest(string $query): array{
        $hints = $this->getHints($query);
        if (empty($hints)) {
            return ['cal'=>null,'prot'=>null,'carb'=>null,'fat'=>null,'label'=>null,'confidence'=>0];
        }

        $chosen = $this->chooseBest($query, $hints);
        $nut = $chosen['nutrients'] ?? [];
        $cal = null;
        if (isset($nut['ENERC_KCAL'])) $cal = (float)$nut['ENERC_KCAL'];
        elseif (isset($nut['ENERC_KCAL']['quantity'])) $cal = (float)$nut['ENERC_KCAL']['quantity'];
        if ($cal === null && isset($nut['energy'])) $cal = (float)$nut['energy'];

        $prot = isset($nut['PROCNT']) ? (float)$nut['PROCNT'] : (isset($nut['PROCNT']['quantity']) ? (float)$nut['PROCNT']['quantity'] : null);
        $carb = isset($nut['CHOCDF']) ? (float)$nut['CHOCDF'] : (isset($nut['CHOCDF']['quantity']) ? (float)$nut['CHOCDF']['quantity'] : null);
        $fat  = isset($nut['FAT']) ? (float)$nut['FAT'] : (isset($nut['FAT']['quantity']) ? (float)$nut['FAT']['quantity'] : null);

        return [
            'cal' => $cal !== null ? (int) round($cal) : null,
            'prot'=> $prot !== null ? round($prot,2) : null,
            'carb'=> $carb !== null ? round($carb,2) : null,
            'fat' => $fat !== null ? round($fat,2) : null,
            'label'=> $chosen['label'] ?? null,
            'confidence' => $chosen['confidence'] ?? 0
        ];
    }
}
