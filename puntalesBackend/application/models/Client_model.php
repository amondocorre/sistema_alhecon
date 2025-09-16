<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Client_model extends CI_Model {
    protected $table = 'cliente'; 
    public function __construct() {
        parent::__construct();
        $this->load->library('form_validation'); 
    }
    public function findIdentity($id) {
        return $this->db->get_where($this->table, ['id_cliente' => $id])->row();
    }
    public function getId($cliente) {
        return $user->id_cliente ?? null;
    }
    public function findAll() {
      $url = getHttpHost();
      $this->db->select("id_cliente,c.id_status,s.descripcion as status,s.color as status_color,es_empresa,nombres,ap_paterno,ap_materno,ci, correo,telefono,fecha_nacimiento,direccion_gps, direccion, profesion, concat('$url',foto_ciA)as foto_ciA,concat('$url',foto_ciB)as foto_ciB,CONCAT(nombres, ' ', ap_paterno, ' ', ap_materno) AS nombre_completo"); 
      $this->db->from($this->table .' as c'); 
      $this->db->join('status as s','s.id_status = c.id_status','inner');
      //$this->db->where('id_status', 1); 
      $this->db->order_by('nombre_completo', 'ASC');
      $query = $this->db->get();
      if ($query->num_rows() > 0) {
        $clientes = $query->result();
        foreach($clientes as $cliente){
          $cliente->empresas = $this->getCompanyByClient($cliente->id_cliente);
        }
          return $clientes; 
      } else {
          return array(); 
      }
    }
    public function getCompanyByClient($id_client) {
      $url = getHttpHost();
      $this->db->select("e.*"); 
      $this->db->from('empresa as e'); 
      $this->db->join('empresa_cliente as ec','ec.id_empresa = e.id_empresa','inner');
      $this->db->where('ec.estado', 1); 
      $this->db->where('ec.id_cliente', $id_client); 
      $this->db->order_by('nombre_empresa', 'ASC');
      $query = $this->db->get();
      if ($query->num_rows() > 0) {
          return $query->result(); 
      } else {
          return array(); 
      }
    }
    public function findActive(){
      $url = getHttpHost();
      $this->db->select("id_cliente,id_status,es_empresa,nombres,ap_paterno,ap_materno,ci, correo,telefono,fecha_nacimiento,direccion_gps, direccion, profesion, concat('$url',foto_ciA)as foto_ciA,concat('$url',foto_ciB)as foto_ciB,CONCAT(nombres, ' ', ap_paterno, ' ', ap_materno) AS nombre_completo"); 
      $this->db->from($this->table); 
      //$this->db->where('id_status', 1); 
      $this->db->order_by('nombre_completo', 'ASC');
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
      $data['ap_materno'] = isset($data['ap_materno'])?$data['ap_materno']:'';
      $data['id_status'] = '1';
      $this->db->insert($this->table, $data);
      return $this->db->insert_id();
    }
  public function update($id, $data) {
    if (!$this->validate_pefil_data($data, $id)) {
        return FALSE;
    }
    $data['ap_materno'] = isset($data['ap_materno'])?$data['ap_materno']:'';
    $this->db->where('id_cliente', $id);
    return $this->db->update($this->table, $data);
  }
  public function delete($id) {
    $this->db->where('id_cliente', $id);
    return $this->db->update($this->table, ['id_status'=>'0']);
  }
  public function activate($id) {
    $this->db->where('id_cliente', $id);
    return $this->db->update($this->table, ['id_status'=>'1']);
  }
  public function updateFotoCi($url,$id,$campo){
    $this->db->where('id_cliente', $id);
    return $this->db->update($this->table, [$campo=>$url]);
  }
  public function addCompanies($id,$companies){
    $stateEmpresa = false;
    $this->db->where('id_cliente', $id);
    $stateEmpresa = $this->db->update('empresa_cliente', ['estado'=>0]);
    if (is_array($companies) && !empty($companies)){
      foreach($companies as $empresa){
        $id_empresa = $empresa->id_empresa;
        if($this->findCompanyClient($id,$id_empresa) ){
          $this->db->where('id_cliente', $id);
          $this->db->where('id_empresa', $id_empresa);
          $stateEmpresa = $this->db->update('empresa_cliente', ['estado'=>1]);
        }else{
          $newEmpresa['id_cliente'] = $id;
          $newEmpresa['id_empresa'] = $id_empresa;
          $newEmpresa['estado'] = 1;
          $stateEmpresa = $this->db->insert('empresa_cliente', $newEmpresa);
        }
      } 
    }
    return $stateEmpresa;
  }
  public function findCompanyClient($id_cliente,$id_empresa) {
    $this->db->where('id_cliente', $id_cliente);
    $this->db->where('id_empresa', $id_empresa);
    return $this->db->get_where('empresa_cliente')->row();
  }
  private function validate_pefil_data($data, $id_client = 0) {
    $this->form_validation->set_data($data);
    $this->form_validation->set_rules('nombres', 'Nombre', 'required|max_length[100]');
    $this->form_validation->set_rules('ap_paterno', 'Apellido paterno', 'required|max_length[15]');
    $this->form_validation->set_rules('ci', 'ci', 'required');
    $this->form_validation->set_rules('telefono', 'Teléfono', 'required');
    $this->form_validation->set_rules('direccion', 'Direccion', 'required');
    return $this->form_validation->run();
  }
}