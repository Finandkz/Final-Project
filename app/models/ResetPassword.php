<?php
namespace App\Models;

class ResetPassword {
    private $conn;
    private $table = "reset_password";

    public function __construct($db){ $this->conn = $db; }

    public function createToken($email, $token, $minutes = 30) {
        $sql = "INSERT INTO {$this->table} (email, token, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL ? MINUTE))";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssi", $email, $token, $minutes);
        return $stmt->execute();
    }

    public function validateToken($token) {
        $sql = "SELECT * FROM {$this->table} WHERE token = ? AND is_used = 0 AND expires_at > NOW() LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $token);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function markUsed($id) {
        $sql = "UPDATE {$this->table} SET is_used = 1 WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
