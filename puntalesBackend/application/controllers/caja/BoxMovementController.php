<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class BoxMovementController extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->database(); 
        $this->load->model('caja/BoxMovement');
        $this->load->model('caja/CajaModel');
    } 
    public function create($id_sucursal) {
      if (!validate_http_method($this, ['POST'])) {
        return;
      }
      $res = verifyTokenAccess();
      if(!$res){
        return;
      }
      $user = $res->user;
      $idUser = $user->id_usuario;
      $data = json_decode(file_get_contents('php://input'), true);
      //$id_sucursal = $data['id_sucursal']??0;
      $turno = $this->CajaModel->findActive($idUser,$id_sucursal);
      if (!$turno) {
        $response = ['status' => 'error','message'=>'No se encontro ningun turno abierto para la sucursal selecionada.'];
        return _send_json_response($this, 400, $response);
      }
      if (!$turno->myTurno) {
        $response = ['status' => 'error','message'=>'El registro solo puede realizar el usuario que aperturo el turo.'];
        return _send_json_response($this, 400, $response);
      }
      $data['id_usuario'] = $idUser;
      $data['id_caja'] = $turno->id;
      $data['id_sucursal'] = $id_sucursal;
      $id = $this->BoxMovement->create($data);
      if ($id) {
          $response = ['status' => 'success','message'=>'Se registro con éxito el movimiento de caja.','id'=>$id];
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
      $turno = $this->BoxMovement->findActive($idUser);
      if (!$turno) {
        $response = ['status' => 'error','message'=>'No se encontro ningun turno abierto.'];
        return _send_json_response($this, 400, $response);
      }
      if (!$turno->myTurno) {
        $response = ['status' => 'success','message'=>'El turno solo puede cerrar e usuario que aperturo.'];
        return _send_json_response($this, 400, $response);
      }
      $data = json_decode(file_get_contents('php://input'), true);
      if ($this->BoxMovement->update($id, $data)) {
          $response = ['status' => 'success','message'=>'Se cerro el turno con éxito.'];
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
      if ($this->BoxMovement->delete($id)) {
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
      if ($this->BoxMovement->activate($id)) {
          $response = ['status' => 'success','message'=>'Metodo de pago Habilitado con éxito.'];
          return _send_json_response($this, 200, $response);
      } else {
        $response = ['status' => 'error', 'message' => 'Ocurrio un eror al internatar Habilitar el metodo de pago.'];
        return _send_json_response($this, 400, $response);
      }
    }
    public function findFilter() {
      if (!validate_http_method($this, ['POST'])) return; 
      $res = verifyTokenAccess();
      if(!$res) return; 
      $data = json_decode(file_get_contents('php://input'), true);
      $id_sucursal = $data['id_sucursal']??'0';
      $ifecha = $data['ifecha']??'';
      $ffecha = $data['ffecha']??'';
      $tipo = $data['tipo']??'';
      $res = $this->BoxMovement->findFilter($tipo,$ifecha,$ffecha,$id_sucursal);
      $response = ['status' => 'success','data'=>$res];
      return _send_json_response($this, 200, $response);
    }
    public function findAll() {
      if (!validate_http_method($this, ['GET'])) return; 
      $res = verifyTokenAccess();
      if(!$res) return; 
      $Mascotaes = $this->BoxMovement->findAll();
      $response = ['status' => 'success','data'=>$Mascotaes];
      return _send_json_response($this, 200, $response);
    }
}
