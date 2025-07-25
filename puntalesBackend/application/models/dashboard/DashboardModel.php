<?php
class DashboardModel extends CI_Model {
  protected $ie = 'ingreso_salida'; 

  public function fetch_arrivals_departures() {
    return $this->db->query("
      select sexo, count(sexo) as cantidad from cliente group by sexo
      
      
    ")->result();
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
        SUM(CASE WHEN sexo = 'M' THEN 1 ELSE 0 END) AS masculino,
        SUM(CASE WHEN sexo = 'F' THEN 1 ELSE 0 END) AS femenino
        FROM cliente;
    ");
    return $query->row(); // Devuelve un solo objeto con ->total
  }
  
  
  // Se obtiene el total de mascotas en guarderia existen en este momento
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

  public function get_ingresos_diarios(){
    $this->db->select("
    DATE(fecha_movimiento) AS dia,
    SUM(CASE WHEN tipo = 'ingreso' THEN monto ELSE 0 END) AS total_ingresos,
    SUM(CASE WHEN tipo = 'egreso' THEN monto ELSE 0 END) AS total_egresos
    ");
    $this->db->from('movimientos_caja');
    $this->db->where('fecha_movimiento >=', date('Y-m-d', strtotime('-30 days')));
    $this->db->group_by('DATE(fecha_movimiento)');
    $this->db->order_by('DATE(fecha_movimiento)', 'ASC');

    $query = $this->db->get();

    if ($query->num_rows() > 0) {
        return $query->result(); // Devuelve un array de objetos por día
    } else {
        return [];
    }


  }
}
