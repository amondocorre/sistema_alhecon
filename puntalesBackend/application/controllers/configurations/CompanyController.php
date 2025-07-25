<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class CompanyController extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->database(); 
        $this->load->model('configurations/Company');
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
      $fileE = $_FILES['fileE']??null;
      $fileI = $_FILES['fileI']??null;
      $id = $this->Company->create($data);
      if ($id) {
          if($fileE){
            $url = guardarArchivo($id.'_empresa',$fileE,'assets/logos/');
            if(!$url){
              $response = ['status' => 'success','message'=>'Ocurrio un error al guardar el logo de empresa.'];
              return _send_json_response($this, 200, $response);
            }
            $this->Company->updateLogo($url,$id,'logo_empresa');
          }
          if($fileI){
            $url = guardarArchivo($id.'_impresion',$fileI,'assets/logos/');
            if(!$url){
              $response = ['status' => 'success','message'=>'Ocurrio un error al guardar el logo de impresion.'];
              return _send_json_response($this, 200, $response);
            }
            $this->Company->updateLogo($url,$id,'logo_impresion');
          }
          $response = ['status' => 'success','message'=>'Empresa creado con éxito.'];
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
        $data = $this->input->post();
        $fileE = $_FILES['fileE']??null;
        $fileI = $_FILES['fileI']??null;
        if ($this->Company->update($id, $data)) {
          if($fileE){
            $url = guardarArchivo($id.'_empresa',$fileE,'assets/logos/');
            if(!$url){
              $response = ['status' => 'success','message'=>'Ocurrio un error al guardar el logo de empresa.'];
              return _send_json_response($this, 200, $response);
            }
            $this->Company->updateLogo($url,$id,'logo_empresa');
          }
          if($fileI){
            $url = guardarArchivo($id.'_impresion',$fileI,'assets/logos/');
            if(!$url){
              $response = ['status' => 'success','message'=>'Ocurrio un error al guardar el logo de impresion.'];
              return _send_json_response($this, 200, $response);
            }
            $this->Company->updateLogo($url,$id,'logo_impresion');
          }
            $response = ['status' => 'success','message'=>'Empresa actualizado con éxito.'];
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
      if ($this->Company->delete($id)) {
          $response = ['status' => 'success','message'=>'Empresa eliminado con éxito.'];
          return _send_json_response($this, 200, $response);
      } else {
        $response = ['status' => 'error', 'message' =>  'Ocurrio un eror al internatar eliminar el Empresa.'];
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
      if ($this->Company->activate($id)) {
          $response = ['status' => 'success','message'=>'Empresa Habilitado con éxito.'];
          return _send_json_response($this, 200, $response);
      } else {
        $response = ['status' => 'error', 'message' => 'Ocurrio un eror al internatar Habilitar al Empresa.'];
        return _send_json_response($this, 400, $response);
      }
    }
    public function findActive() {
      if (!validate_http_method($this, ['GET'])) return; 
      $res = verifyTokenAccess();
      if(!$res) return; 
      $empresas = $this->Company->findActive();
      $response = ['status' => 'success','data'=>$empresas];
      return _send_json_response($this, 200, $response);
    }
    public function findAll() {
      if (!validate_http_method($this, ['GET'])) return; 
      $res = verifyTokenAccess();
      if(!$res) return; 
      $empresas = $this->Company->findAll();
      $response = ['status' => 'success','data'=>$empresas];
      return _send_json_response($this, 200, $response);
    }
}
