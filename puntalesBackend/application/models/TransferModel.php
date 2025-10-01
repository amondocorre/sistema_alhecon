<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class TransferModel extends CI_Model {
  protected $table = 'transferencia_almacen_documento'; 
  public function __construct() {
      parent::__construct();
      $this->load->library('form_validation'); 
  }
  public function findIdentity($id) {
      return $this->db->get_where($this->table, ['id_transferencia_almacen_documento' => $id])->row();
  }
  public function getId($compra_documento) {
      return $user->id_transferencia_almacen_documento ?? null;
  }
  public function list($idSucursal,$i_fecha,$f_fecha){
    $url = getHttpHost();
    $sql = "CALL getTransferFilter('$idSucursal','$i_fecha','$f_fecha');";
    $query = $this->db->query($sql);
    $compras = $query->result_array();
    $query->free_result(); 
    $this->db->close();
    $this->db->initialize();
    foreach($compras as $key=>$alquiler){
      $detalle = isset($alquiler['detalle']) ? json_decode(utf8_encode($alquiler['detalle'])) : []; 
      $compras[$key]['detalle']=$detalle;
    }
    return $compras;
  }
  function getIdTransInve(){
    $this->db->select('AUTO_INCREMENT');
    $this->db->from('information_schema.TABLES');
    $this->db->where('TABLE_SCHEMA', $this->db->database);
    $this->db->where('TABLE_NAME', 'transferencia_inventario');
    $query = $this->db->get();
    $proximoId = $query->row()->AUTO_INCREMENT;
    return $proximoId;
  }
  public function register($data,$id_usuario) {
    if (!$this->validate_pefil_data(json_decode(json_encode($data),true))) {
        return FALSE; 
    }
    $fecha = date('Y-m-d H:i:s');
    $detalle = $data->detalle??[];
    $newData = new stdClass();
    $newData->usuario = $id_usuario;
    $newData->fecha = $fecha;
    $newData->sucursal_origen = $data->sucursal_origen??0;
    $newData->sucursal_destino = $data->sucursal_destino;
    $this->db->trans_start();
    $this->db->insert($this->table, $newData);
    $id =  $this->db->insert_id();
    if(!$id) return false;
    //$id = getIdTransInve();
    foreach($detalle as $key=>$det){
        $cantidad = $det->cantidad??0;
        $idProducto = $det->id_producto??0;
        if($cantidad<=0 && !$idProducto)continue;
        $newDetalle = new stdClass();
        $newDetalle->id_transferencia_almacen_documento = $id;
        $newDetalle->id_producto = $idProducto;
        $newDetalle->cantidad = $cantidad;
        $this->db->insert('transferencia_almacen_detalle', $newDetalle);
        $idDetalle =  $this->db->insert_id();
        if(!$idDetalle){ 
          return false;
        }
        $idSucursal = $data->sucursal_origen??0;
        $idSucursalDestino = $data->sucursal_destino??0;
        $inventarioBatch = [];
        $ids = array();
        $inventarios = $this->getInventario($idSucursal,$idProducto,$cantidad);
        foreach ($inventarios as $key => $inv) {
          array_push($ids,$inv->id_inventario);
          $inventarioBatch[] = [
            'id_inventario' => $inv->id_inventario,
            'id_transferencia_almacen_detalle' => $idDetalle ,
          ];
        }
        $this->db->insert_batch('transferencia_inventario', $inventarioBatch);
        $this->db->where_in('id_inventario',$ids);
        $this->db->update('inventario',['id_inventario_movimiento'=>0,'id_sucursal'=>$idSucursalDestino,'fecha_update'=>$fecha]);
    }
    $this->db->trans_complete();
    if ($this->db->trans_status() === FALSE) {
        return false;
    }
    return $id;
  }
  function getInventario($idSucursal,$idProducto,$cantidad){
    $this->db->select('id_inventario')
    ->from('inventario')->where(['id_estado' => 1,'id_sucursal' => $idSucursal,'id_producto' => $idProducto])
    ->where('id_inventario<>',0)
    ->limit($cantidad);
    $query = $this->db->get();
    if ($query->num_rows() > 0) {
        return $query->result();
    } else {
        return array();
    }
  }
  public function update($id, $data) {
    if (!$this->validate_pefil_data($data, $id)) {
        return FALSE;
    }
    $this->db->where('id_transferencia_almacen_documento', $id);
    return $this->db->update($this->table, $data);
  }
  private function validate_pefil_data($data, $id_client = 0) {
    $this->form_validation->set_data($data);
    $this->form_validation->set_rules('sucursal_origen', 'Sucursal Origen', 'required');
    $this->form_validation->set_rules('sucursal_destino', 'Sucursal Destino', 'required');
    return $this->form_validation->run();
  }
}