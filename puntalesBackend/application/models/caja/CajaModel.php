<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class CajaModel extends CI_Model {
  protected $table = 'caja'; 
  public function __construct() {
      parent::__construct();
  }
  public function findIdentity($id) {
      return $this->db->get_where($this->table, ['id' => $id])->row();
  }
  public function getId($caja) {
      return $caja->id ?? null;
  }
  public function findAll() {
    $this->db->select("*");
    $this->db->from($this->table ); 
    $this->db->where('estado', 0); 
    $query = $this->db->get();
    if ($query->num_rows() > 0) {
        return $query->result();
    } else {
        return array();
    }
  }
  public function findActive($id_usuario,$id_sucursal){
    $this->db->select("c.*,u.nombre as usuario");
    $this->db->from($this->table . ' as c');
    $this->db->join('usuarios as u', 'u.id_usuario = c.id_usuario', 'inner');
    $this->db->where('c.estado', 'Abierta'); 
    $this->db->where('c.id_sucursal', $id_sucursal); 
    $query = $this->db->get();
    if ($query->num_rows() > 0) {
        $turno =$query->result()[0];
        $turno->myTurno = ($turno->id_usuario===$id_usuario);
        return $turno; 
    } else {
        return array(); 
    }
  }
  public function create($data) {
    $data['estado'] = 'Abierta';
    $data['fecha_apertura'] = date('Y-m-d H:i:s');
    $this->db->insert($this->table, $data);
    return $this->db->insert_id();
  }
  public function delete($id) {
    $this->db->where('id', $id);
    return $this->db->update($this->table, ['estado'=>0]);
  }
  public function activate($id) {
    $this->db->where('id', $id);
    return $this->db->update($this->table, ['estado'=>'1']);
  }
  public function update($id, $data){
    $data['estado'] = 'Cerrada';
    $data['fecha_cierre'] = date('Y-m-d H:i:s');
    $this->db->where('id', $id);
    return $this->db->update($this->table, $data);
  }
  public function reportCierreTurno($idUsuario,$ifecha,$ffecha,$id_sucursal){
    $this->db->select("c.*, u.nombre as usuario,s.nombre as sucursal,
      COALESCE((SELECT SUM(p.monto) FROM pago p WHERE p.id_caja = c.id AND p.anulado = 'no' AND p.id_forma_pago = 1), 0.00) AS efectivo,
       COALESCE((SELECT SUM(p.monto) FROM pago p WHERE p.id_caja = c.id AND p.anulado = 'no' AND p.id_forma_pago IN (2,3)), 0.00) AS transferencia,
       COALESCE((SELECT SUM(p.monto) FROM pago p WHERE p.id_caja = c.id AND p.anulado = 'no' AND p.id_forma_pago not IN (1,2,3)), 0.00) AS otros,
       COALESCE((SELECT SUM(mc.monto) FROM movimientos_caja mc WHERE mc.id_caja = c.id AND mc.tipo = 'Ingreso' ), 0.00) AS ingresos,
       COALESCE((SELECT SUM(mc.monto) FROM movimientos_caja mc WHERE mc.id_caja = c.id AND mc.tipo = 'Engreso' ), 0.00) AS egresos");
    $this->db->from($this->table.' as c');
    $this->db->where("fecha_apertura >= '$ifecha 00:00.00'");
    $this->db->where("fecha_apertura <= '$ffecha 23:59:59'");
    //$this->db->where("'All'='$idUsuario' or c.id_usuario = '$idUsuario'");
    if ($idUsuario !== 'All') {
      $this->db->where("c.id_usuario", $idUsuario);
    }
    $this->db->join('usuarios as u','u.id_usuario = c.id_usuario','inner');
    $this->db->join('sucursal as s','s.id_sucursal = c.id_sucursal','inner');
    $this->db->where("c.id_sucursal", $id_sucursal);
    $this->db->order_by('fecha_apertura desc');
    $query = $this->db->get();
    if ($query->num_rows() > 0) {
        return $query->result(); 
    } else {
        return array(); 
    }
  }
}
