<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class DashboardController extends CI_Controller {

  public function getArrivalsDepartures() {
    $this->load->model('dashboard/DashboardModel');
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

  public function getIngresosDiarios() {
    $this->load->model('dashboard/DashboardModel');
    $data = $this->DashboardModel->get_ingresos_diarios();
    echo json_encode($data);
  }
}
