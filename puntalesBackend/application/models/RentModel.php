<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class RentModel extends CI_Model {
  protected $table = 'alquiler_documento'; 
  public function __construct() {
      parent::__construct();
      $this->load->model('Client_model');
      $this->load->model('caja/BoxMovement');
  }
  public function findIdentity($id) {
      return $this->db->get_where($this->table, ['id_alquiler_documento' => $id])->row();
  }
  public function getId($daycare) {
      return $daycare->id_alquiler_documento ?? null;
  }
  public function findAll() {
    $this->db->select("*");
    $this->db->from($this->table ); 
    $this->db->where('estado', 0); 
    $query = $this->db->get();
    if ($query->num_rows() > 0) {
        return $query->result();
    } else {
        return array();
    }
  }
  public function findActive($id_usuario){
    $this->db->select("c.*,u.nombre as usuario");
    $this->db->from($this->table . ' as c');
    $this->db->join('usuarios as u', 'u.id_usuario = c.id_usuario', 'inner');
    $this->db->where('c.estado', 'Abierta'); 
    $query = $this->db->get();
    if ($query->num_rows() > 0) {
        $turno =$query->result()[0];
        $turno->myTurno = ($turno->id_usuario===$id_usuario);
        return $turno; 
    } else {
        return array(); 
    }
  }
  public function create($data) {
    $data['estado'] = 'Abierta';
    $data['fecha_apertura'] = date('Y-m-d H:i:s');
    return $this->db->insert($this->table, $data);
  }
  public function delete($id) {
    $this->db->where('id', $id);
    return $this->db->update($this->table, ['estado'=>0]);
  }
  public function activate($id) {
    $this->db->where('id', $id);
    return $this->db->update($this->table, ['estado'=>'1']);
  }
  public function registerRent($data,$turno,$idUsuario){
    //$this->db->trans_rollback();
    $this->db->trans_start();
    $fechaActual = date('Y-m-d H:i:s');
    $idTurno = $turno->id;
    $idCliente = $data->id_cliente;
    $aCuenta = $data->a_cuenta-$data->garantia;
    $aCuenta = number_format($aCuenta, 2, '.', '');
    $subTotal = $data->sub_total;
    $descuento = $data->descuento;
    $total = $data->total;
    $garantia = $data->garantia;
    $totalPagar = $total;// $data->monto_pagar;
    $idFormaPago = $data->id_forma_pago;
    $fechaEmision = date('Y-m-d H:i:s');
    $fechaEntrega = $data->fecha_entrega??date('Y-m-d');
    $descripcion = $data->descripcion??'';
    $directorObra = $data->director_obra??'';
    $id_estado_producto = 1;
    $cantidadDias = $data->cantidad_dia;
    $idSucursal = $data->id_sucursal;
    $productos = $data->productos;
    $idPago = 0;
    $estado = true;
    
    $idDocumento = $this->insertDocumento($idCliente,$fechaEmision,$fechaEntrega,$descripcion,$directorObra,$id_estado_producto,$idUsuario,$subTotal,$descuento,$total,$garantia,$totalPagar,$cantidadDias,$idSucursal);
    if($idDocumento){
      if($garantia>0){
        $tipo ='Ingreso';
        $descripcion = 'Garantia del contrato: '.$idDocumento.'';
        $idMovimiento = $this->insertMovimientoCaja($idUsuario,$idTurno,$garantia,$tipo,$descripcion,$idSucursal,$idDocumento,$fechaEmision);
      }
      if($aCuenta>0){
        $observaciones ='';
        $idPago = $this->insertPago($idDocumento,$idCliente,$idUsuario,$idTurno,$aCuenta,$observaciones,$idFormaPago,$fechaEmision);
      }   
      foreach ($productos as $key => $producto){
        $idProducto = $producto->id_producto;
        $cantidad = $producto->cantidad;
        $subTotal = $producto->subTotal;
        $precio = $producto->precioU;
        $tipo = $producto->tipo;
        $esCombo = $producto->es_combo??'0';
        $idDetalle = $this->insertContradoDetalle($idDocumento,$idProducto,$cantidad,$precio,$subTotal,$tipo,$esCombo);
        if($idDetalle){
          if($esCombo=='1'){
            $productosCombo = $producto->productos??[];
            foreach($productosCombo as $key=>$pro){
              $idProductoC = $pro->id_producto;
              $cantidadP = $pro->cantidad;
              $this->descontarInventario($idSucursal,$idDetalle,$idProductoC,$cantidad*$cantidadP);
            }
          }else{
              $this->descontarInventario($idSucursal,$idDetalle,$idProducto,$cantidad);
            }
        }else $estado = false;
      }
    }else $estado = false;
    if($estado){
      $this->db->trans_complete();
    }else $this->db->trans_rollback(); 
    $response = new stdClass();
    $response->status = $estado;
    $response->idPago = $idPago;
    $response->numero = $idDocumento;
    return $response;
  }
  public function registerReturn($data,$turno,$idUsuario,$files){
    //$this->db->trans_rollback();
    $this->db->trans_start();
    $fechaActual = date('Y-m-d H:i:s');
    $idTurno = $turno->id;
    $idCliente = $data->id_cliente??1;
    $aCuenta = $data->a_cuenta-$data->costo_reposicion;
    $aCuenta = number_format($aCuenta, 2, '.', '');
    $subTotal = $data->sub_total;
    $descuento = $data->descuento;
    $total = $data->total;
    $costoReposicion = $data->costo_reposicion;
    $totalPagar = $total;
    $idFormaPago = $data->id_forma_pago;
    $observaciones = $data->observacion??'';
    $id_estado = 2;
    $idSucursal = $data->id_sucursal;
    $productos = $data->productos?json_decode($data->productos,false):[];
    $idPago = 0;
    $estado = true;
    $idDocumento = $data->id_alquiler_documento??0;
    $direccionFotos = '/assets/fotos_devolucion';
    if($idDocumento){
      if($aCuenta>0){
        $observaciones ='';
        $idPago = $this->insertPago($idDocumento,$idCliente,$idUsuario,$idTurno,$aCuenta,$observaciones,$idFormaPago,$fechaActual);
        if(!$idPago) $estado =false;
      }   
      $textProducto='';
      $idDevolucion = $this->insertDocumentoDevolucion($idDocumento,$idCliente,$idUsuario,$observaciones,$costoReposicion,$fechaActual);
      $update = $this->updateDocumentoAlquiler($idDocumento,$fechaActual,$id_estado,$costoReposicion);
      if($idDevolucion && $update){
        foreach ($productos as $key => $producto){
          $idAlquilerDetalle = $producto->id_alquiler_detalle;
          $esCombo = $producto->es_combo??'0';
          if($esCombo==0){
            $estados = $producto->estados??[];
            foreach($estados as $est){
              $idProducto = $producto->id_producto;
              $cantidad = $est->cantidad;
              $idEstadoProducto = $est->id_estado;
              $costoReposicionUnitario = number_format(($est->reposicion==1?$cantidad*$producto->precio_reposicion:0),2,'.','');
              $textProducto = $est->reposicion==1?$textProducto." -".$producto->producto:$textProducto.'';
              $idFile = $idEstadoProducto.'_'.$idAlquilerDetalle.'_foto';
              $file = $files[$idFile]??null;
              $idDevolucionDetalle = $this->insertDetalleDevolucion($idDevolucion,$idAlquilerDetalle,$idProducto,$cantidad,$idEstadoProducto,$costoReposicionUnitario);
              if(!$idDevolucionDetalle){
                $estado = false;
                break;
              }
              $res = $this->devolverInventario($idAlquilerDetalle,$idDevolucionDetalle,$idProducto,$cantidad,$idEstadoProducto);
              if(!$res){
                $estado = false;
                break;
              };
              if($file){
                $foto = guardarArchivo($idDevolucionDetalle,$file,$direccionFotos);
                if($foto) $this->updateImagenDevolucion($idDevolucionDetalle,$foto);
              }
            }
          }else{
            $productosCombo = $producto->detalle??[];
            foreach($productosCombo as $productoCombo){
              $estados = $productoCombo->estados??[];
              foreach($estados as $est){
                $idProducto = $productoCombo->id_producto;
                $cantidad = $est->cantidad;
                $idEstadoProducto = $est->id_estado;
                $costoReposicionUnitario = number_format(($est->reposicion==1?$cantidad*$productoCombo->precio_reposicion:0),2,'.','');
                $textProducto = $est->reposicion==1?$textProducto." -".$productoCombo->nombre:$textProducto.'';
                $idDevolucionDetalle = $this->insertDetalleDevolucion($idDevolucion,$idAlquilerDetalle,$idProducto,$cantidad,$idEstadoProducto,$costoReposicionUnitario);
                $idFile = $idEstadoProducto.'_'.$idAlquilerDetalle.'_'.$idProducto.'_foto';
                $file = $files[$idFile]??null;
                if(!$idDevolucionDetalle){
                  $estado = false;
                  break;
                }
                $res = $this->devolverInventario($idAlquilerDetalle,$idDevolucionDetalle,$idProducto,$cantidad,$idEstadoProducto);
                if(!$res){
                  $estado = false;
                  break;
                };
                if($file){
                  $foto = guardarArchivo($idDevolucionDetalle,$file,$direccionFotos);
                  if($foto) $this->updateImagenDevolucion($idDevolucionDetalle,$foto);
                }
              } 
              if(!$estado) break;
            }
          }
          if(!$estado) break;
        }
        if($costoReposicion>0){
          $tipo ='Ingreso';
          $descripcion = 'costo reposicion de los productos: '.$textProducto.'';
          $idMovimiento = $this->insertMovimientoCaja($idUsuario,$idTurno,$costoReposicion,$tipo,$descripcion,$idSucursal,$idDocumento,$fechaActual);
          if(!$idMovimiento) $estado = false;
        }
      }
    }else $estado = false;
    if($estado){
      $this->db->trans_complete();
    }else $this->db->trans_rollback(); 
    $response = new stdClass();
    $response->status = $estado;
    $response->idPago = $idPago;
    $response->numero = $idDocumento;
    return $response;
  }
  public function insertDocumento($idCliente,$fechaEmision,$fechaEntrega,$descripcion,$directorObra,$id_estado_producto,$id_usuario,$subTotal,$descuento,$total,$garantia,$totalPagar,$cantidadDias,$idSucursal){
    $niewData = new stdClass();
    $niewData->id_cliente = $idCliente;
    $niewData->fecha_emision = $fechaEmision;
    $niewData->fecha_entrega = $fechaEntrega;
    $niewData->descripcion = $descripcion;
    $niewData->director_obra = $directorObra;
    $niewData->id_estado_alquiler = $id_estado_producto;
    $niewData->id_usuario = $id_usuario;
    $niewData->sub_total = $subTotal;
    $niewData->descuento = $descuento;
    $niewData->total = $total;
    $niewData->garantia = $garantia;
    $niewData->total_pagar = $totalPagar;
    $niewData->cantidad_dias = $cantidadDias;
    $niewData->id_sucursal = $idSucursal;
    $this->db->insert('alquiler_documento', $niewData);
    return $this->db->insert_id();
  }
  public function insertContradoDetalle($idDocumento,$idProducto,$cantidad,$precio,$subTotal,$tipo,$esCombo) {
    $niewData = new stdClass();
    $niewData->id_alquiler_documento = $idDocumento;
    $niewData->id_producto = $idProducto;
    $niewData->cantidad = $cantidad;
    $niewData->precio_unitario = $precio;
    $niewData->subtotal = $subTotal;
    $niewData->es_combo = $esCombo;
    $niewData->tipo = $tipo;
    $this->db->insert('alquiler_detalle', $niewData);
    return $this->db->insert_id();
  }
  public function insertMovimientoInv($idInventario,$idDetalle,$idDevolucionDetalle) {
    $niewData = new stdClass();
    $niewData->id_inventario = $idInventario;
    $niewData->id_alquiler_detalle = $idDetalle;
    $niewData->id_alquiler_devolucion_detalle = $idDevolucionDetalle;
    $niewData->estado = 1;
    $this->db->insert('inventario_movimiento', $niewData);
    return $this->db->insert_id();
  }
  function insertMovimientoCaja($idUsuario,$idTurno,$monto,$tipo,$descripcion,$idSucursal,$idDocumento,$fecha){
    $data['id_usuario'] = $idUsuario;
    $data['id_caja'] = $idTurno;
    $data['monto'] = $monto;
    $data['tipo'] = $tipo;
    $data['descripcion'] = $descripcion;
    $data['id_sucursal'] = $idSucursal;    
    $data['fecha_movimiento'] = $fecha;
    $data['id_alquiler_documento'] = $idDocumento;
    $id = $this->BoxMovement->create($data);
    return $id;
  }
  public function insertPago($idDocumento,$idCliente,$idUsuario,$idCaja,$monto,$observaciones,$idFormaPago,$fechaPago) {
    $niewData = new stdClass();
    $niewData->id_alquiler_documento = $idDocumento;
    $niewData->id_cliente = $idCliente;
    $niewData->id_usuario = $idUsuario;
    $niewData->id_caja = $idCaja;
    $niewData->monto = $monto;
    $niewData->anulado = 'no';
    $niewData->observaciones = $observaciones;
    $niewData->id_forma_pago = $idFormaPago;
    $niewData->fecha_pago = $fechaPago;
    $this->db->insert('pago', $niewData);
    return $this->db->insert_id();
  }
  public function updateInventario($idInventario,$idMovimiento,$idEstado) {
    $data['id_inventario_movimiento']=$idMovimiento;
    $data['id_estado'] = $idEstado;
    $this->db->where('id_inventario', $idInventario);
    $this->db->update('inventario', $data);
    return $this->db->affected_rows(); 
  }
  public function updateInventarioMovimiento($idInventarioMovimiento) {
    $data['estado'] = 0;
    $this->db->where('id_inventario_movimiento', $idInventarioMovimiento);
    $this->db->update('inventario_movimiento', $data);
    return $this->db->affected_rows(); 
  }
  // devolucion 
  public function insertDocumentoDevolucion($idDocumento,$idCliente,$idUsuario,$observaciones,$costoReposicion,$fecha) {
    $niewData = new stdClass();
    $niewData->id_alquiler_documento = $idDocumento;
    $niewData->id_cliente = $idCliente;
    $niewData->id_usuario = $idUsuario;
    $niewData->fecha_devolucion = $fecha;
    $niewData->observaciones = $observaciones;
    $niewData->costo_reposicion = $costoReposicion;
    $this->db->insert('alquiler_devolucion_documento', $niewData);
    return $this->db->insert_id();
  }
  public function insertDetalleDevolucion($idDevolucion,$idAlquilerDetalle,$idProducto,$cantidad,$idEstadoProducto,$costoReposicion) {
    $niewData = new stdClass();
    $niewData->id_alquiler_devolucion = $idDevolucion;
    $niewData->id_alquiler_detalle = $idAlquilerDetalle;
    $niewData->id_producto = $idProducto;
    $niewData->cantidad_devuelta = $cantidad;
    $niewData->id_estado_producto = $idEstadoProducto;
    $niewData->costo_reposicion = $costoReposicion;
    $niewData->imagen = '';
    $this->db->insert('alquiler_devolucion_detalle', $niewData);
    return $this->db->insert_id();
  }
  function devolverInventario($idAlquilerDetalle,$idDevolucionDetalle,$idProducto,$cantidad,$idEstadoProducto){
    $inventarios = $this->getInventarioMovimiento($idAlquilerDetalle,$idProducto,$cantidad);
    $estadoUpdate = false;
    $idDetalle=0;
    foreach ($inventarios as $key => $inv) {
      $idInventario = $inv->id_inventario;
      $idInventarioMovimiento = $inv->id_inventario_movimiento;
      $id = $this->insertMovimientoInv($idInventario,$idDetalle,$idDevolucionDetalle);
      if($id){
        $this->updateInventarioMovimiento($idInventarioMovimiento);
        $estadoUpdate = $this->updateInventario($idInventario,$id,$idEstadoProducto);
        if(!$estadoUpdate)break;
      }else {
        $estadoUpdate = false;
        break;
      }
      
    }
    return $estadoUpdate;
  }

  public function updateDocumentoAlquiler($idDocumento,$fecha,$id_estado,$costoReposicion){
    $niewData = new stdClass();
    $niewData->fecha_devolucion = $fecha;
    $niewData->id_estado_alquiler = $id_estado;
    $niewData->costo_reposicion =('costo_reposicion+'.$costoReposicion);
    $this->db->where('id_alquiler_documento',$idDocumento);
    $this->db->update('alquiler_documento', $niewData);
    return $this->db->affected_rows();
  }
  public function updateImagenDevolucion($idDetalleDevolucion,$imagen){
    $niewData = new stdClass();
    $niewData->imagen = $imagen;
    $this->db->where('id_alquiler_devolucion_detalle',$idDetalleDevolucion);
    $this->db->update('alquiler_devolucion_detalle', $niewData);
    return $this->db->affected_rows();
  }
  function descontarInventario($idSucursal,$idDetalle,$idProducto,$cantidad){
    $inventarios = $this->getInventario($idSucursal,$idProducto,$cantidad);
    $idDevolucionDetalle = 0;
    $idEstado = 2;
    foreach ($inventarios as $key => $inv) {
      $idInventario = $inv->id_inventario;
      $id = $this->insertMovimientoInv($idInventario,$idDetalle,$idDevolucionDetalle);
      $this->updateInventario($idInventario,$id,$idEstado);
    }

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
  function getInventarioMovimiento($idAlquilerDetalle,$idProducto,$cantidad){
    $this->db
      ->select('i.id_inventario, im.id_inventario_movimiento')
      ->from('inventario AS i')
      ->join('inventario_movimiento AS im', 'im.id_inventario = i.id_inventario', 'inner')
      ->where([
          'im.estado' => 1,
          'im.id_alquiler_detalle' => $idAlquilerDetalle,
          'i.id_producto' => $idProducto,
      ])
      ->where('i.id_inventario !=', 0)
      ->limit($cantidad);

    $query = $this->db->get();
    if ($query->num_rows() > 0) {
        return $query->result();
    } else {
        return array();
    }
  }
//------------------------------------------------------------------------
  public function getAlquilereFilter($idSucursal,$idEstado,$i_fecha,$f_fecha) {
    $sql = "CALL getalquilereFilter('$idSucursal','$idEstado','$i_fecha','$f_fecha');";
    $query = $this->db->query($sql);
    $alquileres = $query->result_array();
    $query->free_result(); 
    $this->db->close();
    $this->db->initialize();
    foreach($alquileres as $key=>$alquiler){
      $detalle = isset($alquiler['detalle']) ? json_decode(utf8_encode($alquiler['detalle'])) : []; 
      $alquileres[$key]['detalle']=$detalle;
    }
    return $alquileres;
  }
  public function getAlquilerById($idContrato) {
    $sql = "CALL getAlquilerById('$idContrato');";
    $query = $this->db->query($sql);
    $alquileres = $query->result_array();
    $query->free_result(); 
    $this->db->close();
    $this->db->initialize();
    foreach($alquileres as $key=>$alquiler){
      $detalle = isset($alquiler['detalle']) ? json_decode(utf8_encode($alquiler['detalle'])) : []; 
      $alquileres[$key]['detalle']=$detalle;
    }
    return $alquileres;
  }
  public function getDataContratoByID($idContrato) {
    $sql = "CALL getDataContratoByID('$idContrato');";
    $query = $this->db->query($sql);
    $alquileres = $query->result_array();
    $query->free_result(); 
    $this->db->close();
    $this->db->initialize();
    foreach($alquileres as $key=>$alquiler){
      $productos = isset($alquiler['productos']) ? json_decode(utf8_encode($alquiler['productos'])) : []; 
      $contrato = isset($alquiler['contrato']) ? json_decode(utf8_encode($alquiler['contrato'])) : null; 
      $alquileres[$key]['productos']=$productos;
      $alquileres[$key]['contrato']=$contrato;
    }
    return $alquileres[0]??null;
  }
  function getEstados(){
    $this->db->select('*')->from('estado_producto');
    $query = $this->db->get();
    if ($query->num_rows() > 0) {
        return $query->result();
    } else {
        return array();
    }
  }
}
