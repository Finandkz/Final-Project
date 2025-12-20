<?php
namespace App\Controllers;

use App\Models\OTP;
use App\Helpers\Mailer;

class OTPController {
    private $db;
    private $otp;
    public function __construct($db) {
        $this->db = $db;
        $this->otp = new OTP($db);
    }

    public function resend($email) {
        $this->otp->invalidateAllByEmail($email);
        $code = random_int(100000, 999999);
        $this->otp->create($email, (string)$code, 5);
        $sent = Mailer::send($email, 
        "Mealify - OTP Code", "Hello, here is your New OTP code:
         <b>{$code}</b>. Use this code to complete your verification.
         This code is only valid for 5 minutes.");
        return $sent;
    }
}
