<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use Firebase\JWT\JWT;

class JWTHandler {

    private $secret_key = PASSWOR_ACCESS; 

    public function encode($payload) {
      $payload['iat'] = time(); 
      $payload['exp'] = time() + 360000000;
      return JWT::encode($payload,$this->secret_key, 'HS256');
    }
    public function decode($jwt) {
        try {
          $decoded = JWT::decode($jwt, $this->secret_key,['HS256']);
          return $decoded;
        } catch (Exception $e) {
            return false;
        }
    }
}