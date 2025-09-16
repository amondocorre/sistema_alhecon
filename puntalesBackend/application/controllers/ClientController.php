<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class ClientController extends CI_Controller {
  public function __construct() {
      parent::__construct();
      $this->load->database(); 
      $this->load->model('Client_model');
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
    $file_ciA = $_FILES['file_ciA']??null;
    $file_ciB = $_FILES['file_ciB']??null;
    $companies = isset($data['empresas'])?json_decode($data['empresas']):[];
    unset($data['empresas']);
    unset($data['nombre_completo']);
    $id = $this->Client_model->create($data);
    if ($id) {
      $this->Client_model->addCompanies($id,$companies);
      if($file_ciA){
        $url = guardarArchivo($id.'_foto_ciA',$file_ciA,'assets/clientes/ci/');
        if(!$url){
          $response = ['status' => 'success','message'=>'Ocurrio un error al guardar la foto de carner.'];
          return _send_json_response($this, 200, $response);
        }
        $this->Client_model->updateFotoCi($url,$id,'foto_ciA');
      }
      if($file_ciB){
        $url = guardarArchivo($id.'_foto_ciB',$file_ciB,'assets/clientes/ci/');
        if(!$url){
          $response = ['status' => 'success','message'=>'Ocurrio un error al guardar la foto de carnet.'];
          return _send_json_response($this, 200, $response);
        }
        $this->Client_model->updateFotoCi($url,$id,'foto_ciB');
      }
      $cliente = $this->Client_model->findIdentity($id); 
      $cliente->nombre_completo = $cliente->nombres.' '.$cliente->ap_paterno.' '.$cliente->ap_materno;
      $response = ['status' => 'success','message'=>'Cliente creado con éxito.','data'=>$cliente];
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
    $file_ciA = $_FILES['file_ciA']??null;
    $file_ciB = $_FILES['file_ciB']??null;
    $companies = $data['empresas']?json_decode($data['empresas']):[];
    unset($data['nombre_completo']);
    unset($data['empresas']);
    if ($this->Client_model->update($id, $data)) {
      $this->Client_model->addCompanies($id,$companies);
      if($file_ciA){
        $url = guardarArchivo($id.'_foto_ciA',$file_ciA,'assets/clientes/ci/');
        if(!$url){
          $response = ['status' => 'success','message'=>'Ocurrio un error al guardar la foto de carner.'];
          return _send_json_response($this, 200, $response);
        }
        $this->Client_model->updateFotoCi($url,$id,'foto_ciA');
      }
      if($file_ciB){
        $url = guardarArchivo($id.'_foto_ciB',$file_ciB,'assets/clientes/ci/');
        if(!$url){
          $response = ['status' => 'success','message'=>'Ocurrio un error al guardar la foto de carnet.'];
          return _send_json_response($this, 200, $response);
        }
        $this->Client_model->updateFotoCi($url,$id,'foto_ciB');
      }
      $response = ['status' => 'success','message'=>'Cliente actualizado con éxito.'];
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
    if ($this->Client_model->delete($id)) {
        $response = ['status' => 'success','message'=>'Cliente eliminado con éxito.'];
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
    if ($this->Client_model->activate($id)) {
        $response = ['status' => 'success','message'=>'Cliente Habilitado con éxito.'];
        return _send_json_response($this, 200, $response);
    } else {
      $response = ['status' => 'error', 'message' => 'Ocurrio un eror al internatar Habilitar al Cliente.'];
      return _send_json_response($this, 400, $response);
    }
  }
  public function findActive() {
    if (!validate_http_method($this, ['GET'])) return; 
    $res = verifyTokenAccess();
    if(!$res) return; 
    $perfiles = $this->Client_model->findActive();
    $response = ['status' => 'success','data'=>$perfiles];
    return _send_json_response($this, 200, $response);
  }
  public function findAll() {
    if (!validate_http_method($this, ['GET'])) return; 
    $res = verifyTokenAccess();
    if(!$res) return; 
    $clients = $this->Client_model->findAll();
    $response = ['status' => 'success','data'=>$clients];
    return _send_json_response($this, 200, $response);
  }
}
