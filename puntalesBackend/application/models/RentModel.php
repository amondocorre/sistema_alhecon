<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class RentModel extends CI_Model {
  protected $table = 'alquiler_documento'; 
  public function __construct() {
      parent::__construct();
      $this->load->model('Client_model');
      $this->load->model('caja/BoxMovement');
      $this->load->model('InventoryModel');
  }
  public function findIdentity($id) {
      return $this->db->get_where($this->table, ['id_alquiler_documento' => $id])->row();
  }
  public function getId($daycare) {
      return $daycare->id_alquiler_documento ?? null;
  }

  public function registerRent($data,$turno,$idUsuario){
    //$this->db->trans_rollback();
    $this->db->trans_start();
    $fechaActual = date('Y-m-d H:i:s');
    $idTurno = $turno->id;
    $idCliente = $data->id_cliente;
    $aCuenta = $data->a_cuenta??0;
    $aCuenta = $aCuenta-$data->garantia;
    $aCuenta = number_format($aCuenta, 2, '.', '');
    $subTotal = $data->sub_total;
    $descuento = $data->descuento;
    $total = $data->total;
    $garantia = $data->garantia;
    $totalPagar = $total;// $data->monto_pagar;
    $idFormaPago = $data->id_forma_pago;
    $fechaEmision = date('Y-m-d H:i:s');
    $fechaEntrega = $data->fecha_entrega??date('Y-m-d');
    $fechaDevolucion =$data->fecha_devolucion??'';
    $descripcion = $data->descripcion??'';
    $id_estado_producto = 1;
    $cantidadDias = $data->cantidad_dia;
    $idSucursal = $data->id_sucursal;
    $idTransporte = $data->id_transporte??0;
    $direccion = $data->direccion??'';
    $direccionGps = $data->direccion_gps??'';
    $productos = $data->productos;
    $contactos = $data->contactos??0;
    $isContrato = $data->contrato??'0';
    $idSucursalSalida = $data->id_sucursal_salida??0;
    if($isContrato=='1'){
      $faltantes = $this->verificarStock($productos,$idSucursalSalida?$idSucursalSalida:$idSucursal);
      if(count($faltantes)>0){
        $response = new stdClass();
        $response->status = false;
        $response->faltantes = $faltantes;
        $response->message = 'Le falata los siguientes productos en stock.';
        return $response;
      }
    }
    $idPago = 0;
    $estado = true;
    $idSucursalSalida = $idSucursalSalida?$idSucursalSalida:$idSucursal;
    $numeroPedido =$isContrato=='0'?0: $this->obtenerMaxNumeroPedido($idSucursalSalida)+1;
    $idDocumento = $this->insertDocumento($idCliente,$fechaEmision,$fechaEntrega,$fechaDevolucion,$descripcion,$id_estado_producto,$idUsuario,$subTotal,$descuento,$total,$garantia,$totalPagar,$cantidadDias,$idSucursalSalida,$idTransporte,$direccion,$direccionGps,$idSucursal,$numeroPedido,$isContrato);
    if($idDocumento){
      for ($i=1; $i <=$contactos ; $i++) { 
        $contacto = $data->{'contacto_'.$i}??'';
        $telefono = $data->{'telefono_'.$i}??'';
        $descripcion = $data->{'descripcion_'.$i}??'';
        if($contacto && $telefono){
          $this->registerContactoObra($idDocumento,$contacto,$telefono,$descripcion);
        }
      }
      if($garantia>0){
        $tipo ='Ingreso';
        $descripcion = 'Garantia del contrato: '.$idDocumento.'';
        $idMovimiento = $this->insertMovimientoCaja($idUsuario,$idTurno,$garantia,$tipo,$descripcion,$idSucursal,$idDocumento,$fechaEmision);
      }
      if($aCuenta>0){
        $observaciones ='';
        $idPago = $this->insertPago($idDocumento,$idSucursal,$idCliente,$idUsuario,$idTurno,$aCuenta,$observaciones,$idFormaPago,$fechaEmision);
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
          if($isContrato=='0') continue;
          if($esCombo=='1'){
            $productosCombo = $producto->productos??[];
            foreach($productosCombo as $key=>$pro){
              $idProductoC = $pro->id_producto;
              $cantidadP = $pro->cantidad;
              $this->descontarInventario($idSucursalSalida,$idDetalle,$idProductoC,$cantidad*$cantidadP);
            }
          }else{
              $this->descontarInventario($idSucursalSalida,$idDetalle,$idProducto,$cantidad);
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
  public function updateRent($id,$data,$turno,$idUsuario){
    $this->db->trans_start();
    $fechaActual = date('Y-m-d H:i:s');
    $subTotal = $data->sub_total;
    $total = $data->total;
    $totalPagar = $total;
    $fechaEmision = date('Y-m-d H:i:s');
    $fechaDevolucion =$data->fecha_devolucion??'';
    $cantidadDias = $data->cantidad_dias;
    $idSucursal = $data->id_sucursal;
    $productos = $data->detalle;
    $idDocumento = $id;
    $idPago = 0;
    $estado = true;
    $this->db->where('id_alquiler_documento',$idDocumento);
    $this->db->update('alquiler_documento', ['cantidad_dias'=>$cantidadDias,'total'=>$total,'sub_total'=>$subTotal,'total_pagar'=>$totalPagar,'fecha_devolucion'=>$fechaDevolucion]);
    if($this->db->affected_rows()>0){
      foreach ($productos as $key => $producto){
        $idDetalle = $producto->id_alquiler_detalle??0;
        $subTotal = $producto->sub_total;
        $precio = $producto->presio_unitario;
        $this->db->where('id_alquiler_detalle',$idDetalle);
        $this->db->update('alquiler_detalle', ['precio_unitario'=>$precio,'subtotal'=>$subTotal]);
        if($this->db->affected_rows()==0) $estado = false;
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
    $precioTotalAtraso = $data->precio_total_atraso??0;
    $diasAtraso = $data->dias_atraso??0;
    $costosAtraso = isset($data->costos_atraso)?json_decode($data->costos_atraso,false):[];
    $id_estado = 6;// estado del alquiler Finalizado
    $idSucursal = $data->id_sucursal;
    $productos = $data->productos?json_decode($data->productos,false):[];
    $idPago = 0;
    $estado = true;
    $idDocumento = $data->id_alquiler_documento??0;
    $direccionFotos = '/assets/fotos_devolucion';
    if($idDocumento){
      if($aCuenta>0){
        $observaciones ='';
        $idPago = $this->insertPago($idDocumento,$idSucursal,$idCliente,$idUsuario,$idTurno,$aCuenta,$observaciones,$idFormaPago,$fechaActual);
        if(!$idPago) $estado =false;
      }   
      $textProducto='';
      $idDevolucion = $this->insertDocumentoDevolucion($idDocumento,$idCliente,$idUsuario,$observaciones,$costoReposicion,$fechaActual);
      $update = $this->updateDocumentoAlquiler($idDocumento,$fechaActual,$id_estado,$costoReposicion,$precioTotalAtraso,$diasAtraso);
      if($idDevolucion && $update){
        foreach ($productos as $key => $producto){
          $idAlquilerDetalle = $producto->id_alquiler_detalle;
          if($precioTotalAtraso>0){
            $costos = $costosAtraso->$idAlquilerDetalle??null;
            $costo = $costos->precio_atraso_total??0;
            if($costo>0) $this->updateCostoAtradoDetalle($idAlquilerDetalle,$costo); 
          }
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
  public function registerPagoDeuda($idTurno,$idSucursal,$idUsuario,$idDocumento,$idFormaPago,$aCuenta,$idCliente){
    $this->db->trans_start();
    $fechaActual = date('Y-m-d H:i:s');
    $aCuenta = number_format($aCuenta, 2, '.', '');
    $idPago = 0;
    $estado = true;
    if($aCuenta>0 && $idDocumento){
      $observaciones ='';
      $idPago = $this->insertPago($idDocumento,$idSucursal,$idCliente,$idUsuario,$idTurno,$aCuenta,$observaciones,$idFormaPago,$fechaActual);
      if(!$idPago) $estado =false;
    }else $estado =false;
    if($estado){
      $this->db->trans_complete();
    }else $this->db->trans_rollback(); 
    $response = new stdClass();
    $response->status = $estado;
    $response->idPago = $idPago;
    return $response;
  }
  public function registerPagosDeudas($idTurno,$idSucursal,$idUsuario,$contratos,$idFormaPago,$aCuenta){
    $this->db->trans_start();
    $fechaActual = date('Y-m-d H:i:s');
    $aCuenta = number_format($aCuenta, 2, '.', '');
    $idPago = 0;
    $estado = true;
    $pagos = array();
    foreach($contratos as $key=>$contrato){
      $idDocumento = $contrato->id_alquiler_documento;
      $saldo = $contrato->deuda??0;
      $idCliente = $contrato->id_cliente;
      $aCuentaAux = $aCuenta>=$saldo?$saldo:$aCuenta;
      $aCuenta = $aCuenta>=$saldo?$aCuenta-$saldo:0;
      if($aCuentaAux>0 && $idDocumento){
        $observaciones ='';
        $idPago = $this->insertPago($idDocumento,$idSucursal,$idCliente,$idUsuario,$idTurno,$aCuentaAux,$observaciones,$idFormaPago,$fechaActual);
        if(!$idPago){ 
          $estado =false;
          break;
        }
        array_push($pagos,$idPago);
        if($aCuenta<=0)break;
      }else break;
    }
    if($estado){
      $this->db->trans_complete();
    }else $this->db->trans_rollback(); 
    $response = new stdClass();
    $response->status = $estado;
    $response->pagos = $pagos;
    return $response;
  }
  public function registerEntrega($idUsuario,$idDocumento,$idArchivoTransporte){
    $this->db->trans_start();
    $fechaActual = date('Y-m-d H:i:s');
    $estado = false;
    $id_estado = 2;// estado alguiler entregado
    if($idDocumento){
      $observaciones ='';
      if($this->updateEstadoDocumentoAlquiler($idDocumento,$id_estado,$idArchivoTransporte)) {
        $estado =true;
      }
    };
    if($estado){
      $this->db->trans_complete();
    }else $this->db->trans_rollback(); 
    return $estado;
  }
  public function registerValidacionEntrega($idUsuario,$idDocumento,$idArchivoTransporte){
    $this->db->trans_start();
    $fechaActual = date('Y-m-d H:i:s');
    $estado = false;
    $id_estado = 3;// estado alguiler validacion entregado
    if($idDocumento){
      $observaciones ='';
      if($this->updateEstadoDocumentoAlquiler($idDocumento,$id_estado,$idArchivoTransporte)) {
        $estado =true;
      }
    };
    if($estado){
      $this->db->trans_complete();
    }else $this->db->trans_rollback(); 
    return $estado;
  }
  function registerContactoObra($idDocumento,$contacto,$telefono,$descripcion){
    $newData = new stdClass();
    $newData->id_alquiler_documento = $idDocumento;
    $newData->contacto = $contacto;
    $newData->telefono = $telefono;
    $newData->descripcion = $descripcion;
    $this->db->insert('contacto_alquiler', $newData);
    return $this->db->insert_id();
  }

  public function insertDocumento($idCliente,$fechaEmision,$fechaEntrega,$fechaDevolucion,$descripcion,$id_estado_producto,$id_usuario,$subTotal,$descuento,$total,$garantia,$totalPagar,$cantidadDias,$idSucursal,$idTransporte,$direccion,$direccionGps,$idSucursalR,$numeroPedido,$isContrato){
    $niewData = new stdClass();
    $niewData->id_cliente = $idCliente;
    $niewData->fecha_emision = $fechaEmision;
    $niewData->fecha_entrega = $fechaEntrega;
    $niewData->descripcion = $descripcion;
    $niewData->id_estado_alquiler = $id_estado_producto;
    $niewData->id_usuario = $id_usuario;
    $niewData->sub_total = $subTotal;
    $niewData->descuento = $descuento;
    $niewData->total = $total;
    $niewData->garantia = $garantia;
    $niewData->total_pagar = $totalPagar;
    $niewData->cantidad_dias = $cantidadDias;
    $niewData->id_sucursal = $idSucursal;
    $niewData->precio_atraso = 0;
    $niewData->direccion_obra = $direccion;
    $niewData->ubicacion_gps_obra = $direccionGps;
    $niewData->id_sucursal_registro = $idSucursalR;
    $niewData->numero_pedido = $numeroPedido;
    $niewData->contrato = $isContrato;
    if($fechaDevolucion) $niewData->fecha_devolucion = $fechaDevolucion;
    //if($idTransporte) 
    $niewData->id_transporte = $idTransporte;
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
  public function insertPago($idDocumento,$idSucursal,$idCliente,$idUsuario,$idCaja,$monto,$observaciones,$idFormaPago,$fechaPago) {
    $niewData = new stdClass();
    $niewData->id_alquiler_documento = $idDocumento;
    $niewData->id_sucursal = $idSucursal;
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

  public function updateDocumentoAlquiler($idDocumento,$fecha,$id_estado,$costoReposicion,$precioTotalAtraso,$diasAtraso){
    $niewData = new stdClass();
    //$niewData->fecha_devolucion = $fecha;
    $niewData->id_estado_alquiler = $id_estado;
    $niewData->costo_reposicion = ('costo_reposicion+'.$costoReposicion);
    $niewData->precio_atraso = $precioTotalAtraso;
    $niewData->dias_atraso = $diasAtraso;
    $this->db->where('id_alquiler_documento',$idDocumento);
    $this->db->update('alquiler_documento', $niewData);
    return $this->db->affected_rows();
  }
  function updateCostoAtradoDetalle($idAlquilerDetalle,$costo){
    $newData = new stdClass();
    $newData->precio_atraso = $costo;
    $this->db->where('id_alquiler_detalle',$idAlquilerDetalle);
    $this->db->update('alquiler_detalle', $newData);
    return $this->db->affected_rows();
  }
  public function updateEstadoDocumentoAlquiler($idDocumento,$id_estado,$idArchivoTransporte){
    $niewData = new stdClass();
    $niewData->id_estado_alquiler = $id_estado;
    $niewData->id_archivo_transporte = $idArchivoTransporte;
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
  function verificarStock($productos = [], $idSucursal) {
    $stocks = $this->InventoryModel->getStock($idSucursal);;
    $faltantes = [];
    foreach ($productos as $combo) {
      $cantidadCombo = isset($combo->cantidad) ? (int)$combo->cantidad : 0;
      if (isset($combo->es_combo) && $combo->es_combo === '1' && !empty($combo->productos)) {
        $minStock = PHP_INT_MAX;
        foreach ($combo->productos as $producto) {
          $id = isset($producto->id_producto) ? $producto->id_producto : 0;
          $available = isset($stocks[$id]) ? (int)$stocks[$id] : 0;
          $required = isset($producto->cantidad) ? (int)$producto->cantidad : 0;
          $restante = ($required > 0) ? floor($available / $required) : 0;
          $stocks[$id] = $available - ($required * $cantidadCombo);
          $minStock = min($minStock, $restante);
        }
        if ($minStock < $cantidadCombo) {
          $combo->faltante = strval($cantidadCombo - $minStock);
          $faltantes[] = $combo;
        }
      } else {
        $id = isset($combo->id_producto) ? $combo->id_producto : 0;
        $stock = isset($stocks[$id]) ? (int)$stocks[$id] : 0;
        $required = $cantidadCombo;
        $stocks[$id] = $stock - $required;
        if ($stock < $required) {
          $combo->faltante = strval($required - $stock);
          $faltantes[] = $combo;
        }
      }
    }
    return $faltantes;
  }
  function obtenerMaxNumeroPedido($id_sucursal){
      $this->db->select_max('numero_pedido');
      $this->db->where('id_sucursal', $id_sucursal);
      $query = $this->db->get('alquiler_documento');
      return $query->row()->numero_pedido ?? 0; 
  }
//------------------------------------------------------------------------
  public function getAlquilereFilter($idSucursal,$idEstado,$i_fecha,$f_fecha) {
    $url = getHttpHost();
    $sql = "CALL getalquilereFilter('$idSucursal','$idEstado','$i_fecha','$f_fecha');";
    $query = $this->db->query($sql);
    $alquileres = $query->result_array();
    $query->free_result(); 
    $this->db->close();
    $this->db->initialize();
    foreach($alquileres as $key=>$alquiler){
      $detalle = isset($alquiler['detalle']) ? json_decode(utf8_encode($alquiler['detalle'])) : []; 
      usort($detalle, function($a, $b) {return $a->nombre <=> $b->nombre;});
      $alquileres[$key]['detalle']=$detalle;
      $alquileres[$key]['fotografia_movilidad']=isset($alquiler['fotografia_movilidad'])?$url.$alquiler['fotografia_movilidad']:null;
    }
    return $alquileres;
  }
  public function getAlquilerEntregaFilter($idSucursal,$idEstado,$i_fecha,$f_fecha) {
    $url = getHttpHost();
    $sql = "CALL getAlquilerEntregaFilter('$idSucursal','$idEstado','$i_fecha','$f_fecha');";
    $query = $this->db->query($sql);
    $alquileres = $query->result_array();
    $query->free_result(); 
    $this->db->close();
    $this->db->initialize();
    foreach($alquileres as $key=>$alquiler){
      $detalle = isset($alquiler['detalle']) ? json_decode(utf8_encode($alquiler['detalle'])) : []; 
      usort($detalle, function($a, $b) {return $a->nombre <=> $b->nombre;});
      $alquileres[$key]['fotografia_movilidad']=isset($alquiler['fotografia_movilidad'])?$url.$alquiler['fotografia_movilidad']:null;
      $alquileres[$key]['detalle']=$detalle;
    }
    return $alquileres;
  }
  public function getAlquilerClienteFilter($idCliente,$idEstado) {
    $url = getHttpHost();
    $sql = "CALL getAlquilerClienteFilter('$idCliente','$idEstado');";
    $query = $this->db->query($sql);
    $alquileres = $query->result_array();
    $query->free_result(); 
    $this->db->close();
    $this->db->initialize();
    foreach($alquileres as $key=>$alquiler){
      $detalle = isset($alquiler['detalle']) ? json_decode(utf8_encode($alquiler['detalle'])) : []; 
      usort($detalle, function($a, $b) {return $a->nombre <=> $b->nombre;});
      $alquileres[$key]['detalle']=$detalle;
      $alquileres[$key]['fotografia_movilidad']=isset($alquiler['fotografia_movilidad'])?$url.$alquiler['fotografia_movilidad']:null;
    }
    return $alquileres;
  }
  public function getAlquilerById($idContrato) {
    $url = getHttpHost();
    $sql = "CALL getAlquilerById('$idContrato');";
    $query = $this->db->query($sql);
    $alquileres = $query->result_array();
    $query->free_result(); 
    $this->db->close();
    $this->db->initialize();
    foreach($alquileres as $key=>$alquiler){
      $detalle = isset($alquiler['detalle']) ? json_decode(utf8_encode($alquiler['detalle'])) : []; 
      $alquileres[$key]['detalle']=$detalle;
      $alquileres[$key]['fotografia_movilidad']=isset($alquiler['fotografia_movilidad'])?$url.$alquiler['fotografia_movilidad']:null;
    }
    return $alquileres[0]??null;
  }
  public function getProductosAlquilerById($idContrato) {
    $sql = "CALL getProductosAlquilerById('$idContrato');";
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
  public function getAlquilerNombreCliente($cliente) {
    $url = getHttpHost();
    $sql = "CALL getalquilerNombreCliente('$cliente');";
    $query = $this->db->query($sql);
    $alquileres = $query->result_array();
    $query->free_result(); 
    $this->db->close();
    $this->db->initialize();
    foreach($alquileres as $key=>$alquiler){
      $detalle = isset($alquiler['detalle']) ? json_decode(utf8_encode($alquiler['detalle'])) : []; 
      usort($detalle, function($a, $b) {return $a->nombre <=> $b->nombre;});
      $alquileres[$key]['detalle']=$detalle;
      $alquileres[$key]['fotografia_movilidad']=isset($alquiler['fotografia_movilidad'])?$url.$alquiler['fotografia_movilidad']:null;
    }
    return $alquileres?$alquileres:[];
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
  function getEstadoAlquiler(){
    $this->db->select('*')->from('estado_alquiler');
    $query = $this->db->get();
    if ($query->num_rows() > 0) {
        return $query->result();
    } else {
        return array();
    }
  }
  public function getAlquilerDeuda($idSucursal) {
    $url = getHttpHost();
    $sql = "CALL getalquilerDeuda('$idSucursal');";
    $query = $this->db->query($sql);
    $alquileres = $query->result_array();
    $query->free_result(); 
    $this->db->close();
    $this->db->initialize();
    foreach($alquileres as $key=>$alquiler){
      $detalle = isset($alquiler['detalle']) ? json_decode(utf8_encode($alquiler['detalle'])) : []; 
      $alquileres[$key]['detalle']=$detalle;
      $alquileres[$key]['fotografia_movilidad']=isset($alquiler['fotografia_movilidad'])?$url.$alquiler['fotografia_movilidad']:null;
    }
    return $alquileres;
  }
  public function getAlquileres($limit, $offset,$idSucursal) {
    $this->db->select("a.id_alquiler_documento, a.fecha_devolucion,a.id_estado_alquiler,
        CONCAT(c.nombres, ' ', c.ap_paterno, ' ', c.ap_materno) AS cliente,
        (a.total_pagar+a.precio_atraso) as total_pagar,
        IFNULL(pagos.monto_pagado, 0) AS a_cuenta");
    $this->db->from("alquiler_documento a");
    $this->db->join("cliente c", "c.id_cliente = a.id_cliente");
    $this->db->join("estado_alquiler ea", "ea.id_estado_alquiler = a.id_estado_alquiler");
    $this->db->join("(SELECT id_alquiler_documento, SUM(monto) AS monto_pagado 
                      FROM pago 
                      WHERE anulado = 'no' 
                      GROUP BY id_alquiler_documento) pagos", 
                      "pagos.id_alquiler_documento = a.id_alquiler_documento", "left");
    if($idSucursal>0) $this->db->where('id_sucursal',$idSucursal);
    $this->db->where('contrato','1');
    $this->db->where_in('a.id_estado_alquiler', [3,5]);
    $this->db->order_by('fecha_devolucion', 'asc');
    $this->db->limit($limit, $offset);
    return $this->db->get()->result();
  }
  public function getAlquileresEntrega($limit, $offset,$idSucursal) {
    $this->db->select("a.id_alquiler_documento, a.fecha_entrega,a.id_estado_alquiler,
        CONCAT(c.nombres, ' ', c.ap_paterno, ' ', c.ap_materno) AS cliente,
        (a.total_pagar+a.precio_atraso) as total_pagar,
        IFNULL(pagos.monto_pagado, 0) AS a_cuenta");
    $this->db->from("alquiler_documento a");
    $this->db->join("cliente c", "c.id_cliente = a.id_cliente");
    $this->db->join("estado_alquiler ea", "ea.id_estado_alquiler = a.id_estado_alquiler");
    $this->db->join("(SELECT id_alquiler_documento, SUM(monto) AS monto_pagado 
                      FROM pago 
                      WHERE anulado = 'no' 
                      GROUP BY id_alquiler_documento) pagos", 
                      "pagos.id_alquiler_documento = a.id_alquiler_documento", "left");
    if($idSucursal>0)  $this->db->where('id_sucursal',$idSucursal);
    $this->db->where('contrato','1');
    $this->db->where_in('a.id_estado_alquiler',[1,2]);
    $this->db->limit($limit, $offset);
    return $this->db->get()->result();
  }
  public function getTotalAlquileres($idSucursal) {
    if($idSucursal>0)  $this->db->where('id_sucursal', $idSucursal);
    $this->db->where_in('id_estado_alquiler', [3,5]);
    $this->db->where('contrato','1');
    return $this->db->count_all_results('alquiler_documento');
  }
  public function getTotalAlquileresEntrega($idSucursal) {
    if($idSucursal>0) $this->db->where('id_sucursal', $idSucursal);
    $this->db->where_in('id_estado_alquiler',[1,2]);
    $this->db->where('contrato','1');
    return $this->db->count_all_results('alquiler_documento');
  }
}
