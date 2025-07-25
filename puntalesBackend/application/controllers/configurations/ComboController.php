<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class ComboController extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->database(); 
        $this->load->model('configurations/ComboModel');
    } 
    public function create() {
      if (!validate_http_method($this, ['POST'])) {
        return;
      }
      $res = verifyTokenAccess();
      if(!$res){
        return;
      }
      $user = $res->user;
      $idUser = $user->id_usuario;
     $data = $this->input->post();
      $file = $_FILES['file']??null;
      $foto = $_FILES['foto']??null;
      $productos = $data['productos']?json_decode($data['productos']):[];
      unset($data['productos']);
      $id = $this->ComboModel->create($data,$idUser);
      if ($id) {
        if($foto){
          $url = guardarArchivo($id,$foto,'assets/imagenes/productos/');
          if(!$url){
            $response = ['status' => 'success','message'=>'Ocurrio un error al guardar la foto del combo.'];
            return _send_json_response($this, 200, $response);
          }
          $this->ComboModel->updateFoto($url,$id);
        }
        $this->ComboModel->addProducts($id,$productos);
        $response = ['status' => 'success','message'=>'Combo creado con éxito.'];
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
        $user = $res->user;
        $idUser = $user->id_usuario;
        $data = $this->input->post();
        $file = $_FILES['file']??null;
        $foto = $_FILES['foto']??null;
        $productos = $data['productos']?json_decode($data['productos']):[];
        unset($data['productos']);
        if ($this->ComboModel->update($id, $data,$idUser)) {
          if($foto){
            $url = guardarArchivo($id,$foto,'assets/imagenes/productos/');
            if(!$url){
              $response = ['status' => 'success','message'=>'Ocurrio un error al guardar la foto del combo.'];
              return _send_json_response($this, 200, $response);
            }
            $this->ComboModel->updateFoto($url,$id);
          }
          $this->ComboModel->addProducts($id,$productos);
          $response = ['status' => 'success','message'=>'Combo actualizado con éxito.'];
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
      if ($this->ComboModel->delete($id)) {
          $response = ['status' => 'success','message'=>'Combo eliminado con éxito.'];
          return _send_json_response($this, 200, $response);
      } else {
        $response = ['status' => 'error', 'message' =>  'Ocurrio un eror al internatar eliminar el Combo.'];
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
      if ($this->ComboModel->activate($id)) {
          $response = ['status' => 'success','message'=>'Combo Habilitado con éxito.'];
          return _send_json_response($this, 200, $response);
      } else {
        $response = ['status' => 'error', 'message' => 'Ocurrio un eror al internatar Habilitar al Combo.'];
        return _send_json_response($this, 400, $response);
      }
    }
    public function findActive() {
      if (!validate_http_method($this, ['GET'])) return; 
      $res = verifyTokenAccess();
      if(!$res) return; 
      $combos = $this->ComboModel->findActive();
      $response = ['status' => 'success','data'=>$combos];
      return _send_json_response($this, 200, $response);
    }
    public function findAll() {
      if (!validate_http_method($this, ['GET'])) return; 
      $res = verifyTokenAccess();
      if(!$res) return; 
      $combos = $this->ComboModel->findAll();
      $response = ['status' => 'success','data'=>$combos];
      return _send_json_response($this, 200, $response);
    }
}
