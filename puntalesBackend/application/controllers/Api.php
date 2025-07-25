<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Api extends CI_Controller {
    public function index() {
        echo json_encode(['status' => 'success', 'message' => 'API funcionando']);
    }
}
