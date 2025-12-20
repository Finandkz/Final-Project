<?php
namespace App\Controllers;

use App\Helpers\Session;
use App\Config\Database;
use App\Helpers\Env;

class AdminNotificationsController
{
    private $conn;
    private $admin;

    public function __construct()
    {
        Session::start();
        $this->admin = Session::get('user');
        if (!$this->admin || ($this->admin['role'] ?? '') !== 'admin') {
            header("Location: ../public/login.php");
            exit;
        }

        Env::load();
        $db = new Database();
        $this->conn = $db->connect();
    }

    public function all(): array
    {
        $stmt = $this->conn->prepare("SELECT * FROM admin_notifications ORDER BY created_at DESC");
        $stmt->execute();
        $res = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $res;
    }

    public function find(int $id): ?array
    {
        $stmt = $this->conn->prepare("SELECT * FROM admin_notifications WHERE id = ? LIMIT 1");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $r = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $r ?: null;
    }

    public function save(array $data): array
    {
        $errors = [];
        $id = isset($data['id']) && $data['id'] !== '' ? (int)$data['id'] : null;
        $name = trim($data['name'] ?? '');
        $title = trim($data['title'] ?? '');
        $body = trim($data['body'] ?? '');
        $send_time = trim($data['send_time'] ?? '');
        $active = isset($data['active']) && ($data['active'] == '1' || $data['active'] === 1) ? 1 : 0;

        if ($name === '') $errors[] = 'The template name must be filled in.';
        if ($title === '') $errors[] = 'Title must be filled in';
        if ($body === '') $errors[] = 'Required fields must be filled in';
        if ($send_time === '') $errors[] = 'The delivery time must be filled in. (HH:MM)';

        if (!$errors) {
            $t = date_create_from_format('H:i', $send_time) ?: date_create_from_format('H:i:s', $send_time);
            if (!$t) $errors[] = 'Invalid time format';
            else $send_time = $t->format('H:i:00');
        }

        if (empty($errors)) {
            if ($id) {
                $stmt = $this->conn->prepare("UPDATE admin_notifications SET name=?, title=?, body=?, send_time=?, active=?, updated_at=NOW() WHERE id=?");
                $stmt->bind_param("ssssii", $name, $title, $body, $send_time, $active, $id);
                $ok = $stmt->execute();
                if (!$ok) $errors[] = 'Failed to update template';
                $stmt->close();
            } else {
                $stmt = $this->conn->prepare("INSERT INTO admin_notifications (name,title,body,send_time,active,created_at) VALUES (?,?,?,?,?,NOW())");
                $stmt->bind_param("ssssi", $name, $title, $body, $send_time, $active);
                $ok = $stmt->execute();
                if (!$ok) $errors[] = 'Failed to create template';
                $stmt->close();
            }
        }

        return ['ok' => empty($errors), 'errors' => $errors];
    }

    public function delete(int $id): bool
    {
        $stmt = $this->conn->prepare("DELETE FROM admin_notifications WHERE id = ?");
        $stmt->bind_param("i", $id);
        $ok = $stmt->execute();
        $stmt->close();
        return (bool)$ok;
    }
}
