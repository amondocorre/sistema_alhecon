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
    public function registerPagoDeuda() {
      if (!validate_http_method($this, ['POST']))return; 
      $res = verifyTokenAccess();
      if(!$res)return;
      $user = $res->user;
      $idUser = $user->id_usuario;
      $data = json_decode(file_get_contents('php://input'), false);
      if (!$data || !isset($data->id_contrato, $data->id_forma_pago, $data->a_cuenta)) {
        return _send_json_response($this, 400, ['status' => 'error', 'message' => 'Datos incompletos.']);
      }
      $idDocumento = $data->id_contrato;
      $idFormaPago = $data->id_forma_pago;
      $aCuenta = $data->a_cuenta;
      $resContrato = $this->RentModel->getDataContratoByID($idDocumento);
      $resContrato = json_decode(json_encode($resContrato),false);
      $contrato = $resContrato->contrato??null;
      if (!$contrato) {
        return _send_json_response($this, 400, ['status' => 'error','message' => "No se encontró el contrato con ID $idDocumento."]);
      }
      $deuda = (float)$contrato->total_pagar - (float)$contrato->monto_pagado;
      if ($aCuenta>$deuda) {
        return _send_json_response($this, 400, ['status' => 'error','message'=>'El monto recibido es mayor a la deuda por favor verifique bien los datos.']);
      }
      $idSucursal=$contrato->id_sucursal??0;
      $turno = $this->CajaModel->findActive($idUser,$idSucursal);
      if (!$turno) {
        return _send_json_response($this, 400, ['status' => 'error','message'=>'No se encontro ningun turno abierto.']);
      }
      if (!$turno->myTurno) {
        return _send_json_response($this, 400, ['status' => 'error','message'=>'Solo el usuario que aperturo puede registrar la información.']);
      }
      $idTurno = $turno->id;
      $idCliente = $contrato->id_cliente;
      $response = $this->RentModel->registerPagoDeuda($idTurno,$idSucursal,$idUser,$idDocumento,$idFormaPago,$aCuenta,$idCliente);
      if ($response->status) {
        $response->status = 'success';
        $response->message='Se registro con éxito el pago.';
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
    public function getEstadoAlquiler() {
      if (!validate_http_method($this, ['GET'])) return; 
      $res = verifyTokenAccess();
      if(!$res) return; 
      $data = $this->RentModel->getEstadoAlquiler();
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
    public function getAlquilerDeuda($id_sucursal) {
      if (!validate_http_method($this, ['GET'])) return; 
      $res = verifyTokenAccess();
      if(!$res) return; 
      $data = $this->RentModel->getAlquilerDeuda($id_sucursal);
      $response = ['status' => 'success','data'=>$data];
      return _send_json_response($this, 200, $response);
    }
}
