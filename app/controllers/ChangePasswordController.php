<?php
namespace App\Controllers;

use App\Helpers\Session;
use App\Config\Database;
use App\Helpers\Env;

class ChangePasswordController
{
    private $conn;
    private $user;

    public function __construct()
    {
        Session::start();
        $this->user = Session::get('user');

        if (!$this->user) {
            session_write_close();
            header("Location: ../login.php");
            exit;
        }

        Env::load();
        $tz = Env::get('APP_TIMEZONE', 'Asia/Jakarta');
        @date_default_timezone_set($tz);

        $db = new Database();
        $this->conn = $db->connect();
    }

    public function handle(): array
    {
        $errors  = [];
        $success = null;

        $userId = (int)$this->user['id'];
        $stmt = $this->conn->prepare(
            "SELECT password, google_id FROM users WHERE id = ?"
        );
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $res  = $stmt->get_result();
        $row  = $res->fetch_assoc();
        $stmt->close();

        if (!$row) {
            $errors[] = "User data not found.";
            return compact('errors', 'success');
        }

        $storedHash = $row['password'];
        $googleId   = $row['google_id'];

        if (is_null($storedHash) && !empty($googleId)) {
            $errors[] =
                "Your account is connected to Google.
                This account password is managed by Google and cannot be changed from the app.";
            return compact('errors', 'success');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::validateCsrfToken($_POST['_csrf'] ?? '')) {
                $errors[] = "Invalid CSRF token. Please refresh the page.";
                return compact('errors', 'success');
            }

            $current = $_POST['current_password'] ?? '';
            $new     = $_POST['new_password'] ?? '';
            $confirm = $_POST['new_password_confirmation'] ?? '';

            if ($current === '' || $new === '' || $confirm === '') {
                $errors[] = "All fields are required.";
            }

            if (!$errors && !password_verify($current, $storedHash)) {
                $errors[] = "Current password is invalid.";
            }

            if (!$errors && (strlen($new) < 8 || !preg_match('/[A-Z]/', $new))) {
                $errors[] = "Passwords must be at least 8 characters long and contain at least 1 capital letter.";
            }

            if (!$errors && $new !== $confirm) {
                $errors[] = "New password confirmation does not match.";
            }

            if (!$errors) {
                $newHash = password_hash($new, PASSWORD_DEFAULT);

                $stmt = $this->conn->prepare(
                    "UPDATE users SET password = ? WHERE id = ?"
                );
                $stmt->bind_param("si", $newHash, $userId);

                if ($stmt->execute()) {
                    $success = "Password updated successfully.";

                    $sessionUser = $this->user;
                    $sessionUser['password'] = $newHash;
                    Session::set('user', $sessionUser);
                    $this->user = $sessionUser;
                } else {
                    $errors[] = "Password update failed. Please try again later.";
                }
                $stmt->close();
            }
        }

        return compact('errors', 'success');
    }
}
