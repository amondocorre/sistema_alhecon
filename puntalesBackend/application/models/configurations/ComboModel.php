<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ComboModel extends CI_Model {
    protected $table = 'producto'; 
    public function __construct() {
        parent::__construct();
    }
  public function findIdentity($id) {
      return $this->db->get_where($this->table, ['id_producto' => $id])->row();
  }
  public function getId($mascota) {
      return $mascota->id_producto ?? null;
  }
  public function findAll() {
    $url = getHttpHost();
    $this->db->select("c.id_producto,c.nombre,c.precio_hora,c.precio_dia,c.precio_30dias,c.estado,concat('$url',c.fotografia) as fotografia,c.uso_dias,c.visible,
            COALESCE(
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
    $this->db->from($this->table . ' AS c'); 
    $this->db->join('combo_producto as cp','cp.id_combo = c.id_producto and cp.estado =1','inner');
    $this->db->join('producto as p ',' p.id_producto = cp.id_producto','inner');
    $this->db->where('c.es_combo','1');
    $this->db->group_by('c.id_producto,c.nombre,c.precio_hora,c.precio_dia,c.precio_30dias,c.estado,c.fotografia,c.uso_dias');
    $this->db->order_by('c.nombre');
    $query = $this->db->get();
    if ($query->num_rows() > 0) {
      $combos =  $query->result(); 
      foreach($combos as $combo){
        $combo->productos = $combo->productos?json_decode($combo->productos,true):[];
      }
      return $combos;
    } else {
        return array(); 
    }
  }
  public function findActive(){
    $url = getHttpHost();
    $this->db->select("c.id_producto,c.nombre,c.precio_hora,c.precio_dia,c.precio_30dias,c.estado,concat('$url',c.fotografia) as fotografia,c.es_combo,c.uso_dias,
            COALESCE(
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
    $this->db->from($this->table . ' AS c'); 
    $this->db->join('combo_producto as cp','cp.id_combo = c.id_producto and cp.estado =1','inner');
    $this->db->join('producto as p ',' p.id_producto = cp.id_producto','inner');
    $this->db->where('c.es_combo','1');
    $this->db->where('c.estado', 'activo');
    $this->db->group_by('c.id_producto,c.nombre,c.precio_hora,c.precio_dia,c.precio_30dias,c.estado,c.fotografia,c.uso_dias');
    $this->db->order_by('c.nombre');
    $query = $this->db->get();
    if ($query->num_rows() > 0) {
      $combos =  $query->result(); 
      foreach($combos as $combo){
        $combo->productos = $combo->productos?json_decode($combo->productos,true):[];
      }
      return $combos;
    } else {
        return array(); 
    }
  }
  public function findVisible(){
    $url = getHttpHost();
    $this->db->select("c.id_producto,c.nombre,c.precio_hora,c.precio_dia,c.precio_30dias,c.estado,concat('$url',c.fotografia) as fotografia,c.es_combo,c.uso_dias,
            COALESCE(
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
    $this->db->from($this->table . ' AS c'); 
    $this->db->join('combo_producto as cp','cp.id_combo = c.id_producto and cp.estado =1','inner');
    $this->db->join('producto as p ',' p.id_producto = cp.id_producto','inner');
    $this->db->where('c.es_combo','1');
    $this->db->where('c.estado', 'activo');
    $this->db->where('c.visible', 'si');
    $this->db->group_by('c.id_producto,c.nombre,c.precio_hora,c.precio_dia,c.precio_30dias,c.estado,c.fotografia,c.uso_dias');
    $this->db->order_by('c.nombre');
    $query = $this->db->get();
    if ($query->num_rows() > 0) {
      $combos =  $query->result(); 
      foreach($combos as $combo){
        $combo->productos = $combo->productos?json_decode($combo->productos,true):[];
      }
      return $combos;
    } else {
        return array(); 
    }
  }
  public function create($data,$idUsuario) {
    if (!$this->validate_pet_data($data)) {
        return FALSE; 
    }
    $data['estado'] = 'activo';
    $data['es_combo'] = '1';
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
    $data['es_combo'] = '1';
    $this->db->where('id_producto', $id);
    $data['id_usuario_modifica'] = $idUsuario;
    $data['fecha_update'] = date('Y-m-d H:i:s');
    return $this->db->update($this->table, $data);
  }
   public function updateFoto($url,$id){
    $this->db->where('id_producto', $id);
    return $this->db->update($this->table, ['fotografia'=>$url]);
  }
  public function addProducts($id_combo,$productos){
    $stateProducto = false;
    $this->db->where('id_combo', $id_combo);
    $stateProducto = $this->db->update('combo_producto', ['estado'=>0]);
    if (is_array($productos) && !empty($productos)){
      foreach($productos as $product){
        $id_producto = $product->id_producto;
        $cantidad = $product->cantidad??1;
        if($this->findComboProduct($id_combo,$id_producto) ){
          $this->db->where('id_combo', $id_combo);
          $this->db->where('id_producto', $id_producto);
          $stateProducto = $this->db->update('combo_producto', ['estado'=>1,'cantidad'=>$cantidad]);
        }else{
          $newProduct['id_combo'] = $id_combo;
          $newProduct['id_producto'] = $id_producto;
          $newProduct['cantidad'] = $cantidad;
          $newProduct['estado'] = 1;
          $stateProducto = $this->db->insert('combo_producto', $newProduct);
        }
      } 
    }
    return $stateProducto;
  }
  public function findComboProduct($id_combo,$id_producto) {
    $this->db->where('id_combo', $id_combo);
    $this->db->where('id_producto', $id_producto);
    return $this->db->get_where('combo_producto')->row();
  }
  private function validate_pet_data($data, $id_producto = 0) {
    $this->form_validation->set_data($data);
    $this->form_validation->set_rules('nombre', 'Nombre', 'required|max_length[100]');
    $this->form_validation->set_rules('precio_hora', 'precio hora', 'numeric');//'required|regex_match[/^\d+(\.\d{1,2})?$/]|greater_than[0]'
    $this->form_validation->set_rules('precio_dia', 'precio dia', 'numeric');
    $this->form_validation->set_rules('precio_30dias', 'precio 30 dias', 'numeric');
    return $this->form_validation->run();
  }
}