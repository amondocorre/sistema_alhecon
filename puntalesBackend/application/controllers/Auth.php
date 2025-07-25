<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {
    private $secret_key = "Adolfo2025"; // Define una clave secreta segura

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->helper('jwt'); // Cargar la clase JWT
    }

    public function login() {
        $json = file_get_contents("php://input");
        $data = json_decode($json);

        if (!isset($data->usuario) || !isset($data->password)) {
            echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
            return;
        }

        // Verificar usuario en la base de datos
        $query = $this->db->get_where('usuarios', [
            'Usuario' => $data->usuario,
            'Password' => $data->password
            //'Password' => md5($data->password)
        ]);
        $user = $query->row();

        if ($user) {
            // Generar Token JWT
            $payload = [
                'id' => $user->UsuarioID,
                'usuario' => $user->Nombres,
                'exp' => time() + 3600 // Expiración en 1 hora
            ];
            $token = JWT::encode($payload, $this->secret_key, 'HS256');

            echo json_encode(['status' => 'success', 'token' => $token]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Credenciales incorrectas']);
        }
    }
}
