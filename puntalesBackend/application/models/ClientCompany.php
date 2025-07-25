<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ClientCompany extends CI_Model {
    protected $table = 'empresa'; 
    public function __construct() {
        parent::__construct();
    }
  public function findIdentity($id) {
      return $this->db->get_where($this->table, ['id_empresa' => $id])->row();
  }
  public function getId($company) {
      return $company->id_empresa ?? null;
  }
  public function getDataId($id) {
      $this->db->select("*");
      $this->db->where('id_empresa', $id);
      $result = $this->db->get($this->table)->row();
      return $result;
  }
  public function findAll() {
    $url = getHttpHost();
    $this->db->select("*");
    $this->db->from($this->table); 
     $query = $this->db->get(); 
    if ($query->num_rows() > 0) {
        return $query->result();
    } else {
        return array(); 
    }
  }
  public function findActive(){
    $url = getHttpHost();
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
    if (!$this->validate_pet_data($data)) {
        return FALSE; 
    }
    $data['estado'] = 1;
    //echo json_encode($data);
    return $this->db->insert($this->table, $data);
  }
  public function delete($id) {
    $this->db->where('id_empresa', $id);
    return $this->db->update($this->table, ['estado'=>0]);
  }
  public function activate($id) {
    $this->db->where('id_empresa', $id);
    return $this->db->update($this->table, ['estado'=>'1']);
  }
  public function update($id, $data) {
    if (!$this->validate_pet_data($data, $id)) {
        return FALSE;
    }
    $this->db->where('id_empresa', $id);
    return $this->db->update($this->table, $data);
  }
  private function validate_pet_data($data, $id_empresa = 0) {
    $this->form_validation->set_data($data);
    $this->form_validation->set_rules('nombre_empresa', 'nombre empresa', 'required|max_length[100]');
    $this->form_validation->set_rules('nit', 'Nit', $id_empresa ? 'max_length[15]' : 'max_length[15]|is_unique[empresa.nit]');
    $this->form_validation->set_rules('direccion', 'Dirección', 'required|max_length[100]');
    $this->form_validation->set_rules('telefono', 'telefono', 'required');

    return $this->form_validation->run();
  }
}
