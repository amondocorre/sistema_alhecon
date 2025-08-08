<?php
defined('BASEPATH') OR exit('No direct script access allowed');

function enable_cors() {
    // Permitir solicitudes de cualquier origen
    header("Access-Control-Allow-Origin: *");
    // Permitir credenciales como cookies o encabezados HTTP personalizados
    header("Access-Control-Allow-Credentials: true");
    // Métodos HTTP permitidos
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    // Encabezados permitidos en las solicitudes
    header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding, Authorization");
    // Si la solicitud es de tipo OPTIONS (preflight), responder sin continuar con la aplicación
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        header("HTTP/1.1 200 OK");
        exit();
    }
}
