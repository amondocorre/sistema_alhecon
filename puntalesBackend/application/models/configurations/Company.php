<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Company extends CI_Model {
    protected $table = 'empresa_sis'; 
    public function __construct() {
        parent::__construct();
    }
  public function findIdentity($id) {
      return $this->db->get_where($this->table, ['id_empresa_sis' => $id])->row();
  }
  public function getId($company) {
      return $mascota->id_empresa_sis ?? null;
  }
  public function getDataId($id) {
      $url = getHttpHost();
      $this->db->select("id_empresa_sis,nombre,direccion,celular,correo,CONCAT('$url', logo_empresa) as logo_empresa,CONCAT('$url', logo_impresion) as logo_impresion");
      $this->db->where('id_empresa_sis', $id);
      $result = $this->db->get($this->table)->row();
      return $result;
  }
  public function findAll() {
    $url = getHttpHost();
    $this->db->select("id_empresa_sis,nombre,nit,direccion,telefono,celular,correo,pagina_web,ubicacion_gps,pie_documento,created_at,updated_at,CONCAT('$url', logo_empresa) as logo_empresa,CONCAT('$url', logo_impresion) as logo_impresion");
    $this->db->from($this->table); 
     $query = $this->db->get(); // Ejecuta la consulta construida
    if ($query->num_rows() > 0) {
        return $query->result();
    } else {
        return array(); 
    }
  }
  public function findActive(){
    $url = getHttpHost();
    $this->db->select("id_empresa_sis,nombre,nit,direccion,telefono,celular,correo,pagina_web,ubicacion_gps,pie_documento,created_at,updated_at,CONCAT('$url', logo_empresa) as logo_empresa,CONCAT('$url', logo_impresion) as logo_impresion");
    $this->db->from($this->table); 
    //$this->db->where('t.estado', 1);
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
    $data['created_at'] = date('Y-md H:i:s');
    return $this->db->insert($this->table, $data);
  }
  public function delete($id) {
    $this->db->where('id_empresa_sis', $id);
    return $this->db->update($this->table, ['estado'=>0]);
  }
  public function activate($id) {
    $this->db->where('id_empresa_sis', $id);
    return $this->db->update($this->table, ['estado'=>'1']);
  }
  public function update($id, $data) {
    if (!$this->validate_pet_data($data, $id)) {
        return FALSE;
    }
    $data['updated_at'] = date('Y-md H:i:s');
    $this->db->where('id_empresa_sis', $id);
    return $this->db->update($this->table, $data);
  }
  public function updateLogo($url,$id,$campo){
    $this->db->where('id_empresa_sis', $id);
    return $this->db->update($this->table, [$campo=>$url]);
  }
  private function validate_pet_data($data, $id_empresa_sis = 0) {
    $this->form_validation->set_data($data);
    $this->form_validation->set_rules('nombre', 'Nombre', 'required|max_length[100]');
    $this->form_validation->set_rules('nit', 'Nit', 'max_length[15]'.($id_empresa_sis?'':' | is_unique[empresa.nit]'));
    //$this->form_validation->set_rules('correo', 'Correo', 'required|valid_email|max_length[100]');

    return $this->form_validation->run();
  }
}
