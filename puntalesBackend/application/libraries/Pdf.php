<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// Incluye el archivo principal de TCPDF desde la carpeta third_party
require_once APPPATH . '/third_party/tcpdf/tcpdf.php';

class Pdf extends TCPDF {
    public function __construct() {
        parent::__construct();
    }
}