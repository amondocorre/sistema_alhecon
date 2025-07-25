<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model {
    protected $table = 'usuarios'; // Tabla asociada al modelo

    public function __construct() {
        parent::__construct();
        $this->load->library('form_validation'); // Cargar la librería de validación de formularios
    }

    // Encuentra un usuario por ID
    public function findIdentity($id) {
        $user = $this->db->get_where($this->table, ['id_usuario' => $id])->row();
        unset($user->password_hash);
        return $user;
    }

    // Encuentra un usuario por token de acceso (si lo usas)
    public function findIdentityByAccessToken($token) {
        $user = $this->db->get_where($this->table, ['access_token' => $token])->row();
        if ($user) {
            $user->access_token = null;
            return $user;
        }
        return null;
    }

    // Obtiene el ID del usuario (este método puede estar en un controlador en lugar de aquí)
    public function getId($user) {
        return $user->id_usuario ?? null;
    }
    // Valida contraseña
    public function validatePassword($password, $passwordHash) {
        return password_verify($password, $passwordHash);
    }
    // Encuentra un usuario por nombre de usuario
    public function findByUsername($username) {
      $url = getHttpHost();
      $this->db->select("id_usuario,usuarios.id_perfil,perfiles.nombre as perfil,usuarios.nombre,password_hash ,email,telefono,celular,usuarios.estado,usuario,CONCAT('$url', foto) as foto,sexo,fecha_nacimiento,direccion,ubicacion_gps");
      $this->db->join('perfiles', 'usuarios.id_perfil = perfiles.id', 'left'); 
      $this->db->where('usuario', $username);
      return $this->db->get($this->table)->row();
      //return $this->db->get_where($this->table, ['usuario' => $username])->row();
    }
    public function findById($id) {
      return $this->db->get_where($this->table, ['id_usuario' => $id])->row();
    }
    public function setUserStatus($id_usuario, $nuevo_estado) {
      $data = array('estado' => $nuevo_estado);
      $this->db->where('id_usuario', $id_usuario);
      $this->db->update($this->table, $data);
      return ($this->db->affected_rows() > 0) ; 
  }
    public function getAllUsers() {
      $url = getHttpHost();
      $campos = "id_usuario,usuarios.id_perfil,perfiles.nombre as perfil,usuarios.nombre,email,telefono,celular,usuarios.estado,fecha_ingreso,fecha_baja,sueldo,usuario,CONCAT('$url', foto) as foto,fecha_registro,ci,ext,complemento,sexo,fecha_nacimiento,direccion,ubicacion_gps";
      $this->db->select($campos);
      $this->db->join('perfiles', 'usuarios.id_perfil = perfiles.id', 'left'); 
      $query = $this->db->get($this->table);
      if ($query->num_rows() > 0) {
          return $query->result(); 
      } else {
          return array(); 
      }
    }
    public function findActive() {
      $url = getHttpHost();
      $campos = "id_usuario,usuarios.id_perfil,perfiles.nombre as perfil,usuarios.nombre,email,telefono,celular,usuarios.estado,ci,sexo";
      $this->db->select($campos);
      $this->db->join('perfiles', 'usuarios.id_perfil = perfiles.id', 'left'); 
      $this->db->where('usuarios.estado', 'Activo');
      $query = $this->db->get($this->table);
      if ($query->num_rows() > 0) {
          return $query->result(); 
      } else {
          return array(); 
      }
    }
    public function create($data) {
        $data['password_hash'] = 'password';
        if (!$this->validate_user_data($data)) {
            return FALSE; 
        }
        $data['password_hash'] = password_hash($data['password_hash'], PASSWORD_DEFAULT);
        unset($data['foto']);
      //unset($data['password_hash']);
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }
    public function update($id, $data) {
      if (!$this->validate_user_data($data, $id)) {
          return FALSE;
      }
      if(isset($data['password_hash'])){
        unset($data['password_hash']);
      }
      unset($data['foto']);
      $this->db->where('id_usuario', $id);
      return $this->db->update($this->table, $data);
    }
    public function delete($id) {
      $this->db->where('id_usuario', $id);
      return $this->db->update($this->table, ['estado'=>'Inactivo']);
    }
    public function active($id) {
      $this->db->where('id_usuario', $id);
      return $this->db->update($this->table, ['estado'=>'Activo']);
    }
    public function updateFoto($url,$id){
      $this->db->where('id_usuario', $id);
      return $this->db->update($this->table, ['foto'=>$url]);
    }
    public function findAccesUser($id_usuario,$id_acces) {
      $this->db->where('id_acceso', $id_acces);
      $this->db->where('id_usuario', $id_usuario);
      return $this->db->get_where('acceso_usuario')->row();
    }
    public function findBotonAccesUser($id_usuario,$id_acces,$id_boton) {
      $this->db->where('id_acceso', $id_acces);
      $this->db->where('id_usuario', $id_usuario);
      $this->db->where('id_boton', $id_boton);
      return $this->db->get_where('acceso_boton_usuario')->row();
    }
    public function addAccessUser($id_usuario,$id_perfil){
      $this->db->select("id_acceso, estado, $id_usuario as id_usuario");
      $this->db->from('acceso_perfil');
      $this->db->where('id_perfil', $id_perfil);
      $this->db->where('estado',1);
      $query = $this->db->get();
      if ($query->num_rows() > 0) {
          $accesos = $query->result_array();
          $stateAcces = false;
          foreach($accesos as $key=>$acceso){
            if($this->findAccesUser($id_usuario,($acceso['id_acceso'])) ){
              $this->db->where('id_acceso', $acceso['id_acceso']);
              $this->db->where('id_usuario', $id_usuario);
              $stateAcces = $this->db->update('acceso_usuario', 'estado',1);
            }else{
              $stateAcces = $this->db->insert('acceso_usuario', $acceso);
            }
          } 
          return $stateAcces;
      } else {
          return false; 
      }
    }
    public function addAccessBottons($id_usuario,$id_perfil){
      $this->db->select("id_acceso, id_boton, estado, $id_usuario as id_usuario");
      $this->db->from('acceso_boton_perfil');
      $this->db->where('id_perfil', $id_perfil);
      $this->db->where('estado',1);
      $query = $this->db->get();
      if ($query->num_rows() > 0) {
        $botones = $query->result_array();
        $stateAcces = false;
        foreach($botones as $key=>$boton){
          if($this->findBotonAccesUser($id_usuario,$boton['id_acceso'],$boton['id_boton']) ){
            $this->db->where('id_acceso', $boton['id_acceso']);
            $this->db->where('id_boton', $boton['id_boton']);
            $this->db->where('id_usuario', $id_usuario);
            $stateAcces = $this->db->update('acceso_boton_usuario', 'estado',1);
          }else{
            $stateAcces = $this->db->insert('acceso_boton_usuario', $boton);
          }
        } 
        return $stateAcces;
      } else {
          return false; 
      }
    }
    public function desactiveAccessUser($id_usuario,$id_perfil){
      $this->db->where('id_usuario', $id_usuario);
      $query = $this->db->update('acceso_usuario', ['estado'=>0]);
      if ($query) {//affets_rows
        return true;     
      } else {
          return false; 
      }
    }
    public function desactiveAccessBottons($id_usuario,$id_perfil){
      $this->db->where('id_usuario', $id_usuario);
      $query=$this->db->update('acceso_boton_usuario', ['estado'=>0]);
      if ($query) {
        return true;     
      } else {
          return false; 
      }
    }
    private function validate_user_data($data, $user_id = 0) {
      $this->form_validation->set_data($data);
      $this->form_validation->set_rules('id_perfil', 'Perfil', 'required|max_length[20]|perfil_existe');
      $this->form_validation->set_rules('nombre', 'Nombre', 'required|max_length[100]');
      $this->form_validation->set_rules('email', 'Email', 'required|valid_email|max_length[100]'.($user_id>0 ? '|email_unique_current['.$user_id.']' : '|is_unique[usuarios.email]'));
      $this->form_validation->set_rules('telefono', 'Teléfono', 'max_length[15]');
      $this->form_validation->set_rules('celular', 'Celular', 'max_length[15]');
      $this->form_validation->set_rules('estado', 'Estado', 'in_list[Activo,Inactivo]');
      //$this->form_validation->set_rules('fecha_ingreso', 'Fecha Ingreso', 'valid_date_format[Y-m-d]');
      //$this->form_validation->set_rules('fecha_baja', 'Fecha Baja', 'valid_date');
      //$this->form_validation->set_rules('sueldo', 'Sueldo', 'decimal');
      $this->form_validation->set_rules('usuario', 'Usuario', 'max_length[15]' . ($user_id>0 ? '|usuario_unique_current['.$user_id.']' : '|is_unique[usuarios.usuario]'));
      //$this->form_validation->set_rules('foto', 'Foto');
      //$this->form_validation->set_rules('password','Contraseña','min_length[8]|regex_match[/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+{}\[\]:;<>,.?~\\/-]).*$/]');

      return $this->form_validation->run();
    }
    public function getButtonsAccesUser($id_usuario,$id_acces){
      $this->db->select('btn.*'); 
      $this->db->from('botones  btn'); 
      $this->db->join('acceso_boton_usuario abu', 'abu.id_boton = btn.id_boton');
      $this->db->where('btn.estado', 1); 
      $this->db->where('abu.estado', 1); 
      $this->db->where('abu.id_acceso', $id_acces); 
      $this->db->where('abu.id_usuario', $id_usuario); 
      $query = $this->db->get();
      if ($query->num_rows() > 0) {
          return $query->result(); 
      } else {
          return array(); 
      }
    }
}
?>