<?php
class DashboardModel extends CI_Model {
  protected $ie = 'ingreso_salida'; 
  public function __construct() {
      parent::__construct();
      $this->load->model('InventoryModel');
  }
  public function fetch_arrivals_departures() {
    return $this->db->query("
      select sexo, count(sexo) as cantidad from cliente group by sexo")->result();
  }
  public function fetch_occupation() {
    return $this->db->query("
      SELECT count(nombre) from mascota
    ")->result();
  }
  //se obtienen el total de clientes y cuantos varones y mujeres hay
  public function get_total_clientes() {
    $query = $this->db->query("
        SELECT 
        COUNT(*) AS total,
        0 AS masculino,
        0 AS femenino
        FROM cliente;
    ");
    return $query->row(); // Devuelve un solo objeto con ->total
  }
  // Se obtiene el total de mascotas en guarderia existen en este momento
  /*
  public function get_mascotas_estancia() {
    
    $this->db->select("count(estado) as total");
    $this->db->from($this->ie); 
    $this->db->where('estado', 'En estancia'); 
    $query = $this->db->get();

    if ($query->num_rows() > 0) {
        return $query->row(); // Esto devuelve un objeto: { total: 25 }
    } else {
        return (object) ['total' => 0]; // Devuelve un objeto con total = 0
    }
  }
  */
  public function get_ingresos_diarios($id_sucursal){
   // Subconsulta 1: pagos
    $this->db->select("DATE(fecha_pago) AS dia, monto, 'ingreso' AS tipo");
    $this->db->from('pago');
    $this->db->where('id_sucursal', 1);
    $this->db->where('anulado', 'no');
    $this->db->where('fecha_pago >=', date('Y-m-d', strtotime('-30 days')));
    $sql1 = $this->db->get_compiled_select();
    // Subconsulta 2: movimientos
    $this->db->select("DATE(fecha_movimiento) AS dia, monto, tipo");
    $this->db->from('movimientos_caja');
    $this->db->where('id_sucursal', 1);
    $this->db->where('fecha_movimiento >=', date('Y-m-d', strtotime('-30 days')));
    $sql2 = $this->db->get_compiled_select();
    $union_sql = "($sql1) UNION ALL ($sql2)";
    $final_sql = " SELECT  dia,
        SUM(CASE WHEN tipo = 'ingreso' THEN monto ELSE 0 END) AS ingresos,
        SUM(CASE WHEN tipo = 'egreso' THEN monto ELSE 0 END) AS egresos
      FROM ($union_sql) AS movimientos GROUP BY dia ORDER BY dia ASC";
    $query = $this->db->query($final_sql);
    $resultado = $query->result();
    if ($resultado) {
        return $query->result(); // Devuelve un array de objetos por día
    } else {
        return [];
    }
  }
  function construirProducto($producto,$inventorio,$totalInventorio){
    if (!empty($producto->es_combo) && $producto->es_combo == '1' && !empty($producto->productos)) {
        $minStock = INF;
        $minTotal = INF;
        foreach ($producto->productos as $i => $p) {
          $id = $p->id_producto;            
          $available = isset($inventorio[$id])?$inventorio[$id]:0;
          $availableTotal = isset($totalInventorio[$id])?$totalInventorio[$id]:0;
          $required = $p->cantidad?? 1;
          $p->stock = $available;
          $p->total = $availableTotal;
          $p->en_uso = $availableTotal - $available;
          $restante = floor(($available) / $required);
          $restanteTotal = floor(($availableTotal) / $required);
          $minStock = (int)min($minStock, $restante);
          $minTotal = (int)min($minTotal, $restanteTotal);
        }
        $stock= ($minStock === INF) ? 0 : $minStock;
        $stockTotal= ($minTotal === INF) ? 0 : $minTotal;
        $producto->stock = $stock;
        $producto->total = $stockTotal;
        $producto->en_uso = $stockTotal-$stock;
        return $producto;
    }
    $id = $producto->id_producto;
    $stock = (int)isset($inventorio[$id])?$inventorio[$id]:0;
    $total = (int)isset($totalInventorio[$id])?$totalInventorio[$id]:0;
    $producto->stock = $stock;
    $producto->total = $total;
    $producto->en_uso = $total-$stock;
    return $producto;
  }
  public function getTotalesInventario($id_sucursal){
    $inventorio = $this->InventoryModel->getStock($id_sucursal);
    $totalInventorio = $this->InventoryModel->getTotalStock($id_sucursal);
    $productos = $this->InventoryModel->getProductos();
    foreach($productos as $key=>$producto){
      $producto = $this->construirProducto($producto,$inventorio,$totalInventorio);
    }
    //usort($productos, function($a, $b) {return  $a->stock<=>$b->stock;})
    return $productos;
  }
}
