<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class SucursalUsuarioController extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->database(); 
        $this->load->model('configurations/SucursalUsuarioModel');
    } 
    public function addSucursales() {
      if (!validate_http_method($this, ['POST'])) {
        return;
      }
      $res = verifyTokenAccess();
      if(!$res){
        return;
      }
      $data = json_decode(file_get_contents('php://input'), true);
      $sucursales = $data['sucursales']??[]; 
      $id_usuario = $data['id_usuario']??0; 
      $status = $this->SucursalUsuarioModel->addSucursales($sucursales,$id_usuario);
      if ($status) {
          $response = ['status' => 'success','message'=>'Se guardo la información con éxito.'];
          return _send_json_response($this, 200, $response);
      } else {
        $response = ['status' => 'error', 'message' =>  array_values($this->form_validation->error_array())];
        return _send_json_response($this, 400, $response);
      }
    }
    public function getSucursalesUser() {
      if (!validate_http_method($this, ['GET'])) return; 
      $res = verifyTokenAccess();
      if(!$res) return;
      $user = $res->user;
      $idUser = $user->id_usuario;
      $proveedores = $this->SucursalUsuarioModel->getSucursalesUser($idUser);
      $response = ['status' => 'success','data'=>$proveedores];
      return _send_json_response($this, 200, $response);
    }
    public function findAll() {
      if (!validate_http_method($this, ['GET'])) return; 
      $res = verifyTokenAccess();
      if(!$res) return; 
      $proveedores = $this->SucursalUsuarioModel->findAll();
      $response = ['status' => 'success','data'=>$proveedores];
      return _send_json_response($this, 200, $response);
    }
}
