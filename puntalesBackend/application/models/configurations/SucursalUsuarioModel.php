<?php
defined('BASEPATH') OR exit('No direct script usuarios allowed');

class SucursalUsuarioModel extends CI_Model {
    protected $table = 'sucursal_usuario'; 
    public function __construct() {
        parent::__construct();
    }
  public function findIdentity($id) {
      return $this->db->get_where($this->table, ['id_sucursal_usuario' => $id])->row_usuario();
  }
  public function getId($sucursal) {
      return $sucursal->id_sucursal_usuario ?? null;
  }
  public function findAll() {
    $this->db->select("u.id_usuario, u.id_perfil, u.nombre, u.estado, p.nombre AS perfil_nombre,
    (SELECT CONCAT('[', GROUP_CONCAT('\"', su.id_sucursal, '\"'), ']')
     FROM sucursal_usuario su
     WHERE su.id_usuario = u.id_usuario AND su.estado = 1) AS sucursales,
     (SELECT CONCAT('[', GROUP_CONCAT('\"', s.nombre, '\"'), ']')
     FROM sucursal_usuario su
     inner join sucursal s on s.id_sucursal = su.id_sucursal
     WHERE su.id_usuario = u.id_usuario AND su.estado = 1) AS nombreSucursales");
    $this->db->from('usuarios AS u');
    $this->db->join('perfiles AS p', 'p.id = u.id_perfil', 'inner');
    $this->db->group_by(['u.id_usuario', 'u.id_perfil', 'u.nombre', 'u.estado', 'p.nombre']);
    $query = $this->db->get();
    if ($query->num_rows() > 0) {
        $usuarios = $query->result();
        foreach($usuarios as $usuario){
          $usuario->sucursales = $usuario->sucursales?json_decode($usuario->sucursales):[];
          $usuario->nombreSucursales = $usuario->nombreSucursales?json_decode($usuario->nombreSucursales):[];
        }
        return $usuarios;
    } else {
        return array(); 
    }
  }
  public function getSucursalesUser($idUsuario){
    $this->db->select("s.*");
    $this->db->from($this->table . ' AS su'); 
    $this->db->join('sucursal as s','s.id_sucursal=su.id_sucursal','inner');
    $this->db->where('su.estado', 1);
    $this->db->where('s.estado', 1);
    $query = $this->db->get();
    if ($query->num_rows() > 0) {
        return $query->result(); 
    } else {
        return array(); 
    }
  }
  public function addSucursales($sucursales,$idUsuario) {
    $stateSucursal = false;
    $this->db->where('id_usuario', $idUsuario);
    $stateSucursal = $this->db->update('sucursal_usuario', ['estado'=>0]);
    foreach($sucursales as $key=>$sucursal){
      if($this->findSucursalUsuario($idUsuario,$sucursal) ){
        $this->db->where('id_usuario', $idUsuario);
        $this->db->where('id_sucursal', $sucursal);
        $stateSucursal = $this->db->update('sucursal_usuario', ['estado'=>1]);
      }else{
        $newSucursal['id_usuario'] = $idUsuario;
        $newSucursal['id_sucursal'] = $sucursal;
        $newSucursal['estado'] = 1;
        $stateSucursal = $this->db->insert('sucursal_usuario', $newSucursal);
      }
    } 
    return $stateSucursal;
  }
  public function findSucursalUsuario($idUsuario,$sucursal) {
    $this->db->where('id_usuario', $idUsuario);
    $this->db->where('id_sucursal', $sucursal);
    return $this->db->get_where('sucursal_usuario')->row();
  }
}