<?php
use App\Config\Database;
use App\Controllers\OTPController;
use App\Helpers\Session;

require_once __DIR__ . "/../vendor/autoload.php";
Session::start();
$db = (new Database())->connect();
$otpCtrl = new OTPController($db);

$email = Session::get('pending_email');
if(!$email) { echo json_encode(['ok'=>false,'msg'=>'No pending email']); exit; }
$sent = $otpCtrl->resend($email);
echo json_encode(['ok'=>$sent]);
