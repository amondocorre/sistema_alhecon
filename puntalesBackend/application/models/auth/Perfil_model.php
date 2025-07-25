<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Perfil_model extends CI_Model {
    protected $table = 'perfiles'; 
    public function __construct() {
        parent::__construct();
        $this->load->library('form_validation'); 
    }
    public function findIdentity($id) {
        return $this->db->get_where($this->table, ['id' => $id])->row();
    }
    public function getId($perfil) {
        return $user->id ?? null;
    }
    public function findAll() {
      return $this->db->get($this->table)->result();
  }
  public function getPerfil(){
    $this->db->select('id,nombre'); 
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
    if (!$this->validate_pefil_data($data)) {
        return FALSE; 
    }
    $data['estado'] = '1';
    return $this->db->insert($this->table, $data);
    //return $this->db->insert_id();
  }
  public function delete($id) {
    $this->db->where('id', $id);
    return $this->db->update($this->table, ['estado'=>0]);
  }
  public function activate($id) {
    $this->db->where('id', $id);
    return $this->db->update($this->table, ['estado'=>'1']);
  }
  public function update($id, $data) {
    if (!$this->validate_pefil_data($data, $id)) {
        return FALSE;
    }
    $this->db->where('id', $id);
    return $this->db->update($this->table, $data);
  }
  private function validate_pefil_data($data, $id_perfil = 0) {
    $this->form_validation->set_data($data);
    $this->form_validation->set_rules('nombre', 'Nombre', 'required|max_length[100]|perfil_unique_current['.$id_perfil.']');
    $this->form_validation->set_rules('id', 'id', 'max_length[15] |is_unique[perfiles.id]');
    return $this->form_validation->run();
  }
}