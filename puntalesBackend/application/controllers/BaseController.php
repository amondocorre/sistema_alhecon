<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class BaseController extends BaseController {
    public function __construct() {
        parent::__construct();
        //$this->load->library('JWTHandler');
    }
  public function verifyTokenAccess() {
      $authHeader = $this->input->get_request_header('Authorization');
      $token = str_replace('Bearer ', '', $authHeader);
      if (!$token) {
          $this->output
              ->set_status_header(401)
              ->set_content_type('application/json')
              ->set_output(json_encode(['message' => 'Token no proporcionado']));
          return;
          exit();
      }
      try {
          $decoded = null;// $this->JWTHandler->decode($token);
          return $decoded; 
      } catch (Exception $e) {
          $this->output
              ->set_status_header(401)
              ->set_content_type('application/json')
              ->set_output(json_encode(['message' => 'Token inválido o expirado']));
          exit;
      }
    }
}
