<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class CalendarModel extends CI_Model {
    protected $table = 'calendario'; 
    protected $dias = [1 => 'Lunes',2 => 'Martes',3 => 'Miércoles',4 => 'Jueves',5 => 'Viernes',6 => 'Sábado',7 => 'Domingo'];
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
    return $datos;
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
}