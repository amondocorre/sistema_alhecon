<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class TransportModel extends CI_Model {
  protected $table = 'transporte_externo'; 
  public function __construct() {
      parent::__construct();
      $this->load->library('form_validation'); 
  }
  public function findIdentity($id) {
      return $this->db->get_where($this->table, ['id_transporte' => $id])->row();
  }
  public function getId($cliente) {
      return $user->id_transporte ?? null;
  }
  public function findAll() {
    $url = getHttpHost();
    $this->db->select("id_transporte, nombre, ci, telefono, capacidad_carga, placa, descripcion,estado, concat('$url',fotografia_movilidad)as foto"); 
    $this->db->from($this->table .' as t'); 
    $this->db->order_by('nombre', 'ASC');
    $query = $this->db->get();
    if ($query->num_rows() > 0) {
      return $query->result();
    } else {
        return array(); 
    }
  }
  public function findActive(){
    $url = getHttpHost();
    $this->db->select("id_transporte, nombre, ci, telefono, capacidad_carga, placa, descripcion, concat('$url',fotografia_movilidad)as foto"); 
    $this->db->from($this->table .' as t'); 
    $this->db->where('t.estado','1');
    $this->db->order_by('nombre', 'ASC');
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
    $this->db->insert($this->table, $data);
    return $this->db->insert_id();
  }
  public function update($id, $data) {
    if (!$this->validate_pefil_data($data, $id)) {
        return FALSE;
    }
    $this->db->where('id_transporte', $id);
    return $this->db->update($this->table, $data);
  }
  public function delete($id) {
    $this->db->where('id_transporte', $id);
    return $this->db->update($this->table, ['estado'=>'0']);
  }
  public function activate($id) {
    $this->db->where('id_transporte', $id);
    return $this->db->update($this->table, ['estado'=>'1']);
  }
  public function updateFoto($url,$id){
    $this->db->where('id_transporte', $id);
    return $this->db->update($this->table, ['fotografia_movilidad'=>$url]);
  }
  private function validate_pefil_data($data, $id_client = 0) {
    $this->form_validation->set_data($data);
    $this->form_validation->set_rules('nombre', 'Nombre', 'required|max_length[100]');
    $this->form_validation->set_rules('ci', 'ci', 'required');
    $this->form_validation->set_rules('telefono', 'Teléfono', 'required');
    $this->form_validation->set_rules('placa', 'Numero placa', 'required|max_length[15]');
    return $this->form_validation->run();
  }
}