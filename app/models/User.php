<?php
namespace App\Models;

class User {
    private $conn;
    private $table = "users";

    public function __construct($db) {
        $this->conn = $db;
    }
    public function register($name, $email, $password) {
        $sql = "INSERT INTO {$this->table} (name, email, password, role, created_at) 
                VALUES (?, ?, ?, 'mahasiswa', NOW())";
        $stmt = $this->conn->prepare($sql);
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt->bind_param("sss", $name, $email, $hash);
        return $stmt->execute();
    }
    public function linkGoogle($name, $email, $googleId, $googleAvatarUrl = null) {
        $u = $this->getByEmail($email);
        if ($u) {
            if ($googleAvatarUrl) {
                $sql = "UPDATE {$this->table} 
                        SET google_id = ?, google_avatar_url = ?, is_verified = 1 
                        WHERE email = ?";
                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param("sss", $googleId, $googleAvatarUrl, $email);
            } else {
                $sql = "UPDATE {$this->table} 
                        SET google_id = ?, is_verified = 1 
                        WHERE email = ?";
                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param("ss", $googleId, $email);
            }
            return $stmt->execute();
        } else {
            if ($googleAvatarUrl) {
                $sql = "INSERT INTO {$this->table} 
                        (name, email, google_id, google_avatar_url, is_verified, role, created_at) 
                        VALUES (?, ?, ?, ?, 1, 'mahasiswa', NOW())";
                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param("ssss", $name, $email, $googleId, $googleAvatarUrl);
            } else {
                $sql = "INSERT INTO {$this->table} 
                        (name, email, google_id, is_verified, role, created_at) 
                        VALUES (?, ?, ?, 1, 'mahasiswa', NOW())";
                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param("sss", $name, $email, $googleId);
            }
            return $stmt->execute();
        }
    }

    public function getByEmail($email) {
        $sql = "SELECT * FROM {$this->table} WHERE email = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function verifyEmail($email) {
        $sql = "UPDATE {$this->table} SET is_verified = 1 WHERE email = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $email);
        return $stmt->execute();
    }

    public function loginByEmail($email, $password) {
        $u = $this->getByEmail($email);
        if(!$u) return false;

        if(!$u['is_verified']) return "NOT_VERIFIED";
        if (is_null($u['password']) && !empty($u['google_id'])) {
            return false;
        }
        if(!password_verify($password, $u['password'])) return false;

        return $u;
    }
}
