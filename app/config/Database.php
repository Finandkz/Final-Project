<?php
namespace App\Config;

use mysqli;
use App\Helpers\Env;

class Database {
    public function connect() {
        Env::load();

        $conn = new mysqli(
            Env::get("DB_HOST"),
            Env::get("DB_USER"),
            Env::get("DB_PASS"),
            Env::get("DB_NAME")
        );

        if ($conn->connect_error) {
            $logFile = __DIR__ . '/../../logs/error.log';
            if (!is_dir(dirname($logFile))) {
                @mkdir(dirname($logFile), 0777, true);
            }
            error_log('[' . date('Y-m-d H:i:s') . '] Database Connection Error: ' . $conn->connect_error . PHP_EOL, 3, $logFile);
            http_response_code(503);
            if (is_file(__DIR__ . '/../../public/maintenance.php')) {
                require_once __DIR__ . '/../../public/maintenance.php';
            } else {
                echo "<h1>Sistem Sedang Maintenance</h1><p>Maaf, silakan coba lagi nanti.</p>";
            }
            exit;
        }

        $conn->set_charset("utf8mb4");
        return $conn;
    }
}
