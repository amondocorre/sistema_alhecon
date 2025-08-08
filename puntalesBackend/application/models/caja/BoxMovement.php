<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class BoxMovement extends CI_Model {
  protected $table = 'movimientos_caja'; 
  public function __construct() { 
      parent::__construct();
  }
  public function findIdentity($id) {
      return $this->db->get_where($this->table, ['id_movimientos_caja' => $id])->row();
  }
  public function getId($movimientos_caja) {
      return $movimientos_caja->id_movimientos_caja ?? null;
  }
  public function findAll() {
    $this->db->select("*");
    $this->db->from($this->table ); 
    $query = $this->db->get();
    if ($query->num_rows() > 0) {
        return $query->result();
    } else {
        return array();
    }
  }
  public function findFilter($tipo,$ifecha,$ffecha,$id_sucursal){
    $this->db->select("mc.*, u.nombre as usuario,s.nombre as sucursal,");
    $this->db->from($this->table.' as mc');
    $this->db->where("fecha_movimiento >= '$ifecha'");
    $this->db->where("fecha_movimiento <= '$ffecha 23:59:59'");
    $this->db->join('usuarios as u','u.id_usuario = mc.id_usuario','inner');
    $this->db->join('sucursal as s','s.id_sucursal = mc.id_sucursal','inner');
    $this->db->where("mc.id_sucursal", $id_sucursal);
    $this->db->order_by('fecha_movimiento desc');
    $query = $this->db->get();
    if ($query->num_rows() > 0) {
        return $query->result(); 
    } else {
        return array(); 
    }
  }
  public function getMovimientosById($id_caja){
    $this->db->select("id_caja,SUM(CASE WHEN tipo = 'Ingreso' THEN monto ELSE 0.00 END) AS Ingreso,SUM(CASE WHEN tipo = 'Egreso' THEN monto ELSE 0.00 END) AS Egreso");
    $this->db->from($this->table);
    $this->db->where("id_caja", $id_caja);
    $this->db->group_by("id_caja");
    $query = $this->db->get();
    $result = $query->row_array(); 
    return $result;
  }
  public function getMontoPagoByIds($id_caja,$if_forma_pago){
    $this->db->select("sum(monto) as monto");
    $this->db->from('pago');
    $this->db->where("id_caja", $id_caja);
    $this->db->where("anulado", 'no');
    $this->db->where_in("id_forma_pago", $if_forma_pago);
    $this->db->group_by("id_caja");
    $query = $this->db->get();
    $result = $query->row_array(); 
    $monto = $result['monto']??'0.00';
    return $monto===null?'0.00':$monto;
  }
  public function getMontoPagoOtros($id_caja,$if_forma_pago){
    $this->db->select("sum(monto) as monto");
    $this->db->from('pago');
    $this->db->where("id_caja", $id_caja);
    $this->db->where("anulado", 'no');
    $this->db->where_not_in("id_forma_pago", $if_forma_pago);
    $this->db->group_by("id_caja");
    $query = $this->db->get();
    $result = $query->row_array(); 
    $monto = $result['monto']??'0.00';
    return $monto===null?'0.00':$monto;
  }
  public function create($data) {
    if (!$this->validate_movimientos_caja_data($data)) {
        return FALSE; 
    }
    $data['fecha_movimiento'] = date('Y-m-d H:i:s');
    $this->db->insert($this->table, $data);
    return $this->db->insert_id();
  }
  public function delete($id) {
    $this->db->where('id_movimientos_caja', $id);
    return $this->db->update($this->table, ['estado'=>0]);
  }
  public function activate($id) {
    $this->db->where('id_movimientos_caja', $id);
    return $this->db->update($this->table, ['estado'=>'1']);
  }
  public function update($id, $data) {
    if (!$this->validate_movimientos_caja_data($data, $id)) {
        return FALSE;
    }
    $this->db->where('id_movimientos_caja', $id);
    return $this->db->update($this->table, $data);
  }
  private function validate_movimientos_caja_data($data, $id_movimientos_caja = 0) {
    $this->form_validation->set_data($data);
    $this->form_validation->set_rules('id_usuario', 'id_usuario', 'required');
    $this->form_validation->set_rules('id_caja', 'id_caja', 'required');
    return $this->form_validation->run();
  }
}
