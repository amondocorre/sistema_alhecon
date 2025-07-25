<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class PaymentMethod extends CI_Model {
  protected $table = 'forma_pago'; 
  public function __construct() {
      parent::__construct();
  }
  public function findIdentity($id) {
      return $this->db->get_where($this->table, ['id_forma_pago' => $id])->row();
  }
  public function getId($forma_pago) {
      return $forma_pago->id_forma_pago ?? null;
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
  public function findActive(){
    $this->db->select("*");
    $this->db->from($this->table); 
    $this->db->where('estado', 1);
    $query = $this->db->get();
    if ($query->num_rows() > 0) {
        return $query->result(); 
    } else {
        return array(); 
    }
  }
  public function create($data) {
    if (!$this->validate_forma_pago_data($data)) {
        return FALSE; 
    }
    $data['estado'] = '1';
    return $this->db->insert($this->table, $data);
  }
  public function delete($id) {
    $this->db->where('id_forma_pago', $id);
    return $this->db->update($this->table, ['estado'=>0]);
  }
  public function activate($id) {
    $this->db->where('id_forma_pago', $id);
    return $this->db->update($this->table, ['estado'=>'1']);
  }
  public function update($id, $data) {
    if (!$this->validate_forma_pago_data($data, $id)) {
        return FALSE;
    }
    $this->db->where('id_forma_pago', $id);
    return $this->db->update($this->table, $data);
  }
  private function validate_forma_pago_data($data, $id_forma_pago = 0) {
    $this->form_validation->set_data($data);
    $this->form_validation->set_rules('nombre', 'Nombre', 'required|max_length[100]');
    return $this->form_validation->run();
  }
}
