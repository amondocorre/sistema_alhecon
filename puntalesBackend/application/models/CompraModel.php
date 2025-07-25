<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class CompraModel extends CI_Model {
    protected $table = 'compra_documento'; 
    public function __construct() {
        parent::__construct();
        $this->load->library('form_validation'); 
    }
    public function findIdentity($id) {
        return $this->db->get_where($this->table, ['id_compra_documento' => $id])->row();
    }
    public function getId($compra_documento) {
        return $user->id_compra_documento ?? null;
    }
    public function list($idSucursal,$idProveedor,$i_fecha,$f_fecha){
      $url = getHttpHost();
      $sql = "CALL getCompraFilter('$idSucursal','$idProveedor','$i_fecha','$f_fecha');";
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
    public function register($data,$id_usuario) {
      if (!$this->validate_pefil_data(json_decode(json_encode($data),true))) {
          return FALSE; 
      }
      $fecha = date('Y-m-d H:i:s');
      $data->id_usuario = $id_usuario;
      $data->fecha_registro = $fecha;
      $detalle = $data->detalle??[];//?json_decode($data->detalle,false):[];
      unset($data->detalle);
      $this->db->trans_start();
      $this->db->insert($this->table, $data);
      $id =  $this->db->insert_id();
      if(!$id) return false;
      foreach($detalle as $key=>$det){
          $det->id_compra_documento = $id;
          $cantidad = $det->cantidad;
          $this->db->insert('compra_detalle', $det);
          $idDetalle =  $this->db->insert_id();
          if(!$idDetalle){ 
            return false;
          }
          $inventarioBatch = [];
          for ($i=0; $i < $cantidad; $i++) { 
            $inventarioBatch[] = [
              'id_producto' => $det->id_producto,
              'fecha_registro' => $fecha,
              'id_tipo_movimiento' => 1,
              'id_estado' => 1,
              'observaciones' => '',
              'id_compra_detalle' => $idDetalle,
              'id_inventario_movimiento' => 0,
              'id_sucursal' => $data->id_sucursal
            ];
          }
          $this->db->insert_batch('inventario', $inventarioBatch);
      }
    $this->db->trans_complete();
    if ($this->db->trans_status() === FALSE) {
        return false;
    }
      return $id;
    }
  public function update($id, $data) {
    if (!$this->validate_pefil_data($data, $id)) {
        return FALSE;
    }
    $this->db->where('id_compra_documento', $id);
    return $this->db->update($this->table, $data);
  }
  private function validate_pefil_data($data, $id_client = 0) {
    $this->form_validation->set_data($data);
    $this->form_validation->set_rules('id_proveedor', 'Proveedor', 'required|max_length[100]');
    $this->form_validation->set_rules('fecha_compra', 'Fecha Compra', 'required|max_length[25]');
    $this->form_validation->set_rules('numero_factura', 'Numero Factura', 'required');
    $this->form_validation->set_rules('total', 'Total', 'required');
    $this->form_validation->set_rules('id_sucursal', 'Sucursal', 'required');
    return $this->form_validation->run();
  }
}