<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class StatusController extends CI_Controller {
  public function __construct() {
    parent::__construct();
    $this->load->database(); 
    $this->load->model('configurations/StatusModel');
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
    $id = $this->StatusModel->create($data);
    if ($id) {
      $response = ['status' => 'success','message'=>'Estado creado con éxito.'];
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
    if ($this->StatusModel->update($id, $data)) {
      $response = ['status' => 'success','message'=>'Estado actualizado con éxito.'];
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
    if ($this->StatusModel->delete($id)) {
        $response = ['status' => 'success','message'=>'Estado eliminado con éxito.'];
        return _send_json_response($this, 200, $response);
    } else {
      $response = ['status' => 'error', 'message' =>  'Ocurrio un eror al internatar eliminar el Estado.'];
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
    if ($this->StatusModel->activate($id)) {
        $response = ['status' => 'success','message'=>'Estado Habilitado con éxito.'];
        return _send_json_response($this, 200, $response);
    } else {
      $response = ['status' => 'error', 'message' => 'Ocurrio un eror al internatar Habilitar al Estado.'];
      return _send_json_response($this, 400, $response);
    }
  }
  public function findActive() {
    if (!validate_http_method($this, ['GET'])) return; 
    $res = verifyTokenAccess();
    if(!$res) return; 
    $status = $this->StatusModel->findActive();
    $response = ['status' => 'success','data'=>$status];
    return _send_json_response($this, 200, $response);
  }
  public function findAll() {
    if (!validate_http_method($this, ['GET'])) return; 
    $res = verifyTokenAccess();
    if(!$res) return; 
    $status = $this->StatusModel->findAll();
    $response = ['status' => 'success','data'=>$status];
    return _send_json_response($this, 200, $response);
  }
}
