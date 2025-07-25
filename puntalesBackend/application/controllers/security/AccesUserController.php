<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class AccesUserController extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->database(); 
        $this->load->model('configurations/AccessMenuModel');
        $this->load->model('security/AccesUser');
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
    public function update($idAcces,$idUser) {
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
        if(!$this->AccesUser->update($idAcces,$idUser,$estado)) {
            $response = ['status' => 'error', 'message' =>  'Ucurrio un error inesperado.'];
          return _send_json_response($this, 400, $response);
        } elseif($this->AccesUser->addButtonsAccesUser($idAcces,$idUser,$buttons)) {
            $response = ['status' => 'success','message'=>'Permisos actualizado con éxito.'];
            return _send_json_response($this, 200, $response);
        }else{
          $response = ['status' => 'error', 'message' =>  'Ucurrio un error inesperado.'];
          return _send_json_response($this, 400, $response);
        }
    }
    public function findActive() {
      if (!validate_http_method($this, ['GET'])) return; 
      $res = verifyTokenAccess();
      if(!$res) return; 
      $perfiles = $this->ButtonModel->findActive();
      $response = ['status' => 'success','buttons'=>$perfiles];
      return _send_json_response($this, 200, $response);
    }
    public function findByUser($idUser) {
      if (!validate_http_method($this, ['GET'])) return; 
      $res = verifyTokenAccess();
      if(!$res) return; 
      $access = $this->AccesUser->findByUser($idUser);
      $response = ['status' => 'success','menu'=>$access];
      return _send_json_response($this, 200, $response);
    }
    
}
