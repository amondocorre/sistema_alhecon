<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class CajaController extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->database(); 
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
      $turno = $this->CajaModel->findActive($idUser,$id_sucursal);
      if ($turno) {
        $response = ['status' => 'error','message'=>'Existe un turno abierto.'];
        return _send_json_response($this, 400, $response);
      }
      $data = json_decode(file_get_contents('php://input'), true);
      $data['id_usuario'] = $idUser;
      $data['id_sucursal'] = $id_sucursal;
      $id = $this->CajaModel->create($data);
      if ($id) {
          $response = ['status' => 'success','message'=>'Se aperturo con éxito el Turno.','id'=>$id];
          return _send_json_response($this, 200, $response);
      } else {
        $response = ['status' => 'error', 'message' =>  array_values($this->form_validation->error_array())];
        return _send_json_response($this, 400, $response);
      }
    }
    public function update($id,$id_sucursal) {
      if (!validate_http_method($this, ['POST'])) {
        return; 
      }
      $res = verifyTokenAccess();
      if(!$res){
        return;
      } 
      $user = $res->user;
      $idUser = $user->id_usuario;
      $turno = $this->CajaModel->findActive($idUser,$id_sucursal);
      if (!$turno) {
        $response = ['status' => 'error','message'=>'No se encontro ningun turno abierto.'];
        return _send_json_response($this, 400, $response);
      }
      if (!$turno->myTurno) {
        $response = ['status' => 'error','message'=>'El turno solo puede cerrar el usuario que aperturo el turno.'];
        return _send_json_response($this, 400, $response);
      }
      $data = json_decode(file_get_contents('php://input'), true);
      if ($this->CajaModel->update($id, $data)) {
          $response = ['status' => 'success','message'=>'Se cerro el turno con éxito.','id'=>$id];
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
      if ($this->CajaModel->delete($id)) {
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
      if ($this->CajaModel->activate($id)) {
          $response = ['status' => 'success','message'=>'Metodo de pago Habilitado con éxito.'];
          return _send_json_response($this, 200, $response);
      } else {
        $response = ['status' => 'error', 'message' => 'Ocurrio un eror al internatar Habilitar el metodo de pago.'];
        return _send_json_response($this, 400, $response);
      }
    }
    public function findActive($id_sucursal) {
      if (!validate_http_method($this, ['GET'])) return; 
      $res = verifyTokenAccess();
      if(!$res) return; 
      $user = $res->user;
      $idUser = $user->id_usuario;
      $Mascotaes = $this->CajaModel->findActive($idUser,$id_sucursal);
      $response = ['status' => 'success','data'=>$Mascotaes];
      return _send_json_response($this, 200, $response);
    }
    public function findAll() {
      if (!validate_http_method($this, ['GET'])) return; 
      $res = verifyTokenAccess();
      if(!$res) return; 
      $Mascotaes = $this->CajaModel->findAll();
      $response = ['status' => 'success','data'=>$Mascotaes];
      return _send_json_response($this, 200, $response);
    }
}
