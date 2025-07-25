<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class AccessMenuModel extends CI_Model {
  protected $table = 'menu_acceso'; 
  public function __construct() {
    parent::__construct();
  }
  public function findIdentity($id) {
      return $this->db->get_where($this->table, ['id_menu_acceso' => $id])->row();
  }
  public function getId($user) {
      return $user->id_menu_acceso ?? null;
  }
  public function findAll() {
    $this->db->select('ma.*, (SELECT CONCAT("[", GROUP_CONCAT(\'"\', ab.id_boton, \'"\'), "]") FROM acceso_boton ab WHERE ab.id_acceso = ma.id_menu_acceso AND ab.estado = 1) AS id_botones');
    $this->db->from('menu_acceso ma');
    //$this->db->where('ma.estado', 1);
    $this->db->order_by('numero_orden', 'ASC');
    $query = $this->db->get();
    $access = $query->result();
      $resAccess =  $this->getSubMenu($access,'0',0);
      return $resAccess;
  }
  public function findAllIdUser($idUser) {
    $this->db->select('ma.*'); 
    $this->db->from($this->table.' as ma' );
    $this->db->where('ma.estado','1');
    $this->db->join('acceso_usuario as au', 'ma.id_menu_acceso = au.id_acceso AND au.id_usuario = '.$this->db->escape($idUser), 'inner');
    $this->db->where('au.estado','1');
    $this->db->order_by('numero_orden', 'ASC');
    $access = $this->db->get()->result();
    $resAccess =  $this->getSubMenu($access,'0',0);
    return $resAccess;
  }
  function getSubMenu($access,$idSubMenu,$nivel){
    $resAccess = array();
    foreach ($access as $key => $acces) {
      if($acces->nivel_superior === $idSubMenu){
        $acces->nivel = $nivel;
        if(property_exists($acces, 'id_botones')){
          $acces->id_botones = $acces->id_botones?json_decode($acces->id_botones):[];
        }
        $acces->subMenu = $this->getSubMenu($access,$acces->id_menu_acceso,$nivel+1);
        array_push($resAccess,$acces);
      }
    }
    return $resAccess;
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
    $this->db->where('id_menu_acceso', $id);
    return $this->db->update($this->table, $data);
  }
  public function delete($id) {
    $this->db->where('id_menu_acceso', $id);
    return $this->db->update($this->table, ['estado'=>'0']);
  }
  public function activate($id) {
    $this->db->where('id_menu_acceso', $id);
    return $this->db->update($this->table, ['estado'=>'1']);
  }
  private function validate_pefil_data($data, $id = 0) {
    $this->form_validation->set_data($data);
    $this->form_validation->set_rules('nombre', 'Nombre', 'required|max_length[100]');
    $this->form_validation->set_rules('link', 'Link', 'required|max_length[100]');//.($id_client>0 ? '|email_unique_client['.$id_client.']' : '|is_unique[cliente.email]'));
    return $this->form_validation->run();
  }
  public function addButtons($id,$buttons){
    $stateButton = false;
    $this->db->where('id_acceso', $id);
    $stateButton = $this->db->update('acceso_boton', ['estado'=>0]);
    foreach($buttons as $key=>$button){
      if($this->findButtonAcces($id,$button) ){
        $this->db->where('id_acceso', $id);
        $this->db->where('id_boton', $button);
        $stateButton = $this->db->update('acceso_boton', ['estado'=>1]);
      }else{
        $niewButton['id_acceso'] = $id;
        $niewButton['id_boton'] = $button;
        $niewButton['estado'] = 1;
        $stateButton = $this->db->insert('acceso_boton', $niewButton);
      }
    } 
    return $stateButton;
  }
  public function findButtonAcces($id_acces,$id_boton) {
    $this->db->where('id_acceso', $id_acces);
    $this->db->where('id_boton', $id_boton);
    return $this->db->get_where('acceso_boton')->row();
  }
}