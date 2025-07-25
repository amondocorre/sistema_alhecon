<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class AccesPerfilController extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->database(); 
        $this->load->model('security/AccesPerfil');
    } 
    public function create() {
      if (!validate_http_method($this, ['POST'])) {
        return;
      }
      $res = verifyTokenAccess();
      if(!$res){
        return;
      }
      $data = json_decode(file_get_contents('php://input'), true);
      $id = $this->Perfil_model->create($data);
      if ($id) {
          $response = ['status' => 'success','message'=>'Perfil creado con éxito.'];
          return _send_json_response($this, 200, $response);
      } else {
        $response = ['status' => 'error', 'message' =>  array_values($this->form_validation->error_array())];
        return _send_json_response($this, 400, $response);
      }
    }
    public function update($idAcces,$idPefil) {
        if (!validate_http_method($this, ['POST'])) {
          return; 
        }
        $res = verifyTokenAccess();
        if(!$res){
          return;
        } 
        $data = json_decode(file_get_contents('php://input'), true);
        $buttons = $data['buttons']??[];
        $estado = $data['estado']??0;
        $usuarios = $this->AccesPerfil->findUsuariosByPefil($idPefil);
        if(!$this->AccesPerfil->update($idAcces,$idPefil,$estado,$usuarios)) {
            $response = ['status' => 'error', 'message' =>  'Ucurrio un error inesperado.'];
          return _send_json_response($this, 400, $response);
        } elseif($this->AccesPerfil->addButtonsAccesPerfil($idAcces,$idPefil,$buttons,$usuarios)) {
            $response = ['status' => 'success','message'=>'Permisos actualizado con éxito.'];
            return _send_json_response($this, 200, $response);
        }else{
          $response = ['status' => 'error', 'message' =>  'Ucurrio un error inesperado.'];
          return _send_json_response($this, 400, $response);
        }
    }
    public function findByPerfil($idPefil) {
      if (!validate_http_method($this, ['GET'])) return; 
      $res = verifyTokenAccess();
      if(!$res) return; 
      $access = $this->AccesPerfil->findByPerfil($idPefil);
      $response = ['status' => 'success','menu'=>$access];
      return _send_json_response($this, 200, $response);
    }
    
}
