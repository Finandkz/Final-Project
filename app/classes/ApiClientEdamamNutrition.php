<?php
namespace App\Classes;

use App\Helpers\Env;
use InvalidArgumentException;
use RuntimeException;

class ApiClientEdamamNutrition
{
    private $baseUrl;
    private $appId;
    private $appKey;
    private $cachePath;
    private $ttl;

    public function __construct()
    {
        if (class_exists('Env')) {
            Env::load();
        }

        $this->baseUrl = Env::get('EDAMAM_NUTRITION_URL', 'https://api.edamam.com/api/nutrition-details');
        $this->appId   = Env::get('EDAMAM_NUTRITION_APP_ID');
        $this->appKey  = Env::get('EDAMAM_NUTRITION_APP_KEY');

        $this->ttl     = (int) Env::get('NUTRITION_CACHE_TTL', Env::get('CACHE_TTL', 3600));

        $this->cachePath = __DIR__ . '/../../cache/nutrition/';
        if (!is_dir($this->cachePath)) {
            @mkdir($this->cachePath, 0777, true);
        }
    }

    public function analyzeFromText(string $ingredientsText): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $ingredientsText);
        $ingredients = array_values(array_filter(array_map('trim', $lines)));

        if (empty($ingredients)) {
            throw new InvalidArgumentException('The list of ingredients must not be empty.');
        }

        return $this->fetch($ingredients);
    }

    private function fetch(array $ingredients): array
    {
        $cacheFile = $this->getCacheFile($ingredients);

        if (file_exists($cacheFile)) {
            $modified = filemtime($cacheFile);
            if ($this->ttl > 0 && (time() - $modified < $this->ttl)) {
                $cached = @file_get_contents($cacheFile);
                $decoded = json_decode($cached, true);
                if (is_array($decoded)) {
                    return $decoded;
                }
            }
        }

        if (empty($this->appId) || empty($this->appKey)) {
            throw new RuntimeException('EDAMAM_APP_ID atau EDAMAM_APP_KEY belum diset di .env');
        }

        $data = $this->requestApi($ingredients);

        if (is_dir($this->cachePath) && is_writable($this->cachePath)) {
            @file_put_contents($cacheFile, json_encode($data));
        }

        return $data;
    }

    private function getCacheFile(array $ingredients): string
    {
        $keyData = [
            'url'  => $this->baseUrl,
            'id'   => $this->appId,
            'ingr' => $ingredients,
        ];

        $hash = md5(json_encode($keyData));
        return $this->cachePath . 'nut_' . $hash . '.json';
    }

    private function requestApi(array $ingredients): array
    {
        $url = rtrim($this->baseUrl, '/') .
            '?app_id=' . urlencode($this->appId) .
            '&app_key=' . urlencode($this->appKey);

        $payload = json_encode(['ingr' => $ingredients]);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_USERAGENT      => 'Mealify-NutritionClient/1.0',
        ]);

        $response = curl_exec($ch);
        $errNo    = curl_errno($ch);
        $errMsg   = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($errNo !== 0) {
            throw new RuntimeException('Gagal menghubungi API Edamam: ' . $errMsg);
        }

        $data = json_decode($response, true);

        if ($httpCode >= 400 || !is_array($data)) {
            throw new RuntimeException('API Edamam mengembalikan error (HTTP ' . $httpCode . ').');
        }

        return $data;
    }
}
