<?php
namespace App\Controllers;

use App\Helpers\Session;
use App\Config\Database;
use App\Helpers\Env;

class AdminUsersController
{
    private $conn;
    private $admin;

    public function __construct()
    {
        Session::start();
        $this->admin = Session::get('user');

        if (!$this->admin || ($this->admin['role'] ?? '') !== 'admin') {
            header('Location: ../public/login.php');
            exit;
        }

        $db = new Database();
        $this->conn = $db->connect();
    }

    public function list(): array
    {
        $stmt = $this->conn->prepare(
            "SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC"
        );
        $stmt->execute();
        $users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $users;
    }

    public function get(int $id): ?array
    {
        $stmt = $this->conn->prepare(
            "SELECT id, name, email, role FROM users WHERE id = ? LIMIT 1"
        );
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $user ?: null;
    }

    public function update(int $id, array $data): array
    {
        $current = $this->get($id);

        if (!$current) {
            return ['ok' => false, 'errors' => ['User not found']];
        }

        if ($current['role'] !== 'admin') {
            return ['ok' => false, 'errors' => ['Editing user accounts is not permitted.']];
        }

        $name = trim($data['name'] ?? '');
        $email = trim($data['email'] ?? '');
        $password = trim($data['password'] ?? '');

        if ($name === '') return ['ok' => false, 'errors' => ['Name must be filled in']];
        if (!filter_var($email, FILTER_VALIDATE_EMAIL))
            return ['ok' => false, 'errors' => ['Invalid email']];

        if ($password !== '') {
            if (strlen($password) < 8 || !preg_match('/[A-Z]/', $password)) {
                return ['ok' => false, 'errors' => ['Passwords must be at least 8 characters long and contain at least 1 capital letter.']];
            }
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->conn->prepare(
                "UPDATE users SET name=?, email=?, password=? WHERE id=?"
            );
            $stmt->bind_param("sssi", $name, $email, $hash, $id);
        } else {
            $stmt = $this->conn->prepare(
                "UPDATE users SET name=?, email=? WHERE id=?"
            );
            $stmt->bind_param("ssi", $name, $email, $id);
        }

        $ok = $stmt->execute();
        $stmt->close();

        return $ok
            ? ['ok' => true, 'errors' => []]
            : ['ok' => false, 'errors' => ['Failed to save data']];
    }

    public function delete(int $id): bool
    {
        if ($id === (int)($this->admin['id'] ?? 0)) {
            return false;
        }

        $stmt = $this->conn->prepare("DELETE FROM users WHERE id=?");
        $stmt->bind_param("i", $id);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }
}
