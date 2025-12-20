<?php
namespace App\Controllers;

use App\Models\ResetPassword;
use App\Models\User;
use App\Helpers\Mailer;
use App\Helpers\Env;

class PasswordController {
    private $db;
    private $rp;
    private $user;

    public function __construct($db) {
        $this->db = $db;
        $this->rp = new ResetPassword($db);
        $this->user = new User($db);
    }

    public function sendResetLink($email) {
        $user = $this->user->getByEmail($email);
        if (!$user) {
            return ['ok' => false, 'reason' => 'NOT_FOUND'];
        }

        if (is_null($user['password']) && !empty($user['google_id'])) {
            return ['ok' => false, 'reason' => 'GOOGLE_ONLY'];
        }

        $token = bin2hex(random_bytes(32));
        $this->rp->createToken($email, $token, 30);

        $baseUrl = Env::get("BASE_URL");
        $link = $baseUrl . "/reset-password.php?token=" . urlencode($token);

        $body = "Password reset request<br>
                 Please <a href='{$link}'>click here</a> to reset your password.
                 This is valid for 30 minutes only.
                 Please ignore this message if you are not requesting a reset.";

        $sent = Mailer::send($email, "Mealify - Reset Password", $body);

        if ($sent) {
            return ['ok' => true, 'reason' => 'OK'];
        }

        return ['ok' => false, 'reason' => 'MAIL_ERROR'];
    }

    public function validateToken($token) {
        return $this->rp->validateToken($token);
    }

    public function resetPassword($token, $newPassword) {
        $entry = $this->rp->validateToken($token);
        if (!$entry) return false;

        $user = $this->user->getByEmail($entry['email']);
        if (!$user) return false;

        if (is_null($user['password']) && !empty($user['google_id'])) {
            return false;
        }

        if (strlen($newPassword) < 8 || !preg_match('/[A-Z]/', $newPassword)) {
            return false;
        }

        $sql = "UPDATE users SET password = ? WHERE email = ?";
        $stmt = $this->db->prepare($sql);
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt->bind_param("ss", $hash, $entry['email']);
        $ok = $stmt->execute();

        if ($ok) {
            $this->rp->markUsed($entry['id']);
        }

        return $ok;
    }
}
