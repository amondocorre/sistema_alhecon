<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class CalendarModel extends CI_Model {
    protected $table = 'calendario'; 
    protected $dias = [1 => 'Lunes',2 => 'Martes',3 => 'Miércoles',4 => 'Jueves',5 => 'Viernes',6 => 'Sábado',7 => 'Domingo'];
    protected $meses = ['01' => 'Enero','02' => 'Febrero','03' => 'Marzo','04' => 'Abril','05' => 'Mayo','06' => 'Junio','07' => 'Julio','08' => 'Agosto','09' => 'Septiembre','10' => 'Octubre','11' => 'Noviembre','12' => 'Diciembre'];
    protected $holiday_translations = [
    'New Year\'s Day' => 'Año Nuevo',
    'Epiphany' => 'Epifanía',
    'Plurinational State Foundation Day' => 'Día de la Fundación del Estado Plurinacional',
    'Feast of Candelaria' => 'Fiesta de la Candelaria',
    'Carnival / Shrove Monday' => 'Carnaval / Lunes de Carnaval',
    'Carnival / Shrove Tuesday / Pancake Day' => 'Carnaval / Martes de Carnaval / Día del Panqueque',
    'Father Day' => 'Día del Padre',
    'March Equinox' => 'Equinoccio de marzo',
    'Day of the Sea' => 'Día del Mar',
    'Children\'s Day' => 'Día del Niño',
    'Maundy Thursday' => 'Jueves Santo',
    'Good Friday' => 'Viernes Santo',
    'Labor Day / May Day' => 'Día del Trabajo / Día de Mayo',
    'Mother\'s Day' => 'Día de la Madre',
    'Corpus Christi' => 'Corpus Christi',
    'Aymara New Year Day' => 'Año Nuevo Aymara',
    'June Solstice' => 'Solsticio de junio',
    'Independence Day' => 'Día de la Independencia',
    'Flag Day' => 'Día de la Bandera',
    'September Equinox' => 'Equinoccio de septiembre',
    'Bolivian Women\'s Day' => 'Día de la Mujer Boliviana',
    'Day of Decolonization' => 'Día de la Descolonización',
    'Day of Dignity' => 'Día de la Dignidad',
    'All Saints Day' => 'Día de Todos los Santos',
    'December Solstice' => 'Solsticio de diciembre',
    'Christmas Day' => 'Navidad',
    'National holiday' => 'Feriado nacional',
    'Observance' => 'Conmemoración',
    'Season' => 'Estación'
  ];

  public function __construct() {
      parent::__construct();
  }
  public function findIdentity($id) {
      return $this->db->get_where($this->table, ['fecha' => $id])->row();
  }
  public function updateDate($fecha,$es_feriado,$es_laboral,$nombre_feriado){
    $newData['es_feriado'] = $es_feriado; 
    $newData['es_laboral'] = $es_laboral; 
    $newData['nombre_feriado'] = $nombre_feriado; 
    $res = $this->db->update('calendario',$newData,['fecha'=>$fecha]);
    return $res;// $this->db->affected_rows(); 
  }
  function obtenerFeriados($año) {
    $apiKey = 'LEobTgsDC3DV9UH9HwK4SMzX4yN4ewye';
    $idioma = 'es'; // Español
    $url = "https://calendarific.com/api/v2/holidays?api_key=$apiKey&country=BO&year=$año&language=$idioma";
    $response = @file_get_contents($url);
    if (!$response) return [];
    $data = json_decode($response, true);
    $feriados = [];
    if (!empty($data['response']['holidays'])) {
        foreach ($data['response']['holidays'] as $feriado) {
            $tipo = $feriado['type'][0]??'';
            $nombre = $feriado['name'];
            $feriados[$feriado['date']['iso']] = [
                //'fecha' => $feriado['date']['iso'],
                'nombre' => $this->holiday_translations[$nombre]??$nombre,
                'tipo' => $this->holiday_translations[$tipo]??$tipo
            ];
        }
    }
    return $feriados;
  }
  public function poblarCalendarioPorAño($año) {
    $feriados = $this->obtenerFeriados($año);
    $inicio = new DateTime("$año-01-01");
    $fin = new DateTime("$año-12-31");
    $datos =[];
    while ($inicio <= $fin) {
       $datos[]=$this->contruirFecha($inicio,$feriados);
      $inicio->modify('+1 day');
    }
    if(count($datos)>0){
      return  $this->db->insert_batch($this->table, $datos);
    }
    return true;
  }
  function poblarCalendarioPorMes($año, $mes) {
    $feriados = $this->obtenerFeriados($año);
    $inicio = new DateTime("$año-$mes-01");
    $fin = clone $inicio;
    $fin->modify('last day of this month');
    $datos =[];
    while ($inicio <= $fin) {
        $datos[] = $this->contruirFecha($inicio,$feriados);
        $inicio->modify('+1 day');
    }
    
    if(count($datos)>0){
      return  $this->db->insert_batch($this->table, $datos);
    }
    return false;
  }
  function contruirFecha($fechaObj, $feriados) {
    $fecha = $fechaObj->format('Y-m-d');
    $dia = $fechaObj->format('N'); // 1 = lunes, 7 = domingo
    $es_fin_de_semana = ($dia >= 6) ? '1' : '0';
    $dia_nombre = $this->dias[$dia];// $fechaObj->format('l');
    $es_feriado = isset($feriados[$fecha])?'1':'0';
    $nombre_feriado = $feriados[$fecha]['nombre']??null;
    $tipo_feriado = $feriados[$fecha]['tipo']??null;
    return [
        'fecha' => $fecha,
        'es_laboral' => ($es_fin_de_semana === '1' || $es_feriado === '1') ? '0' : '1',
        'es_feriado' => $es_feriado,
        'nombre_feriado' => $nombre_feriado,
        'tipo_feriado' => $tipo_feriado,
        'dia_semana' => $dia_nombre,
        'es_fin_de_semana' => $es_fin_de_semana
    ];
  }
  public function obtenerLaborales($cantidadMeses){
    $date = new DateTime(date('Y-m-d'));
    $numMes = $date->format('m');
    $date->modify('+'.($cantidadMeses). ' month');
    $fechaFin = $date->format('Y-m-d');
    $fechas = $this->db
        ->select('*')
        ->from('calendario')
        ->where('fecha >=', date('Y-m-d'))
        ->where('es_laboral', 1)
        ->where('fecha <=', $fechaFin)
        ->order_by('fecha', 'ASC')
        ->get()
        ->result();
    $res = array();
    foreach($fechas as $key=>$fecha){
      array_push($res,$fecha->fecha);
    }
    return $res;
  }
  public function obtenerCalendario($cantidadMeses){
    $date = new DateTime(date('Y-m-d'));
    $numMes = $date->format('m');
    $date->modify('+'.($cantidadMeses). ' month');
    $fechaFin = $date->format('Y-m-d');
    $fechas = $this->db
        ->select('fecha,es_laboral,es_feriado')
        ->from('calendario')
        ->where('fecha >=', date('Y-m-d'))
        ->where('fecha <=', $fechaFin)
        ->order_by('fecha', 'ASC')
        ->get()
        ->result();
    return $fechas?$fechas:[];
  }
  public function getCalendarioByAnio($año){
    $meses = [];
    for ($mes=1; $mes < 13; $mes++) { 
      $meses[] = $this->obtenerCalendarioMensual($año, $mes);
    }
    return $meses;
  }
  function obtenerCalendarioMensual($año, $mes) {
    $inicio = new DateTime("$año-$mes-01");
    $fin = new DateTime("$año-$mes-" . date('t', strtotime($inicio->format('Y-m-d'))));
    $diaSemana = (int) $inicio->format('N');
    $diaSemanaFin = (int) $fin->format('N');
    if ($diaSemana > 1) $inicio->modify('last monday');// $inicio->modify('-'.($diaSemana-1). ' days');
    if ($diaSemanaFin < 7) $fin->modify('+'.(7-$diaSemanaFin). ' days');
    $inicioMes = $inicio->format('Y-m-d');
    $finMes = $fin->format('Y-m-d');// date("Y-m-t", strtotime($inicioMes));
    $datos = $this->db
        ->select('*')
        ->from('calendario')
        ->where('fecha >=', $inicioMes)
        ->where('fecha <=', $finMes)
        ->order_by('fecha', 'ASC')
        ->get()
        ->result_array();

    $mapaFechas = [];
    foreach ($datos as $dia) {
        $mapaFechas[$dia['fecha']] = $dia;
    }
    $estructura = [
        'mes' => $this->meses[str_pad($mes, 2, '0', STR_PAD_LEFT)],
        'año' => $año,
        'semanas' => []
    ];
    //$inicio->modify('last monday');
    while ($inicio <= $fin) {
        $semana = [];
        for ($i = 0; $i < 7; $i++) {
            $fechaStr = $inicio->format('Y-m-d');
            $dia = $mapaFechas[$fechaStr] ?? [];/*[
                'fecha' => $fechaStr,
                'es_laboral' => '0',
                'es_feriado' => '0',
                'nombre_feriado' => null,
                'dia_semana' => $inicio->format('l'),
                'es_fin_de_semana' => in_array($inicio->format('N'), [6, 7]) ? '1' : '0',
                'tipo_feriado' => null
            ];*/
            $dia['dia_mes'] = ((int)$inicio->format("m"))==((int)$mes)?1:0; 
            $semana[]=$dia;
            $inicio->modify('+1 day');
        }
        $estructura['semanas'][] = $semana;
    }

    return $estructura;
  }
}