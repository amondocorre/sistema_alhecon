<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class ProductController extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->database(); 
        $this->load->model('configurations/ProductModel');
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
      $id = $this->ProductModel->create($data,$idUser);
      if ($id) {
        if($foto){
          $url = guardarArchivo($id,$foto,'assets/imagenes/productos/');
          if(!$url){
            $response = ['status' => 'success','message'=>'Ocurrio un error al guardar la foto del producto.'];
            return _send_json_response($this, 200, $response);
          }
          $this->ProductModel->updateFoto($url,$id);
        }
        $response = ['status' => 'success','message'=>'Producto creado con éxito.'];
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
        if ($this->ProductModel->update($id, $data,$idUser)) {
          if($foto){
            $url = guardarArchivo($id,$foto,'assets/imagenes/productos/');
            if(!$url){
              $response = ['status' => 'success','message'=>'Ocurrio un error al guardar la foto del producto.'];
              return _send_json_response($this, 200, $response);
            }
            $this->ProductModel->updateFoto($url,$id);
          }
          $response = ['status' => 'success','message'=>'Producto actualizado con éxito.'];
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
      if ($this->ProductModel->delete($id)) {
          $response = ['status' => 'success','message'=>'Producto eliminado con éxito.'];
          return _send_json_response($this, 200, $response);
      } else {
        $response = ['status' => 'error', 'message' =>  'Ocurrio un eror al internatar eliminar la Producto.'];
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
      if ($this->ProductModel->activate($id)) {
          $response = ['status' => 'success','message'=>'Producto Habilitado con éxito.'];
          return _send_json_response($this, 200, $response);
      } else {
        $response = ['status' => 'error', 'message' => 'Ocurrio un eror al internatar Habilitar la Producto.'];
        return _send_json_response($this, 400, $response);
      }
    }
    public function findActive() {
      if (!validate_http_method($this, ['GET'])) return; 
      $res = verifyTokenAccess();
      if(!$res) return; 
      $productos = $this->ProductModel->findActive();
      $response = ['status' => 'success','data'=>$productos];
      return _send_json_response($this, 200, $response);
    }
    public function findAll() {
      if (!validate_http_method($this, ['GET'])) return; 
      $res = verifyTokenAccess();
      if(!$res) return; 
      $productos = $this->ProductModel->findAll();
      $response = ['status' => 'success','data'=>$productos];
      return _send_json_response($this, 200, $response);
    }
}
