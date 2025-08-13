<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class CalendarController extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->database(); 
        $this->load->model('configurations/CalendarModel');
    } 
    public function obtenerFeriados() {
      if (!validate_http_method($this, ['POST'])) return; 
      $res = verifyTokenAccess();
      if(!$res) return; 
      $data = json_decode(file_get_contents('php://input'), false);
      $anio = $data->anio??'';
      $fechas = $this->CalendarModel->obtenerFeriados($anio);
      $response = ['status' => 'success','data'=>$fechas];
      return _send_json_response($this, 200, $response);
    }
    public function poblarCalendarioPorMes() {
      if (!validate_http_method($this, ['POST'])) return; 
      $res = verifyTokenAccess();
      if(!$res) return; 
      $data = json_decode(file_get_contents('php://input'), false);
      $anio = $data->anio??'';
      $mes = $data->mes??'';
      $fechas = $this->CalendarModel->poblarCalendarioPorMes($anio,$mes);
      if($fechas){
        $response = ['status' => 'success','message'=>$fechas];
        return _send_json_response($this, 200, $response);
      }else{
        $response = ['status' => 'error', 'message' =>  array_values($this->form_validation->error_array())];
        return _send_json_response($this, 400, $response);
      }
    }
    public function poblarCalendarioPorAño() {
      if (!validate_http_method($this, ['POST'])) return; 
      $res = verifyTokenAccess();
      if(!$res) return; 
      $data = json_decode(file_get_contents('php://input'), false);
      $anio = $data->anio??'';
      $fechas = $this->CalendarModel->poblarCalendarioPorAño($anio);
      if($fechas){
        $response = ['status' => 'success','message'=>'Se Genero correctamente el calendario.'];
        return _send_json_response($this, 200, $response);
      }else{
        $response = ['status' => 'error', 'message' =>  array_values($this->form_validation->error_array())];
        return _send_json_response($this, 400, $response);
      }
    }
    public function updateDate() {
      if (!validate_http_method($this, ['POST'])) return; 
      $res = verifyTokenAccess();
      if(!$res) return; 
      $data = json_decode(file_get_contents('php://input'), false);
      $es_feriado = $data->es_feriado??'';
      $es_laboral = $data->es_laboral??'';
      $fecha = $data->fecha??'';
      $nombre_feriado = $data->nombre_feriado??'';
      $respuesta = $this->CalendarModel->updateDate($fecha,$es_feriado,$es_laboral,$nombre_feriado);
      if($respuesta){
        $response = ['status' => 'success','message'=>'Se Genero correctamente el calendario.'];
        return _send_json_response($this, 200, $response);
      }else{
        $response = ['status' => 'error', 'message' =>  array_values($this->form_validation->error_array())];
        return _send_json_response($this, 400, $response);
      }
    }
    public function getCalendarioByAnio($anio) {
      if (!validate_http_method($this, ['GET'])) return; 
      $res = verifyTokenAccess();
      if(!$res) return; 
      $data = $this->CalendarModel->getCalendarioByAnio($anio);
      $response = ['status' => 'success','data'=>$data];
      return _send_json_response($this, 200, $response);
    }
    public function obtenerLaborales() {
      if (!validate_http_method($this, ['GET'])) return; 
      $res = verifyTokenAccess();
      if(!$res) return; 
      $cantidadMeses = 12;
      $fechas = $this->CalendarModel->obtenerLaborales($cantidadMeses);
      $response = ['status' => 'success','data'=>$fechas];
      return _send_json_response($this, 200, $response);
    }
}
