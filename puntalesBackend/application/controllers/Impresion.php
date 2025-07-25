<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Impresion extends CI_Controller {
  public function __construct() {
    parent::__construct();
    $this->load->database(); 
    $this->load->model('configurations/Company');
    $this->load->model('configurations/PaymentMethod');
    $this->load->model('RentModel');
    $this->load->model('caja/CajaModel');
    $this->load->model('auth/User_model');
    $this->load->model('caja/BoxMovement');
    $this->load->model('configurations/SucursalModel');
  } 
  public function imprimirMovimientoCaja($idMovimiento) {
   if (!validate_http_method($this, ['POST'])) return; 
    $res = verifyTokenAccess();
    if(!$res) return; 
    $data = json_decode(file_get_contents('php://input'), true);
    $movi = $this->BoxMovement->findIdentity($idMovimiento);
    if(!$movi){
      return _send_json_response($this, 204 , ['status' => 'error','message'=>'No se encotro el movimiento.']);
    }
    $idUSer = $movi->id_usuario;
    $idSuc = $movi->id_sucursal;
    $user = $this->User_model->findIdentity($idUSer);
    $company = $this->Company->findIdentity(1);
    $sucursal = $this->SucursalModel->findIdentity($idSuc);
    $objeto = new stdClass();
    $objeto->empresa = strtoupper($company->nombre.'');//S.R.L.
    $objeto->sucursal = strtoupper($sucursal->nombre.'');//S.R.L.
    $objeto->usuario = $user->nombre;
    $objeto->tipoMovimieno = strtoupper($movi->tipo);
    $objeto->descripcionIE = "";
    $objeto->monto = $movi->monto.' Bs.';
    $objeto->descripcion = ucfirst(strtolower($movi->descripcion));
    $objeto->fecha = date('d-m-Y', strtotime($movi->fecha_movimiento));
    $objeto->hora = date('h:i:s A', strtotime($movi->fecha_movimiento));;
    $datos['json'] = json_encode($objeto);
    $this->load->view('impresion/movimientoCaja', $datos, FALSE);    
    $response = ['status' => 'success','data'=>$company];
    //return _send_json_response($this, 200, $response);
  }
  public function imprimirAperturaTurno($id) {
    if (!validate_http_method($this, ['POST'])) return; 
    $res = verifyTokenAccess();
    if(!$res) return; 
    $data = json_decode(file_get_contents('php://input'), true);
    $caja = $this->CajaModel->findIdentity($id);
    if(!$caja){
      return _send_json_response($this, 204 , ['status' => 'error','message'=>'No se encontro la apertura de caja.']);
    }
    $idUSer = $caja->id_usuario;
    $user = $this->User_model->findIdentity($idUSer);
    $company = $this->Company->findIdentity(1);
    $idSuc = $caja->id_sucursal;
    $sucursal = $this->SucursalModel->findIdentity($idSuc);
    $objeto = new stdClass();
    $objeto->empresa = strtoupper($company->nombre.'');//S.R.L.
    $objeto->usuario = $user->nombre;
    $objeto->sucursal = strtoupper($sucursal->nombre.'');//S.R.L.
    $objeto->monto = $caja->monto_inicial.' Bs.';
    $objeto->fecha = date('d-m-Y', strtotime($caja->fecha_apertura));
    $objeto->hora = date('h:i:s A', strtotime($caja->fecha_apertura));;
    $datos['json'] = json_encode($objeto);
    $this->load->view('impresion/AperturaCaja', $datos, FALSE); 
    //$response = ['status' => 'success','data'=>$objeto];
    //return _send_json_response($this, 200, $response);
  }
  public function imprimirCierreTurno($id) {
    /*if (!validate_http_method($this, ['POST'])) return; 
    $res = verifyTokenAccess();
    if(!$res) return; */
    $data = json_decode(file_get_contents('php://input'), true);
    $caja = $this->CajaModel->findIdentity($id);
    if(!$caja){
      return _send_json_response($this, 204 , ['status' => 'error','message'=>'No se encontro la apertura de caja.']);
    }
    $idSuc = $caja->id_sucursal;
    $sucursal = $this->SucursalModel->findIdentity($idSuc);
    $movi = $this->BoxMovement->getMovimientosById($id);
    $ingesos = $movi['Ingreso']??0.00;
    $egesos = $movi['Egreso']??0.00;
    $efectivos = $this->BoxMovement->getMontoPagoByIds($id,[1]);
    $transferencias = $this->BoxMovement->getMontoPagoByIds($id,[2,3]);
    $otros = $this->BoxMovement->getMontoPagoOtros($id,[1,2,3]);
    $montoInicial = $caja->monto_inicial?$caja->monto_inicial:0.00;
    $saldoTeorico = ($ingesos + $efectivos + $transferencias + $otros + $montoInicial) - $egesos;
    $saldoReal = $caja->monto_final?$caja->monto_final:0.00;
    $descuadre =  $saldoReal-$saldoTeorico;
    $idUSer = $caja->id_usuario;
    $user = $this->User_model->findIdentity($idUSer);
    $company = $this->Company->findIdentity(1);
    $objeto = new stdClass();
    $objeto->empresa = strtoupper($company->nombre.'');//S.R.L.
    $objeto->sucursal = strtoupper($sucursal->nombre.'');//S.R.L.
    $objeto->usuario = $user->nombre;
    $objeto->fechaIngreso = date('d-m-Y', strtotime($caja->fecha_apertura)).' '.date('h:i:s A', strtotime($caja->fecha_apertura));
    $objeto->fechaSalida = $caja->fecha_cierre?date('d-m-Y', strtotime($caja->fecha_cierre)).' '.date('h:i:s A', strtotime($caja->fecha_cierre)):'';
    $objeto->montoInicial = $montoInicial.' Bs.';
    $objeto->ingresos = $ingesos.' Bs.';
    $objeto->egresos = $egesos.' Bs.';
    $objeto->efectivo = $efectivos.' Bs.';
    $objeto->transferencia = $transferencias.' Bs.';
    $objeto->otros = $otros.' Bs.';
    $objeto->saldoTeorico = $saldoTeorico.' Bs.';
    $objeto->saldoReal = $saldoReal.' Bs.';
    $objeto->descuadre =  number_format((float)$descuadre, 2, '.', '').' Bs.';
    $datos['json'] = json_encode($objeto);
    $this->load->view('impresion/CierreCaja', $datos, FALSE); 
    //$response = ['status' => 'success','data'=>$objeto];
    //return _send_json_response($this, 200, $response);
    
  }
  public function imprimirContrato($numero) {
    if (!validate_http_method($this, ['GET'])) return; 
    $res = verifyTokenAccess();
    if(!$res) return; 
    $data = json_decode(file_get_contents('php://input'), true);
    $url = getHttpHost();
    $data = $this->RentModel->getDataContratoByID($numero);
    $data = json_decode(json_encode($data),false);
    $company = $this->Company->findIdentity(1);
    $objeto = new stdClass();
    $data->contrato->literal = construirLiteral($data->contrato->total);
    $data->empresa = strtoupper($company->nombre??'');
    $data->direccion = $company->direccion??'';
    $data->nit = $company->nit??'';
    $data->celular = $company->celular??'';
    $data->contrato->numero = $numero;
    $data->contrato->fecha_emision = date('d-m-Y', strtotime($data->contrato->fecha_emision));
    $data->contrato->fecha_entrega = date('d-m-Y', strtotime($data->contrato->fecha_entrega));
    $data->contrato->fecha_devolucion = date('d-m-Y', strtotime($data->contrato->fecha_devolucion));
    //$data->hora = date('h:i:s A', strtotime($data->fecha));
    $data->logo = $company->logo_impresion?$url.$company->logo_impresion:'';
    $datos['json'] = json_encode($data);
    $this->load->view('impresion/NotaVenta', $datos, FALSE); 
    //$response = ['status' => 'success','data'=>$data];
    //return _send_json_response($this, 200, $data);
  }
  public function imprimirReciboPago($idPago) {
    if (!validate_http_method($this, ['POST'])) return; 
    $res = verifyTokenAccess();
    if(!$res) return; 
    $data = json_decode(file_get_contents('php://input'), true);
    $sql = "CALL getPagoById(?)";
    $query = $this->db->query($sql, [$idPago]);
    $pago = $query->result();
    $query->free_result(); 
    $this->db->close();
    $this->db->initialize();
    $url = getHttpHost();
    if(!$pago)return;
    $pago = $pago[0] ?? null;
    $company = $this->Company->findIdentity(1);
    $objeto = new stdClass();
    $pago->literal = construirLiteral($pago->monto);
    $pago->empresa = strtoupper($company->nombre??'');
    $pago->direccion = $company->direccion??'';
    $pago->nit = $company->nit??'';
    $pago->celular = $company->celular??'';
    $pago->numero = $idPago;
    $pago->montoAtraso = number_format($pago->montoAtraso,2);
    $pago->fecha = date('d-m-Y', strtotime($pago->fecha));
    $pago->hora = date('h:i:s A', strtotime($pago->fecha));
    $pago->logo = $company->logo_impresion?$url.$company->logo_impresion:'';
    $datos['json'] = json_encode($pago);
    $this->load->view('impresion/ReciboPago', $datos, FALSE); 
    $response = ['status' => 'success','data'=>$pago];
    //return _send_json_response($this, 200, $response);
  }
}