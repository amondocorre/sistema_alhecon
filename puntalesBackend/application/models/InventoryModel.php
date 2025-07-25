<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class InventoryModel extends CI_Model {
  protected $table = 'inventario'; 
  public function __construct() {
      parent::__construct();
  }
  public function findIdentity($id) {
      return $this->db->get_where($this->table, ['id_inventario' => $id])->row();
  }
  public function getId($inventory) {
      return $inventory->id_inventario ?? null;
  }
  public function create($data) {
    $data['id_estado'] = '1';
    $data['fecha_registro'] = date('Y-m-d H:i:s');
    return $this->db->insert($this->table, $data);
  }
  public function delete($id) {
    $this->db->where('id', $id);
    return $this->db->update($this->table, ['id_estado'=>0]);
  }
  public function activate($id) {
    $this->db->where('id', $id);
    return $this->db->update($this->table, ['id_estado'=>'1']);
  }
  public function getStock($id_sucursal){
    $this->db->select('id_producto, COUNT(id_inventario) as stock');
    $this->db->from('inventario');
    $this->db->where('id_estado', 1);
    $this->db->where('id_sucursal', $id_sucursal);
    $this->db->group_by('id_producto');
    $query = $this->db->get();
    if ($query->num_rows() > 0) {
        $productos = $query->result(); 
        $resProductos = [];
        foreach($productos as $producto){
          $resProductos[$producto->id_producto] = (int)$producto->stock??0;
        }
        return $resProductos;
    } else {
        return array(); 
    }
  }
}
