<?php
namespace App\Classes;
use App\Helpers\Env;

class ApiClientEdamam{
    private $baseUrl;
    private $appId;
    private $appKey;
    private $cachePath;
    private $ttl;

    public function __construct(){
        if (class_exists('Env')) {
            Env::load();
        }
        $this->baseUrl = Env::get(
            "EDAMAM_RECIPE_API_URL",
            "https://api.edamam.com/api/recipes/v2"
        );
        $this->appId  = Env::get("EDAMAM_RECIPE_APP_ID");
        $this->appKey = Env::get("EDAMAM_RECIPE_APP_KEY");
        $this->ttl       = Env::get("CACHE_TTL", 3600);
        $this->cachePath = __DIR__ . "/../../cache/";

        if (!is_dir($this->cachePath)) {
            @mkdir($this->cachePath, 0777, true);
        }
    }

    private function buildUrl($params){
        if (!empty($params["uri"])) {
            $query = [
                "type"    => "public",
                "app_id"  => $this->appId,
                "app_key" => $this->appKey,
                "uri"     => $params["uri"],
            ];
            return rtrim($this->baseUrl, "/") . "/by-uri?" . http_build_query($query);
        }

        $query = [
            "type"    => "public",
            "app_id"  => $this->appId,
            "app_key" => $this->appKey,
            "q"       => $params["q"] ?? "",
        ];

        if (!empty($params["diet"])) {
            $query["diet"] = $params["diet"];
        }

        if (!empty($params["health"])) {
            $query["health"] = $params["health"];
        }

        return rtrim($this->baseUrl, "/") . "?" . http_build_query($query);
    }
    private function getCacheFile($params)
    {
        return $this->cachePath . "cache_" . md5(json_encode($params)) . ".json";
    }

    public function fetch($params)
    {
        $cacheFile = $this->getCacheFile($params);
        $debugUrl = $this->buildUrl($params);
        @file_put_contents($this->cachePath . "last_recipe_url.txt", $debugUrl);
        if (file_exists($cacheFile)) {
            $modified = filemtime($cacheFile);
            if ($this->ttl > 0 && (time() - $modified < (int)$this->ttl)) {
                $cached = json_decode(file_get_contents($cacheFile), true);
                if (is_array($cached)) {
                    return $cached;
                }
            }
        }
        if (empty($this->appId) || empty($this->appKey)) {
            return ["hits" => []];
        }
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL            => $debugUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_USERAGENT      => "Mealify-ApiClient/1.0",
        ]);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($httpCode >= 400 || !$response) {
            return ["hits" => []];
        }

        $json = json_decode($response, true);
        if (!is_array($json)) {
            return ["hits" => []];
        }
        @file_put_contents($cacheFile, json_encode($json));
        return $json;
    }
}
