<?php
namespace App\Models;

class OTP {
    private $conn;
    private $table = "otp_codes";

    public function __construct($db){ $this->conn = $db; }

    public function create($email, $code, $minutes = 5) {
        $sql = "INSERT INTO {$this->table} (email, otp_code, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL ? MINUTE))";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssi", $email, $code, $minutes);
        return $stmt->execute();
    }

    public function validate($email, $code) {
        $sql = "SELECT * FROM {$this->table} WHERE email = ? AND otp_code = ? AND is_used = 0 AND expires_at > NOW() LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $email, $code);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function markUsed($id) {
        $sql = "UPDATE {$this->table} SET is_used = 1 WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function invalidateAllByEmail($email) {
        $sql = "UPDATE {$this->table} SET is_used = 1 WHERE email = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $email);
        return $stmt->execute();
    }
}
