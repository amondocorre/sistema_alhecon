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
        $response = ['status' => 'success','me'=>$fechas];
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
      $response = ['status' => 'success','data'=>$fechas];
      return _send_json_response($this, 200, $response);
    }
}
