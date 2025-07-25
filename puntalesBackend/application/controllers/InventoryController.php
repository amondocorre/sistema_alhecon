<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class InventoryController extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->database(); 
        $this->load->model('InventoryModel');
    } 
    public function getStock($id_sucursal) {
      if (!validate_http_method($this, ['GET'])) return; 
      $res = verifyTokenAccess();
      if(!$res) return; 
      $data = $this->InventoryModel->getStock($id_sucursal);
      $response = ['status' => 'success','data'=>$data];
      return _send_json_response($this, 200, $response);
    }

}
