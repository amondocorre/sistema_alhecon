<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class DashboardController extends CI_Controller {

  public function getArrivalsDepartures() {
    $this->load->model('dashboard/DashboardModel');
    $this->load->model('RentModel');
    $data = $this->DashboardModel->fetch_arrivals_departures();
    echo json_encode($data);
  }

  public function getOccupation() {
    $this->load->model('dashboard/DashboardModel');
    $data = $this->DashboardModel->fetch_occupation();
    echo json_encode($data);
  }

  public function getTotalClientes() {
    $this->load->model('dashboard/DashboardModel');
    $data = $this->DashboardModel->get_total_clientes();
    echo json_encode($data);
  }

  public function getMascotasEstancia() {
    $this->load->model('dashboard/DashboardModel');
    $data = $this->DashboardModel->get_mascotas_estancia();
    echo json_encode($data);
  }

  public function getIngresosDiarios($id_sucursal) {
    $this->load->model('dashboard/DashboardModel');
    $data = $this->DashboardModel->get_ingresos_diarios($id_sucursal);
    echo json_encode($data);
  }
  public function getTotalesInventario($id_sucursal) {
    $this->load->model('dashboard/DashboardModel');
    $data = $this->DashboardModel->getTotalesInventario($id_sucursal);
    echo json_encode($data);
  }
  public function listRent($idSucursal) {
    $this->load->model('RentModel');
    $data = json_decode(file_get_contents('php://input'), true);
    $page = $data['page'] ?? 1;
    $limit = $data['limit'] ?? 10;
    $offset = ($page - 1) * $limit;
    $data = $this->RentModel->getAlquileres($limit, $offset,$idSucursal);
    $total = $this->RentModel->getTotalAlquileres($idSucursal);
    $response = [
      'data' => $data,
      'pagination' => [
        'total' => $total,
        'page' => (int) $page,
        'limit' => (int) $limit,
        'totalPages' => ceil($total / $limit)
      ]
    ];
    echo json_encode($response);
  }
  public function listRentEntrega($idSucursal) {
    $this->load->model('RentModel');
    $data = json_decode(file_get_contents('php://input'), true);
    $page = $data['page'] ?? 1;
    $limit = $data['limit'] ?? 10;
    $offset = ($page - 1) * $limit;
    $data = $this->RentModel->getAlquileresEntrega($limit, $offset,$idSucursal);
    $total = $this->RentModel->getTotalAlquileresEntrega($idSucursal);
    $response = [
      'data' => $data,
      'pagination' => [
        'total' => $total,
        'page' => (int) $page,
        'limit' => (int) $limit,
        'totalPages' => ceil($total / $limit)
      ]
    ];
    echo json_encode($response);
  }
  public function getDetailRent($idContrato) {
    if (!validate_http_method($this, ['GET'])) return; 
    ///$res = verifyTokenAccess();
    $this->load->model('RentModel');
    $data = $this->RentModel->getAlquilerById($idContrato);
    $productos = $data['id_estado_alquiler']==2?$this->RentModel->getProductosAlquilerById($idContrato):[];
    $response = [
      'data' => $data,
      'productos'=>$productos     
    ];
    echo json_encode($response);
  }
}
