<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ButtonModel extends CI_Model {
  protected $table = 'botones'; 
  public function __construct() {
      parent::__construct();
  }
  public function findIdentity($id) {
      return $this->db->get_where($this->table, ['id_boton' => $id])->row();
  }
  public function getId($user) {
      return $user->id_menu_acceso ?? null;
  }
  public function findAll() {
    return $this->db->get($this->table)->result();
  }
  public function findActive() {
    return $this->db->where('estado', 1)->get($this->table)->result();
  }
}