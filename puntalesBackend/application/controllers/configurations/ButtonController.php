<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class ButtonController extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->database(); 
        $this->load->model('configurations/ButtonModel');
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
    public function update($id) {
        if (!validate_http_method($this, ['POST'])) {
          return; 
        }
        $res = verifyTokenAccess();
        if(!$res){
          return;
        } 
        $data = json_decode(file_get_contents('php://input'), true);
        if ($this->Perfil_model->update($id, $data)) {
            $response = ['status' => 'success','message'=>'Perfil actualizado con éxito.'];
            return _send_json_response($this, 200, $response);
        } else {
          $response = ['status' => 'error', 'message' =>  array_values($this->form_validation->error_array())];
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
    public function findAll() {
      if (!validate_http_method($this, ['GET'])) return; 
      $res = verifyTokenAccess();
      if(!$res) return; 
      $access = $this->ButtonModel->findAll();
      $response = ['status' => 'success','buttons'=>$access];
      return _send_json_response($this, 200, $response);
    }
}
