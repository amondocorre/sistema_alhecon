<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Usuario_model extends CI_Model {
    protected $table = 'usuario'; // Tabla asociada al modelo
    public function __construct() {
        parent::__construct();
    }
    // Método para insertar un usuario
    public function create($data) {
        return $this->db->insert($this->table, $data);
    }
    // Método para obtener un usuario por ID
    public function findById($id) {
        return $this->db->get_where($this->table, ['id' => $id])->row();
    }
    // Método para actualizar un usuario por ID
    public function update($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update($this->table, $data);
    }
    // Método para obtener todas las filas de la tabla
    public function findAll() {
        return $this->db->get($this->table)->result();
    }
    // Ejemplo de validación (puedes implementarlo en el controlador)
    public function validate($data) {
        $errors = [];
        if (empty($data['username'])) {
            $errors['username'] = 'Username is required';
        }
        if (empty($data['password_hash'])) {
            $errors['password_hash'] = 'Password Hash is required';
        }
        return $errors;
    }
}
