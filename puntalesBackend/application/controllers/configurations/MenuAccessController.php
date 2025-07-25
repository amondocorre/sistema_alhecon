<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class MenuAccessController extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->database(); 
        $this->load->model('configurations/AccessMenuModel');
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
      $buttons = $data['id_botones'];
      unset($data['id_botones']);
      $id = $this->AccessMenuModel->create($data);
      if ($id) {
        if (count($buttons)>0 && $this->AccessMenuModel->addButtons($id,$buttons)) { 
        }
        $response = ['status' => 'success','message'=>'Acceso creado con éxito.'];
        return _send_json_response($this, 200, $response);
      } else {
        $response = ['status' => 'error', 'message' =>  array_values($this->form_validation->error_array())];
        return _send_json_response($this, 400, $response);
      }
    }
    public function update($id) {
        if (!validate_http_method($this, ['POST'])) {
          return; 
        }
        $res = verifyTokenAccess();
        if(!$res){
          return;
        }  
        $data = json_decode(file_get_contents('php://input'), true);
        $buttons = $data['id_botones'];
        unset($data['id_botones']);
        if ($this->AccessMenuModel->update($id, $data)) {
            if (count($buttons)>=0 && $this->AccessMenuModel->addButtons($id,$buttons)) { 
            }
            $response = ['status' => 'success','message'=>'Acceso actualizado con éxito.'];
            return _send_json_response($this, 200, $response);
        } else {
          $response = ['status' => 'error', 'message' =>  array_values($this->form_validation->error_array())];
          return _send_json_response($this, 400, $response);
        }
    }
    public function delete($id) {
      if (!validate_http_method($this, ['DELETE'])) {
        return; 
      }
      $res = verifyTokenAccess();
      if(!$res){
        return;
      } 
      if ($this->AccessMenuModel->delete($id)) {
          $response = ['status' => 'success','message'=>'Acceso eliminado con éxito.'];
          return _send_json_response($this, 200, $response);
      } else {
        $response = ['status' => 'error', 'message' => 'Ocurrio un error al intentar eliminar el acceso.'];
        return _send_json_response($this, 400, $response);
      }
    }
    public function activate($id) {
      if (!validate_http_method($this, ['PUT'])) {
        return; 
      }
      $res = verifyTokenAccess();
      if(!$res){
        return;
      } 
      if ($this->AccessMenuModel->activate($id)) {
          $response = ['status' => 'success','message'=>'Acceso Habilitado con éxito.'];
          return _send_json_response($this, 200, $response);
      } else {
        $response = ['status' => 'error', 'message' => 'Ocurrio un error al intentar Habilitar el acceso.'];
        return _send_json_response($this, 400, $response);
      }
    }
    public function getPerfil() {
      if (!validate_http_method($this, ['GET'])) return; 
      $res = verifyTokenAccess();
      if(!$res) return; 
      $perfiles = $this->Perfil_model->getPerfil();
      $response = ['status' => 'success','data'=>$perfiles];
      return _send_json_response($this, 200, $response);
    }
    public function findAll() {
      if (!validate_http_method($this, ['GET'])) return; 
      $res = verifyTokenAccess();
      if(!$res) return; 
      $access = $this->AccessMenuModel->findAll();
      $response = ['status' => 'success','menu'=>$access];
      return _send_json_response($this, 200, $response);
    }
}
