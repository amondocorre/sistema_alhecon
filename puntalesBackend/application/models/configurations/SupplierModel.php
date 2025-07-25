<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class SupplierModel extends CI_Model {
    protected $table = 'proveedor'; 
    public function __construct() {
        parent::__construct();
    }
  public function findIdentity($id) {
      return $this->db->get_where($this->table, ['id_proveedor' => $id])->row();
  }
  public function getId($proveedor) {
      return $proveedor->id_proveedor ?? null;
  }
  public function findAll() {
    $this->db->select("*");
    $this->db->from($this->table . ' AS p'); 
    $query = $this->db->get(); 
    if ($query->num_rows() > 0) {
        return $query->result();
    } else {
        return array(); 
    }
  }
  public function findActive(){
    $this->db->select("*");
    $this->db->from($this->table . ' AS p'); 
    //$this->db->where('s.estado', 1);
    $query = $this->db->get();
    if ($query->num_rows() > 0) {
        return $query->result(); 
    } else {
        return array(); 
    }
  }
  public function create($data) {
    if (!$this->validate_pet_data($data)) {
        return FALSE; 
    }
    $data['estado'] = '1';
    return $this->db->insert($this->table, $data);
    //return $this->db->insert_id();
  }
  public function delete($id) {
    $this->db->where('id_proveedor', $id);
    return $this->db->update($this->table, ['estado'=>0]);
  }
  public function activate($id) {
    $this->db->where('id_proveedor', $id);
    return $this->db->update($this->table, ['estado'=>'1']);
  }
  public function update($id, $data) {
    if (!$this->validate_pet_data($data, $id)) {
        return FALSE;
    }
    $this->db->where('id_proveedor', $id);
    return $this->db->update($this->table, $data);
  }
  private function validate_pet_data($data, $id_proveedor = 0) {
    $this->form_validation->set_data($data); 
    $this->form_validation->set_rules('nombre', 'Nombre', 'required|max_length[100]');
    $this->form_validation->set_rules('nit', 'nit', 'required|max_length[100]');
    $this->form_validation->set_rules('telefono', 'Telefono', 'required|numeric');
    $this->form_validation->set_rules('direccion', 'Direccion', 'required|max_length[100]');
    return $this->form_validation->run();
  }
}