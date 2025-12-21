<?php
namespace App\Controllers;
use App\Helpers\Session;
use App\Config\Database;
use App\Helpers\Env;
use Throwable;
class AccountController{
    private $conn;
    private $user;

    public function __construct(){
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

    public function handle(): array{
        $errors  = [];
        $success = null;
        $userId  = (int)$this->user['id'];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::validateCsrfToken($_POST['_csrf'] ?? '')) {
                $errors[] = "Invalid CSRF token. Please refresh the page.";
                return [
                    'userRow' => $this->getUserData($userId),
                    'errors'  => $errors,
                    'success' => $success,
                ];
            }
            $action = $_POST['action'] ?? '';
            if ($action === 'update-profile') {
                $this->handleUpdateProfile($userId, $errors, $success);
            } elseif ($action === 'delete-account') {
                $this->handleDeleteAccount($userId);
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload-avatar') {
            $this->handleUploadAvatar($userId, $errors, $success);
        }
        $row = $this->getUserData($userId);
        if (!$row) {
            $errors[] = "User data not found.";
        }
        return [
            'userRow' => $row,
            'errors'  => $errors,
            'success' => $success,
        ];
    }

    private function getUserData(int $userId): ?array{
        $stmt = $this->conn->prepare(
            "SELECT id, name, email, role, weight_kg, goal_diet, goal_bulking, avatar, google_avatar_url
            FROM users
            WHERE id = ?"
        );
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $res   = $stmt->get_result();
        $row   = $res->fetch_assoc();
        $stmt->close();
        return $row;
    }

    private function handleUpdateProfile(int $userId, array &$errors, ?string &$success): void{
        $name      = trim($_POST['name'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $weightStr = trim($_POST['weight_kg'] ?? '');
        $goalSelection = $_POST['goal_selection'] ?? '';
        $goalDiet = ($goalSelection === 'diet') ? 1 : 0;
        $goalBulk = ($goalSelection === 'bulking') ? 1 : 0;

        if ($name === '') {
            $errors[] = 'Name must be filled in.';
        }
        if ($email === '') {
            $errors[] = 'Email is required.';
        }
        $weight = null;
        if ($weightStr !== '') {
            if (!ctype_digit($weightStr)) {
                $errors[] = 'Weight must be in numbers (kg).';
            } else {
                $weight = (int)$weightStr;
            }
        }

        if ($errors) return;

        $stmt = $this->conn->prepare(
            "UPDATE users
             SET name = ?, email = ?, weight_kg = ?, goal_diet = ?, goal_bulking = ?
             WHERE id = ?"
        );
        $stmt->bind_param(
            "ssiiii",
            $name,
            $email,
            $weight,
            $goalDiet,
            $goalBulk,
            $userId
        );

        if ($stmt->execute()) {
            $success = "Profile successfully updated.";
            $sessionUser           = $this->user;
            $sessionUser['name']   = $name;
            $sessionUser['email']  = $email;
            Session::set('user', $sessionUser);
            $this->user = $sessionUser;

        } else {
            $errors[] = "Failed to update profile.";
        }

        $stmt->close();
    }

    private function handleUploadAvatar(int $userId, array &$errors, ?string &$success): void
    {
        if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] === UPLOAD_ERR_NO_FILE) {
            $errors[] = "No files selected.";
            return;
        }

        $file = $_FILES['avatar'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "Failed to upload file.";
            return;
        }

        if ($file['size'] > 2 * 1024 * 1024) { // 2MB
            $errors[] = "Maximum photo size is 2MB.";
            return;
        }

        $tmpPath = $file['tmp_name'];
        $mime    = mime_content_type($tmpPath);
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png'];

        if (!isset($allowed[$mime])) {
            $errors[] = "The image format must be JPG or PNG.";
            return;
        }

        $ext        = $allowed[$mime];
        $newName    = 'avatar_' . $userId . '_' . time() . '.' . $ext;
        $uploadDir  = __DIR__ . '/../../public/uploads/avatars/';
        $uploadPath = $uploadDir . $newName;

        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0777, true);
        }

        if (!move_uploaded_file($tmpPath, $uploadPath)) {
            $errors[] = "Failed to save file.";
            return;
        }

        $old = null;
        $stmt = $this->conn->prepare("SELECT avatar FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            $old = $row['avatar'];
        }
        $stmt->close();

        $stmt = $this->conn->prepare("UPDATE users SET avatar = ? WHERE id = ?");
        $stmt->bind_param("si", $newName, $userId);
        if ($stmt->execute()) {
            $success = "Profile photo successfully updated.";

            if ($old && is_file($uploadDir . $old)) {
                @unlink($uploadDir . $old);
            }
        } else {
            $errors[] = "Failed to save profile photo.";
        }
        $stmt->close();
    }

    private function handleDeleteAccount(int $userId): void
    {
        $this->conn->begin_transaction();

        try {
            $stm1 = $this->conn->prepare("DELETE FROM meal_plans WHERE user_id = ?");
            $stm1->bind_param("i", $userId);
            $stm1->execute();
            $stm1->close();

            $stmUser = $this->conn->prepare("DELETE FROM users WHERE id = ?");
            $stmUser->bind_param("i", $userId);
            $stmUser->execute();
            $stmUser->close();

            $this->conn->commit();

            Session::destroy();
            session_write_close();
            header("Location: ../login.php");
            exit;
        } catch (Throwable $e) {
            $this->conn->rollback();
            Session::set('account_delete_error', 'Failed to delete account. Please try again later.');
            session_write_close();
            header("Location: account.php");
            exit;
        }
    }
}
