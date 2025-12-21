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
        $existingUser = $this->user->getByEmail($email);
        if($existingUser) {
             if((int)$existingUser['is_verified'] === 1) {
                return ['status'=>'error','message'=>'EMAIL USED'];
             }
             $this->user->updateUnverified($name, $email, $password);
        } else {
            $created = $this->user->register($name, $email, $password);
            if(!$created) 
                return ['status'=>'error','message'=>'DB ERROR'];
        }
        $this->otp->invalidateAllByEmail($email);
        $code = random_int(100000, 999999);
        $otpCreated = $this->otp->create($email, (string)$code, 5);
        if (!$otpCreated) {
            return ['status' => 'error', 'message' => 'Failed to generate security code.'];
        }

        $sent = Mailer::send($email,
         "Mealify - OTP Code", "Hello, here is your OTP code:
         <b>{$code}</b>. Use this code to complete your verification.
          This code is only valid for 5 minutes.");

        if ($sent !== true) {
            return ['status' => 'error', 'message' => 'Failed to send verification email: ' . $sent];
        }

        return ['status'=>'ok','message'=>'OTP_SENT'];
    }

    public function verifyOTP($email, $code) {
        $data = $this->otp->validate($email, $code);
        if(!$data) return false;
        $this->otp->markUsed($data['id']);
        return $this->user->verifyEmail($email);
    }

    public function login($email, $password) {
        $res = $this->user->loginByEmail($email, $password);

        if($res === "NOT_VERIFIED") 
            return ['status'=>'error','message'=>'NOT_VERIFIED'];

        if(!$res) 
            return ['status'=>'error','message'=>'INVALID CREDENTIALS'];

        Session::start();
        session_regenerate_id(true);

        return ['status'=>'ok','user'=>$res]; 
    }
}