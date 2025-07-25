<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class AccesPerfil extends CI_Model {
  protected $table = 'acceso_perfil'; 
  public function __construct() {
      parent::__construct();
  }
  public function findIdentity($id) {
      return $this->db->get_where($this->table, ['id_acceso_perfil' => $id])->row();
  }
  public function getId($accesPerfil) {
      return $user->id_acceso_perfil ?? null;
  }
  public function findByPerfil($idPerfil) {
    $campos = 'ma.id_menu_acceso, ma.nombre, ma.nivel_superior, ma.icono, ma.tipo,coalesce(ap.estado,0)as acceso,
    (SELECT CONCAT("[", GROUP_CONCAT(\'"\', ab.id_boton, \'"\'), "]") FROM acceso_boton ab WHERE ab.id_acceso = ma.id_menu_acceso AND ab.estado = 1) AS id_botones,
    (SELECT CONCAT("[", GROUP_CONCAT(\'"\', abp.id_boton, \'"\'), "]") FROM acceso_boton_perfil abp WHERE abp.id_acceso = ma.id_menu_acceso AND abp.estado = 1 AND abp.id_perfil = '.$this->db->escape($idPerfil).') AS botones';
    
    $this->db->select($campos); 
    $this->db->from('menu_acceso as ma' );
    $this->db->where('ma.estado','1');
    $this->db->join($this->table . ' as ap', 'ma.id_menu_acceso = ap.id_acceso AND ap.id_perfil = '.$this->db->escape($idPerfil), 'left');
    $this->db->order_by('numero_orden', 'ASC');
    $access = $this->db->get()->result();
    $resAccess =  $this->getSubMenu($access,'0',0);
      return $resAccess;
  }
  public function findOne($idAcces,$idPerfil) {
    $this->db->where('id_acceso', $idAcces);
    $this->db->where('id_perfil', $idPerfil);
    return $this->db->get_where($this->table)->row();
  }
  public function findActive() {
    return $this->db->where('estado', 1)->get($this->table)->result();
  }
  public function update($idAcces,$idPerfil,$estado,$usuarios){
    $state = false;
    if($this->findOne($idAcces,$idPerfil) ){
      $this->db->where('id_acceso', $idAcces);
      $this->db->where('id_perfil', $idPerfil);
      $state = $this->db->update($this->table, ['estado'=>$estado]);
    }else{
      $niewData['id_acceso'] = $idAcces;
      $niewData['id_perfil'] = $idPerfil;
      $niewData['estado'] = $estado;
      $state = $this->db->insert($this->table, $niewData);
    }
    $this->addAccesUser($idAcces,$usuarios,$estado);
    return $state;
  }
  public function addButtonsAccesPerfil($id,$idPerfil,$buttons,$usuarios){
    $stateButton = false;
    $buttonsOlds = $this->findButtonsAccesPerfilActive($id,$idPerfil,$buttons);
    $this->db->where('id_acceso', $id);
    $this->db->where('id_perfil', $idPerfil);
    $stateButton = $this->db->update('acceso_boton_perfil', ['estado'=>0]);
    foreach($buttons as $key=>$button){
      if($this->findButtonAccesPerfil($id,$idPerfil,$button) ){
        $this->db->where('id_acceso', $id);
        $this->db->where('id_boton', $button);
        $this->db->where('id_perfil', $idPerfil);
        $stateButton = $this->db->update('acceso_boton_perfil', ['estado'=>1]);
      }else{
        $niewButton['id_acceso'] = $id;
        $niewButton['id_boton'] = $button;
        $niewButton['id_perfil'] = $idPerfil;
        $niewButton['estado'] = 1;
        $stateButton = $this->db->insert('acceso_boton_perfil', $niewButton);
      }
      $this->addButtonsAccesUser($id,$usuarios,$button,1);
    } 
    foreach($buttonsOlds as $key=>$button){
      $this->addButtonsAccesUser($id,$usuarios,$button->id_boton,0);
    }
    return $stateButton;
  }
  public function findButtonAccesPerfil($idAcces,$idPerfil,$idButton) {
    $this->db->where('id_acceso', $idAcces);
    $this->db->where('id_boton', $idButton);
    $this->db->where('id_perfil', $idPerfil);
    return $this->db->get_where('acceso_boton_perfil')->row();
  }
  public function findButtonsAccesPerfilActive($idAcces,$idPerfil,$buttons) {
    $this->db->where('id_acceso', $idAcces);
    $this->db->where('id_perfil', $idPerfil);
    $this->db->where_not_in('id_boton', $buttons);
    $this->db->where('estado', 1);
    return $this->db->get_where('acceso_boton_perfil')->result();
  }
  public function addButtonsAccesUser($idAcces,$usuarios,$idButton,$estado){
    $stateButton = false;
    foreach($usuarios as $key=>$usuario){
      $idUser = $usuario->id_usuario;
      if($this->findButtonAccesUser($idAcces,$idUser,$idButton) ){
        $this->db->where('id_acceso', $idAcces);
        $this->db->where('id_boton', $idButton);
        $this->db->where('id_usuario', $idUser);
        $stateButton = $this->db->update('acceso_boton_usuario', ['estado'=>$estado]);
      }elseif($estado===1){
        $niewButton['id_acceso'] = $idAcces;
        $niewButton['id_boton'] = $idButton;
        $niewButton['id_usuario'] = $idUser;
        $niewButton['estado'] = $estado;
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
  public function findUsuariosByPefil($idPerfil) {
    $this->db->where('id_perfil', $idPerfil);
    return $this->db->get_where('usuarios')->result();
  }
  public function addAccesUser($idAcces,$usuarios,$estado){
    $stateButton = false;
    foreach($usuarios as $key=>$usuario){
      $idUser = $usuario->id_usuario;
      if($this->findOneAccesUser($idAcces,$idUser) ){
        $this->db->where('id_acceso', $idAcces);
        $this->db->where('id_usuario', $idUser);
        $state = $this->db->update('acceso_usuario', ['estado'=>$estado]);
      }elseif($estado==1){
        $niewData['id_acceso'] = $idAcces;
        $niewData['id_usuario'] = $idUser;
        $niewData['estado'] = $estado;
        $state = $this->db->insert('acceso_usuario', $niewData);
      }
    } 
    return $stateButton;
  }
  public function findOneAccesUser($idAcces,$idUser) {
    $this->db->where('id_acceso', $idAcces);
    $this->db->where('id_usuario', $idUser);
    return $this->db->get_where('acceso_usuario')->row();
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
        array_push($resAccess,$acces);
      }
    }
    return $resAccess;
  }
}