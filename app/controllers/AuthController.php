<?php
namespace App\Controllers;

use App\Models\User;
use App\Models\OTP;
use App\Helpers\Mailer;
use App\Helpers\Session;
use App\Helpers\Env;

class AuthController {
    private $db;
    private $user;
    private $otp;

    public function __construct($db) {
        $this->db = $db;
        $this->user = new User($db);
        $this->otp = new OTP($db);
    }

    public function registerAndSendOTP($name, $email, $password) {
        if (strlen($password) < 8 || !preg_match('/[A-Z]/', $password)) {
            return [
                'status' => 'error',
                'message' => 'Passwords must be at least 8 characters long and contain at least 1 capital letter.'
            ];
        }

        if($this->user->getByEmail($email)) {
             $existingUser = $this->user->getByEmail($email);
             if((int)$existingUser['is_verified'] === 1) {
                return ['status'=>'error','message'=>'EMAIL USED'];
             }

        } else {
            $created = $this->user->register($name, $email, $password);
            if(!$created) 
                return ['status'=>'error','message'=>'DB ERROR'];
        }

        $this->otp->invalidateAllByEmail($email);

        $code = random_int(100000, 999999);
        $this->otp->create($email, (string)$code, 5);

        $sent = Mailer::send($email,
         "Mealify - OTP Code", "Hello, here is your OTP code:
         <b>{$code}</b>. Use this code to complete your verification.
          This code is only valid for 5 minutes.");

        return ['status'=>'ok','message'=>'OTP_SENT','sent'=>$sent];
    }

    public function verifyOTP($email, $code) {
        $data = $this->otp->validate($email, $code);
        if(!$data) return false;
        $this->otp->markUsed($data['id']);
        $this->user->verifyEmail($email);
        return true;
    }

    public function login($email, $password) {
        $res = $this->user->loginByEmail($email, $password);

        if($res === "NOT_VERIFIED") 
            return ['status'=>'error','message'=>'NOT VERIFIED'];

        if(!$res) 
            return ['status'=>'error','message'=>'INVALID CREDENTIALS'];

        Session::start();
        session_regenerate_id(true);

        return ['status'=>'ok','user'=>$res]; 
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    require_once __DIR__ . "/../helpers/Session.php";
    require_once __DIR__ . "/../helpers/env.php";

    Env::load();
    Session::start();
    Session::destroy();

    $baseUrl = Env::get("BASE_URL", "http://localhost/mealify/public");
    header("Location: " . $baseUrl . "/login.php");
    exit;
}


