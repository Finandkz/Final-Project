<?php
namespace App\Controllers;

use App\Models\User;
use App\Helpers\Env;
use Google_Client;
use Google_Service_Oauth2;

class GoogleController {
    private $client;
    private $db;
    private $userModel;

    public function __construct($db) {
        $this->db = $db;
        $this->userModel = new User($db);

        $this->client = new Google_Client();
        $this->client->setClientId(Env::get("GOOGLE_CLIENT_ID"));
        $this->client->setClientSecret(Env::get("GOOGLE_CLIENT_SECRET"));
        $this->client->setRedirectUri(Env::get("GOOGLE_REDIRECT_URI"));
        $this->client->addScope("openid");
        $this->client->addScope("email");
        $this->client->addScope("profile");
    }

    public function getAuthUrl() {
        return $this->client->createAuthUrl();
    }

    public function handleCallback($code) {
        $token = $this->client->fetchAccessTokenWithAuthCode($code);
        if (isset($token['error'])) return false;

        $this->client->setAccessToken($token);
        $oauth = new Google_Service_Oauth2($this->client);
        $info = $oauth->userinfo->get();

        $name    = $info->name ?? 'No Name';
        $email   = $info->email;
        $googleId = $info->id;
        $picture = property_exists($info, 'picture') ? $info->picture : null; // URL can be long



        $this->userModel->linkGoogle($name, $email, $googleId, $picture);

        return $this->userModel->getByEmail($email);
    }
}
