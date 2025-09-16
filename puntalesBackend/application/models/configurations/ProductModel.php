<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ProductModel extends CI_Model {
    protected $table = 'producto'; 
    public function __construct() {
        parent::__construct();
    }
  public function findIdentity($id) {
      return $this->db->get_where($this->table, ['id_producto' => $id])->row();
  }
  public function getId($producto) {
      return $producto->id_producto ?? null;
  }
  public function findAll() {
    $url = getHttpHost();
    $this->db->select("id_producto, nombre, descripcion, precio_hora, precio_dia, precio_30dias, precio_reposicion, estado, concat('$url',fotografia) as fotografia, fecha_creacion, fecha_update, id_usuario_crea, id_usuario_modifica,uso_dias,visible");
    $this->db->from($this->table); 
    $this->db->where('es_combo','0');
    $query = $this->db->get();
    if ($query->num_rows() > 0) {
        return $query->result();
    } else {
        return array();
    }
  }
  public function findActive(){
    $url = getHttpHost();
    $this->db->select("id_producto, nombre, descripcion, precio_hora, precio_dia, precio_30dias, precio_reposicion, estado, concat('$url',fotografia) as fotografia,es_combo,uso_dias");
    $this->db->from($this->table); 
    $this->db->where('estado', 'activo');
    $this->db->where('es_combo','0');
    $query = $this->db->get();
    if ($query->num_rows() > 0) {
        return $query->result(); 
    } else {
        return array(); 
    }
  }
  public function findVisible(){
    $url = getHttpHost();
    $this->db->select("id_producto, nombre, descripcion, precio_hora, precio_dia, precio_30dias, precio_reposicion, estado, concat('$url',fotografia) as fotografia,es_combo,uso_dias");
    $this->db->from($this->table); 
    $this->db->where('estado', 'activo');
    $this->db->where('visible', 'si');
    $this->db->where('es_combo','0');
    $query = $this->db->get();
    if ($query->num_rows() > 0) {
        return $query->result(); 
    } else {
        return array(); 
    }
  }
  public function create($data,$idUsuario) {
    if (!$this->validate_pet_data($data)) {
        return FALSE; 
    }
    $data['estado'] = 'activo';
    $data['es_combo']='0';
    $data['id_usuario_crea'] = $idUsuario;
    $data['fecha_creacion'] = date('Y-m-d H:i:s');
    $this->db->insert($this->table, $data);
    return $this->db->insert_id();
  }
  public function delete($id) {
    $this->db->where('id_producto', $id);
    return $this->db->update($this->table, ['estado'=>'inactivo']);
  }
  public function activate($id) {
    $this->db->where('id_producto', $id);
    return $this->db->update($this->table, ['estado'=>'activo']);
  }
  public function update($id, $data,$idUsuario) {
    if (!$this->validate_pet_data($data, $id)) {
        return FALSE;
    }
    unset($data['fotografia']);
    $data['es_combo']='0';
    $data['id_usuario_modifica'] = $idUsuario;
    $data['fecha_update'] = date('Y-m-d H:i:s');
    $this->db->where('id_producto', $id);
    return $this->db->update($this->table, $data);
  }
  public function updateFoto($url,$id){
    $this->db->where('id_producto', $id);
    return $this->db->update($this->table, ['fotografia'=>$url]);
  }
  private function validate_pet_data($data, $id_producto = 0) {
    $this->form_validation->set_data($data);
    $this->form_validation->set_rules('nombre', 'Nombre', 'required|max_length[100]');
    $this->form_validation->set_rules('precio_hora', 'precio hora', 'numeric');//'required|regex_match[/^\d+(\.\d{1,2})?$/]|greater_than[0]'
    $this->form_validation->set_rules('precio_dia', 'precio dia', 'numeric');
    $this->form_validation->set_rules('precio_30dias', 'precio 30 dias', 'numeric');
    $this->form_validation->set_rules('precio_reposicion', 'precio reposicion', 'numeric');
    return $this->form_validation->run();
  }
}
