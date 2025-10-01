<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class TransferController extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->database(); 
        $this->load->model('TransferModel');
        $this->load->model('caja/CajaModel');
        $this->load->library('pdf');
    } 
    public function register() {
      if (!validate_http_method($this, ['POST'])) {
        return; 
      }
      $res = verifyTokenAccess();
      if(!$res){
        return;
      }
      $data = json_decode(file_get_contents('php://input'), false);
      $user = $res->user;
      $idUser = $user->id_usuario;
      $id = $this->TransferModel->register($data,$idUser);
      if ($id) {
        $response = new stdClass();
        $response->status = 'success';
        $response->message='Se registro con éxito la información.';
        return _send_json_response($this, 200, $response);
      } else {
        $response = ['status' => 'error', 'message' =>  'Ocurrio un error al intentar registrar la información.'];
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
      //$data = json_decode(file_get_contents('php://input'), false);
      $data = $this->input->post();
      $data = json_decode(json_encode($data),false);
      $files = $_FILES??null;
      $file = $files['doc']??null;
      $id_sucursal=$data->id_sucursal??0;
      $response = $this->TransferModel->update($id,$data);
      if ($response->status) {
        $response->status = 'success';
        $response->message='Se registro con éxito la informaión.';
        return _send_json_response($this, 200, $response);
      } else {
        $response = ['status' => 'error', 'message' =>  'Ocurrio un error al intentar registrar la información.'];
        return _send_json_response($this, 400, $response);
      }
    }
    public function list() {
      if (!validate_http_method($this, ['POST'])) return; 
      $res = verifyTokenAccess();
      if(!$res) return; 
      $data = json_decode(file_get_contents('php://input'), true);
      $i_fecha = $data['i_fecha']??'';
      $f_fecha = $data['f_fecha']??'';
      $id_sucursal = $data['id_sucursal']??'';
      $data = $this->TransferModel->list($id_sucursal,$i_fecha,$f_fecha);
      $response = ['status' => 'success','data'=>$data];
      return _send_json_response($this, 200, $response);
    }
}
