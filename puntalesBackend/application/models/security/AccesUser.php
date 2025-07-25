<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class AccesUser extends CI_Model {
  protected $table = 'acceso_usuario'; 
  public function __construct() {
      parent::__construct();
  }
  public function findIdentity($id) {
      return $this->db->get_where($this->table, ['id_acceso_usuario' => $id])->row();
  }
  public function getId($acces_user) {
      return $user->id_acceso_usuario ?? null;
  }
  public function findByUser($idUser) {
    $campos = 'ma.id_menu_acceso, ma.nombre,ma.numero_orden, ma.nivel_superior, ma.icono, ma.tipo,coalesce(au.estado,0)as acceso,
    (SELECT CONCAT("[", GROUP_CONCAT(\'"\', ab.id_boton, \'"\'), "]") FROM acceso_boton ab WHERE ab.id_acceso = ma.id_menu_acceso AND ab.estado = 1) AS id_botones,
    (SELECT CONCAT("[", GROUP_CONCAT(\'"\', abu.id_boton, \'"\'), "]") FROM acceso_boton_usuario abu WHERE abu.id_acceso = ma.id_menu_acceso AND abu.estado = 1 AND abu.id_usuario = '.$this->db->escape($idUser).') AS botones';
    
    $this->db->select($campos); 
    $this->db->from('menu_acceso as ma' );
    $this->db->where('ma.estado','1');
    $this->db->join($this->table . ' as au', 'ma.id_menu_acceso = au.id_acceso AND au.id_usuario = '.$this->db->escape($idUser), 'left');
    $this->db->order_by('numero_orden', 'ASC');
    $access = $this->db->get()->result();
    $resAccess =  $this->getSubMenu($access,'0',0);
      return $resAccess;
  }
  public function findOne($idAcces,$idUser) {
    $this->db->where('id_acceso', $idAcces);
    $this->db->where('id_usuario', $idUser);
    return $this->db->get_where($this->table)->row();
  }
  public function findActive() {
    return $this->db->where('estado', 1)->get($this->table)->result();
  }
  public function update($idAcces,$idUser,$estado){
    $state = false;
    if($this->findOne($idAcces,$idUser) ){
      $this->db->where('id_acceso', $idAcces);
      $this->db->where('id_usuario', $idUser);
      $state = $this->db->update($this->table, ['estado'=>$estado]);
    }else{
      $niewData['id_acceso'] = $idAcces;
      $niewData['id_usuario'] = $idUser;
      $niewData['estado'] = $estado;
      $state = $this->db->insert($this->table, $niewData);
    }
    return $state;
  }
  public function addButtonsAccesUser($id,$idUser,$buttons){
    $stateButton = false;
    $this->db->where('id_acceso', $id);
    $this->db->where('id_usuario', $idUser);
    $stateButton = $this->db->update('acceso_boton_usuario', ['estado'=>0]);
    foreach($buttons as $key=>$button){
      if($this->findButtonAccesUser($id,$idUser,$button) ){
        $this->db->where('id_acceso', $id);
        $this->db->where('id_boton', $button);
        $this->db->where('id_usuario', $idUser);
        $stateButton = $this->db->update('acceso_boton_usuario', ['estado'=>1]);
      }else{
        $niewButton['id_acceso'] = $id;
        $niewButton['id_boton'] = $button;
        $niewButton['id_usuario'] = $idUser;
        $niewButton['estado'] = 1;
        $stateButton = $this->db->insert('acceso_boton_usuario', $niewButton);
      }
    } 
    return $stateButton;
  }
  public function findButtonAccesUser($idAcces,$idUsern,$idButton) {
    $this->db->where('id_acceso', $idAcces);
    $this->db->where('id_boton', $idButton);
    $this->db->where('id_usuario', $idUsern);
    return $this->db->get_where('acceso_boton_usuario')->row();
  }
  function getSubMenu($access,$idSubMenu,$nivel){
    $resAccess = array();
    foreach ($access as $key => $acces) {
      if($acces->nivel_superior === $idSubMenu){
        $acces->nivel = $nivel;
        if(property_exists($acces, 'id_botones')){
          $acces->id_botones = $acces->id_botones?json_decode($acces->id_botones):[];
        }
        if(property_exists($acces, 'botones')){
          $acces->botones = $acces->botones?json_decode($acces->botones):[];
        }
        $acces->subMenu = $this->getSubMenu($access,$acces->id_menu_acceso,$nivel+1);
        //$acces->subMenu = usort($acces->subMenu, function($a, $b){return strcmp($a->numero_orden, $b->numero_orden);});
        array_push($resAccess,$acces);
      }
    }
    return $resAccess;
  }
}