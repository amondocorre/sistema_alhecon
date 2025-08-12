<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class TransportController extends CI_Controller {
  public function __construct() {
      parent::__construct();
      $this->load->database(); 
      $this->load->model('TransportModel');
  } 
  public function create() {
    if (!validate_http_method($this, ['POST'])) {
      return;
    }
    $res = verifyTokenAccess();
    if(!$res){
      return;
    }
    $data = $this->input->post();
    $file = $_FILES['file']??null;
    $id = $this->TransportModel->create($data);
    if ($id) {
      if($file){
        $url = guardarArchivo($id,$file,'assets/fotografia_movilidad/');
        if(!$url){
          $response = ['status' => 'success','message'=>'Ocurrio un error al guardar la foto la movilidad.'];
          return _send_json_response($this, 200, $response);
        }
        $this->TransportModel->updateFoto($url,$id);
      }
      $response = ['status' => 'success','message'=>'Transporte creado con éxito.'];
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
    $data = $this->input->post();
    $file = $_FILES['file']??null;
    if ($this->TransportModel->update($id, $data)) {
      if($file){
        $url = guardarArchivo($id.'_foto_ciA',$file,'assets/fotografia_movilidad');
        if(!$url){
          $response = ['status' => 'success','message'=>'Ocurrio un error al guardar la foto de carner.'];
          return _send_json_response($this, 200, $response);
        }
        $this->TransportModel->updateFotoCi($url,$id);
      }
      $response = ['status' => 'success','message'=>'Transporte actualizado con éxito.'];
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
    if ($this->TransportModel->delete($id)) {
        $response = ['status' => 'success','message'=>'Transporte eliminado con éxito.'];
        return _send_json_response($this, 200, $response);
    } else {
      $response = ['status' => 'error', 'message' =>  array_values($this->form_validation->error_array())];
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
    if ($this->TransportModel->activate($id)) {
        $response = ['status' => 'success','message'=>'Transporte Habilitado con éxito.'];
        return _send_json_response($this, 200, $response);
    } else {
      $response = ['status' => 'error', 'message' => 'Ocurrio un eror al internatar Habilitar al Transporte.'];
      return _send_json_response($this, 400, $response);
    }
  }
  public function findActive() {
    if (!validate_http_method($this, ['GET'])) return; 
    $res = verifyTokenAccess();
    if(!$res) return; 
    $perfiles = $this->TransportModel->findActive();
    $response = ['status' => 'success','data'=>$perfiles];
    return _send_json_response($this, 200, $response);
  }
  public function findAll() {
    if (!validate_http_method($this, ['GET'])) return; 
    $res = verifyTokenAccess();
    if(!$res) return; 
    $clients = $this->TransportModel->findAll();
    $response = ['status' => 'success','data'=>$clients];
    return _send_json_response($this, 200, $response);
  }
}
