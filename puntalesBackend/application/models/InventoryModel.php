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
   public function getTotalStock($id_sucursal){
    $this->db->select('id_producto, COUNT(id_inventario) as stock');
    $this->db->from('inventario');
    //$this->db->where('id_estado<>', 1);
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
  public function getProductos(){
    $url = getHttpHost();
    $this->db->select("c.id_producto,c.nombre,c.descripcion,c.precio_hora,c.precio_dia,c.precio_30dias,c.estado,concat('$url',c.fotografia) as fotografia,c.es_combo,c.uso_dias,
            IF(c.es_combo=1,
                  JSON_ARRAYAGG(
                      JSON_OBJECT(
                          'id_producto', p.id_producto,
                          'nombre', p.nombre,
                          'fotografia', concat('$url',p.fotografia),
                          'cantidad', cp.cantidad
                      )
                  ), 
                  '[]'
              ) AS productos");
    $this->db->from('producto AS c'); 
    $this->db->join('combo_producto as cp','cp.id_combo = c.id_producto and cp.estado =1','left');
    $this->db->join('producto as p ',' p.id_producto = cp.id_producto','left');
    $this->db->where('c.estado', 'activo');
    $this->db->group_by('c.id_producto,c.nombre,c.precio_hora,c.precio_dia,c.precio_30dias,c.estado,c.fotografia,c.uso_dias');
    $this->db->order_by('c.nombre');
    $query = $this->db->get();
    if ($query->num_rows() > 0) {
      $combos =  $query->result(); 
      foreach($combos as $combo){
        $combo->productos = $combo->productos?json_decode($combo->productos,false):[];
      }
      return $combos;
    } else {
        return array(); 
    }
  }
  function getStockProducto($producto,$inventorio){
    if (!empty($producto->es_combo) && $producto->es_combo == '1' && !empty($producto->productos)) {
        $minStock = INF;
        foreach ($producto->productos as $i => $p) {
          $id = $p->id_producto;            
          $available = isset($inventorio[$id])?$inventorio[$id]:0;
          $required = $p->cantidad?? 1;
          $p->stock = $available;
          $restante = floor(($available) / $required);
          $minStock = (int)min($minStock, $restante);
        }
        return ($minStock === INF) ? 0 : $minStock;
    }
    $id = $producto->id_producto;
    return (int)isset($inventorio[$id])?$inventorio[$id]:0;
  }
  public function getInventario($id_sucursal){
    $inventorio = $this->getStock($id_sucursal);
    $productos = $this->getProductos();
    foreach($productos as $key=>$producto){
      $producto->stock = $this->getStockProducto($producto,$inventorio);
    }
    usort($productos, function($a, $b) {return  $a->stock<=>$b->stock;});
    return $productos;
  }
}
