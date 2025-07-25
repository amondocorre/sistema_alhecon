<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class RentController extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->database(); 
        $this->load->model('RentModel');
        $this->load->model('Client_model');
        $this->load->model('configurations/PaymentMethod');
        $this->load->model('configurations/ComboModel');
        $this->load->model('configurations/ProductModel');
        $this->load->model('caja/CajaModel');
        $this->load->library('pdf');
    } 
    public function registerRent() {
      if (!validate_http_method($this, ['POST'])) {
        return; 
      }
      $res = verifyTokenAccess();
      if(!$res){
        return;
      } 
      $id_sucursal=0;
      $data = json_decode(file_get_contents('php://input'), false);
      $sucursal = $data->sucursal??null;
      if ($sucursal) {
        $response = ['status' => 'error','message'=>'Debe seleccionar una sucursal.'];
        return _send_json_response($this, 400, $response);
      }
      $id_sucursal = $sucursal->id_sucursal??1;
      $user = $res->user;
      $idUser = $user->id_usuario;
      $turno = $this->CajaModel->findActive($idUser,$id_sucursal);
      if (!$turno) {
        $response = ['status' => 'error','message'=>'No se encontro ningun turno abierto.'];
        return _send_json_response($this, 400, $response);
      }
      if (!$turno->myTurno) {
        $response = ['status' => 'error','message'=>'Solo el usuario que aperturo puede realizar el registro.'];
        return _send_json_response($this, 400, $response);
      }
      $response = $this->RentModel->registerRent($data,$turno,$idUser);
      if ($response->status) {
        $response->status = 'success';
        $response->message='Se registro con éxito la información.';
        return _send_json_response($this, 200, $response);
      } else {
        $response = ['status' => 'error', 'message' =>  'Ocurrio un error al intentar registrar la información.'];
        return _send_json_response($this, 400, $response);
      }
    }
    public function registerReturn() {
      if (!validate_http_method($this, ['POST'])) {
        return; 
      }
      $res = verifyTokenAccess();
      if(!$res){
        return;
      } 
      $user = $res->user;
      $idUser = $user->id_usuario;
      //$data = json_decode(file_get_contents('php://input'), false);
      $data = $this->input->post();
      $data = json_decode(json_encode($data),false);
      $files = $_FILES??null;
      $id_sucursal=$data->id_sucursal??0;
      $turno = $this->CajaModel->findActive($idUser,$id_sucursal);
      if (!$turno) {
        $response = ['status' => 'error','message'=>'No se encontro ningun turno abierto.'];
        return _send_json_response($this, 400, $response);
      }
      if (!$turno->myTurno) {
        $response = ['status' => 'error','message'=>'Solo el usuario que aperturo puede registrar la información.'];
        return _send_json_response($this, 400, $response);
      }
      $response = $this->RentModel->registerReturn($data,$turno,$idUser,$files);
      if ($response->status) {
        $response->status = 'success';
        $response->message='Se registro con éxito la recpcion.';
        return _send_json_response($this, 200, $response);
      } else {
        $response = ['status' => 'error', 'message' =>  'Ocurrio un error al intentar registrar la información.'];
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
      if ($this->RentModel->delete($id)) {
          $response = ['status' => 'success','message'=>'Metodo de pago eliminado con éxito.'];
          return _send_json_response($this, 200, $response);
      } else {
        $response = ['status' => 'error', 'message' =>  'Ocurrio un eror al internatar eliminar el metodo de pago.'];
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
      if ($this->RentModel->activate($id)) {
          $response = ['status' => 'success','message'=>'Metodo de pago Habilitado con éxito.'];
          return _send_json_response($this, 200, $response);
      } else {
        $response = ['status' => 'error', 'message' => 'Ocurrio un eror al internatar Habilitar el metodo de pago.'];
        return _send_json_response($this, 400, $response);
      }
    }
    public function listRentals() {
      if (!validate_http_method($this, ['POST'])) return; 
      $res = verifyTokenAccess();
      if(!$res) return; 
      $data = json_decode(file_get_contents('php://input'), true);
      $estado = $data['id_estado']??'0';
      $i_fecha = $data['i_fecha']??'';
      $f_fecha = $data['f_fecha']??'';
      $id_sucursal = $data['id_sucursal']??'';
      $data = $this->RentModel->getAlquilereFilter($id_sucursal,$estado,$i_fecha,$f_fecha);
      $response = ['status' => 'success','data'=>$data];
      return _send_json_response($this, 200, $response);
    }
    public function getAlquilerById($id) {
      if (!validate_http_method($this, ['GET'])) return; 
      $res = verifyTokenAccess();
      if(!$res) return; 
      $response = new stdClass();
      $response->status = 'success';
      $response->estados = $this->RentModel->getEstados();
      $response->productos = $this->RentModel->getAlquilerById($id);;
      $response->formasPago = $this->PaymentMethod->findActive();
      return _send_json_response($this, 200, $response);
    }
    public function getRentById($id_sucursal) {
      if (!validate_http_method($this, ['POST'])) return; 
      $res = verifyTokenAccess();
      if(!$res) return; 
      $data = json_decode(file_get_contents('php://input'), true);
      $idsIngreso = $data['idsIngreso']??[];
      $data = $this->RentModel->getRentById($idsIngreso,$id_sucursal);
      $response = ['status' => 'success','data'=>$data];
      return _send_json_response($this, 200, $response);
    }
    public function getDataRequerid() {
      if (!validate_http_method($this, ['GET'])) return; 
      $res = verifyTokenAccess();
      if(!$res) return; 
      $response = new stdClass();
      $response->status = 'success';
      $response->clientes = $this->Client_model->findActive();
      $response->productos = $this->ProductModel->findActive();
      $response->combos = $this->ComboModel->findActive();
      $response->formasPago = $this->PaymentMethod->findActive();
      return _send_json_response($this, 200, $response);
    }

}
