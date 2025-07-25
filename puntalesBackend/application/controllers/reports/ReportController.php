<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ReportController extends CI_Controller {
  public function __construct() {
    parent::__construct();
    $this->load->database(); 
    $this->load->model('configurations/Company');
    $this->load->model('configurations/PaymentMethod');
    $this->load->model('caja/CajaModel');
    $this->load->model('auth/User_model');
    $this->load->model('caja/BoxMovement');
  } 
  public function reportCierreTurno() {
   if (!validate_http_method($this, ['POST'])) return; 
    $res = verifyTokenAccess();
    if(!$res) return; 
    $data = json_decode(file_get_contents('php://input'), true);
    $id_usuario = $data['id_usuario']??'All';
    $i_fecha = $data['i_fecha']??'';
    $f_fecha = $data['f_fecha']??'';
    if (!$i_fecha) {
      $i_fecha = date('Y-m-d');
      $f_fecha = date('Y-m-d');
    }
    $cajas = $this->CajaModel->reportCierreTurno($id_usuario,$i_fecha,$f_fecha);
    foreach($cajas as $key=>$caja){
      $idCaja = $caja->id;
      $ingesos = $caja->ingresos??0.00;
      $egesos = $caja->egresos??0.00;
      $efectivos = $caja->efectivo??0.00;
      $transferencias = $caja->efectivos??0.00;
      $otros = $caja->otros??0.00;
      $montoInicial = $caja->monto_inicial?$caja->monto_inicial:0.00;
      $saldoTeorico = ($ingesos + $efectivos + $transferencias + $otros + $montoInicial) - $egesos;
      $saldoReal = $caja->monto_final?$caja->monto_final:0.00;
      $descuadre =  $saldoReal-$saldoTeorico;
      $caja->montoInicial = $montoInicial.' Bs.';
      $caja->ingresos = $ingesos.' Bs.';
      $caja->egresos = $egesos.' Bs.';
      $caja->efectivo = $efectivos.' Bs.';
      $caja->transferencia = $transferencias.' Bs.';
      $caja->otros = $otros.' Bs.';
      $caja->saldoTeorico = $saldoTeorico.' Bs.';
      $caja->saldoReal = $saldoReal.' Bs.';
      $caja->descuadre =  number_format((float)$descuadre, 2, '.', '').' Bs.';
    }
    $response = ['status' => 'success','data'=>$cajas];
    return _send_json_response($this, 200, $response);
  }
  public function reportContratos() {
   if (!validate_http_method($this, ['POST'])) return; 
    $res = verifyTokenAccess();
    if(!$res) return; 
    $data = json_decode(file_get_contents('php://input'), true);
    $i_fecha = $data['i_fecha']??'';
    $f_fecha = $data['f_fecha']??'';
    if (!$i_fecha) {
      $i_fecha = date('Y-m-d');
      $f_fecha = date('Y-m-d');
    }
    $sql = "CALL getClienteContrato('$i_fecha','$f_fecha');";
    $query = $this->db->query($sql);
    $clientes = $query->result_array();
    $query->free_result(); 
    $this->db->close();
    $this->db->initialize();
    foreach($clientes as $key=>$cliente){
      $clientes[$key]['detalle'] = $cliente['detalle']?json_decode(utf8_encode($cliente['detalle'])):[];
    }
    $response = ['status' => 'success','data'=>$clientes];
    return _send_json_response($this, 200, $response);
  }
  public function reportContratoDeudas() {
   if (!validate_http_method($this, ['POST'])) return; 
    $res = verifyTokenAccess();
    if(!$res) return; 
    $data = json_decode(file_get_contents('php://input'), true);
    $i_fecha = $data['i_fecha']??'';
    $f_fecha = $data['f_fecha']??'';
    if (!$i_fecha) {
      $i_fecha = date('Y-m-d');
      $f_fecha = date('Y-m-d');
    }
    $sql = "CALL getClienteContratoDeuda('$i_fecha','$f_fecha');";
    $query = $this->db->query($sql);
    $clientes = $query->result_array();
    $query->free_result(); 
    $this->db->close();
    $this->db->initialize();
    foreach($clientes as $key=>$cliente){
      $clientes[$key]['detalle'] = $cliente['detalle']?json_decode(utf8_encode($cliente['detalle'])):[];
    }
    $response = ['status' => 'success','data'=>$clientes];
    return _send_json_response($this, 200, $response);
  }
}