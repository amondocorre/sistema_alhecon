<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class UserController extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->model('auth/User_model');
        $this->load->model('auth/Usuario_model');
        $this->load->model('configurations/AccessMenuModel');
        $this->load->model('configurations/Company');
        $this->load->model('configurations/SucursalUsuarioModel');
    }
    public function index() {
      echo 'Hello from UserController!';
    } 
    public function create_user() {
      if (!validate_http_method($this, ['POST'])) {
        return;
      }
      $res = verifyTokenAccess();
      if(!$res){
        return;
      }
      $data = $this->input->post();
      $file = $_FILES['file']??null;
      //$data = json_decode(file_get_contents('php://input'), true);
      $id_usuario = $this->User_model->create($data);
      if ($id_usuario) {
          if($file){
            $url = guardarArchivo($id_usuario,$file,'assets/user/');
            if(!$url){
              $response = ['status' => 'success','message'=>'Ocurrió un error inesperado al intertar resetear la contraseña.'];
              return _send_json_response($this, 200, $response);
            }
            $this->User_model->updateFoto($url,$id_usuario);
          }
          if(!$this->User_model->addAccessUser($id_usuario,$data['id_perfil'])){
            //return;
          }
          if(!$this->User_model->addAccessBottons($id_usuario,$data['id_perfil'])){
            //return;
          }
          $response = ['status' => 'success','message'=>'Usuario creado con éxito.'];
          return _send_json_response($this, 200, $response);
      } else {
        $response = ['status' => 'error', 'message' =>  array_values($this->form_validation->error_array())];
        return _send_json_response($this, 400, $response);
      }
    }
    public function update_user($id) {
        if (!validate_http_method($this, ['POST'])) {
          return; 
        }
        $res = verifyTokenAccess();
        if(!$res){
          return;
        } 
        $data = $this->input->post();
        $file = $_FILES['file']??null;
        $id_perfil_ant = isset($data['id_perfil_ant'])?$data['id_perfil_ant']:null;
        unset($data['id_perfil_ant']);
        if ($this->User_model->update($id, $data)) {
            if($file){
              $url = guardarArchivo($id,$file,'assets/user/');
              if(!$url){
                $response = ['status' => 'success','message'=>'Ocurrió un error inesperado al intertar resetear la contraseña.'];
                return _send_json_response($this, 200, $response);
              }
              $this->User_model->updateFoto($url,$id);
            }
            if(!empty($id_perfil_ant) && $id_perfil_ant !== $data['id_perfil']){
              if(!$this->User_model->desactiveAccessUser($id,$id_perfil_ant)){
                //return;
              }
              if(!$this->User_model->desactiveAccessBottons($id,$id_perfil_ant)){
                //return;
              }
              if(!$this->User_model->addAccessUser($id,$data['id_perfil'])){
                //return;
              }
              if(!$this->User_model->addAccessBottons($id,$data['id_perfil'])){
                //return;
              }
            }
            $response = ['status' => 'success','message'=>'Usuario actualizado con éxito.'];
            return _send_json_response($this, 200, $response);
        } else {//echo validation_errors(); 
          $response = ['status' => 'error', 'message' =>  array_values($this->form_validation->error_array())];
          return _send_json_response($this, 400, $response);
        }
    }
    public function delete($id) {
      if (!validate_http_method($this, ['POST'])) {
        return; 
      }
      $res = verifyTokenAccess();
      if(!$res){
        return;
      } 
      if ($this->User_model->delete($id)) {
          $response = ['status' => 'success','message'=>'Usuario eliminado con éxito.'];
          return _send_json_response($this, 200, $response);
      } else {
        $response = ['status' => 'error', 'message' =>  array_values($this->form_validation->error_array())];
        return _send_json_response($this, 400, $response);
      }
    }
    public function activate($id) {
      if (!validate_http_method($this, ['PUT'])) {
        return; 
      }
      $res = verifyTokenAccess();
      if(!$res){
        return;
      } 
      if ($this->User_model->active($id)) {
          $response = ['status' => 'success','message'=>'Usuario activado con éxito.'];
          return _send_json_response($this, 200, $response);
      } else {
        $response = ['status' => 'error', 'message' =>  array_values($this->form_validation->error_array())];
        return _send_json_response($this, 400, $response);
      }
    }
    public function login() {
      $body = json_decode(file_get_contents('php://input'), true);
      $username = isset($body['username']) ? $body['username'] : null; // Corregido el nombre del campo
      $password = isset($body['password']) ? $body['password'] : null;
      if (!$username || !$password) {
          return _send_json_response($this, 400, ['message' => 'Username and password are required']);
      }
      $user = $this->User_model->findByUsername($username);
      if (!$user) {
          return _send_json_response($this, 401, ['message' => 'Incorrect username/password']);
      }
      if (!$user->estado) {
          return _send_json_response($this, 403, ['message' => 'Inactive account. Access denied']);
      }
      if (!password_verify($password, $user->password_hash)) {
          return _send_json_response($this, 401, ['message' => 'Incorrect username/password']);
      }
      unset($user->password_hash);
      $payload = ['user' => $user];
      $token = $this->jwthandler->encode($payload);
      $user->sucursales=$this->SucursalUsuarioModel->getSucursalesUser($user->id_usuario);
      $data = ['user' => $user, 'token' => $token];

      return _send_json_response($this, 200, $data);
    }
    public function changePassword() {
      if (!validate_http_method($this, ['POST'])) return; 
      $res = verifyTokenAccess();
      if(!$res) return;
      $body = json_decode(file_get_contents('php://input'), true);
      $username = isset($body['username']) ? $body['username'] : null;
      $password = isset($body['password']) ? $body['password'] : null;
      $newPassword = isset($body['newPassword']) ? $body['newPassword'] : null;
      $repeatNewPassword = isset($body['repeatNewPassword']) ? $body['repeatNewPassword'] : null;
      
      if (!$username || !$password) {
          return _send_json_response($this, 400, ['message' => 'Username, password and newPassword are required']);
      }
      if ($newPassword !== $repeatNewPassword) {
          return _send_json_response($this, 403, ['message' => 'The new passwords are not the same']);
      }
      $user = $this->User_model->findByUsername($username);
      if (!$user) {
          return _send_json_response($this, 401, ['message' => 'Incorrect username/password']);
      }
      if (!password_verify($password, $user->password_hash)) {
          return _send_json_response($this, 401, ['message' => 'Incorrect username/password']);
      }
      $id = $user->id_usuario;
      if ($this->User_model->changePassword($id,$newPassword)) {
          $response = ['status' => 'success','message'=>'Se cambio la contraseña correctamente.'];
          return _send_json_response($this, 200, $response);
      } else {
        $response = ['status' => 'error', 'message' => 'Ocurrió un error inesperado al intertar resetear la contraseña.'];
        return _send_json_response($this, 400, $response);
      }
    }
    public function resetPassword($id) {
      if (!validate_http_method($this, ['PUT'])) return; 
      $res = verifyTokenAccess();
      if(!$res) return;
  
      $user = $this->User_model->findIdentity($id);
      if (!$user) {
          return _send_json_response($this, 401, ['message' => 'No se encontro el usuario.']);
      }
      if ($this->User_model->resetPassword($id)) {
          $response = ['status' => 'success','message'=>'Se reseteo la contraseña correctamente.'];
          return _send_json_response($this, 200, $response);
      } else {
        $response = ['status' => 'error', 'message' => 'Ocurrió un error inesperado al intertar resetear la contraseña.'];
        return _send_json_response($this, 400, $response);
      }
    }
    public function logout(){
      $res = verifyTokenAccess();
      if(!$res){
        return;
      }
      $response = ['message' => 'success','data'=>$res];
      return _send_json_response($this, 200, $response);
    }
    public function getMenuAccess(){
      $res = verifyTokenAccess();
      if(!$res){
        return;
      }
      $user = $res->user;
      $idUser = $user->id_usuario;
      $access = $this->AccessMenuModel->findAllIdUser($idUser);
      $company = $this->Company->getDataId(1);
      $response = ['message' => 'success','menu'=>$access,'dataConpany'=>$company];
      return _send_json_response($this, 200, $response);
    }
    public function getAllUsers() {
      if (!validate_http_method($this, ['GET'])) return; 
      $res = verifyTokenAccess();
      if(!$res) return; 
      $usuarios = $this->User_model->getAllUsers();
      $data['usuarios'] = $usuarios;
      $response = ['status' => 'success','users'=>$usuarios];
      return _send_json_response($this, 200, $response);
    }
    public function findActive() {
      if (!validate_http_method($this, ['GET'])) return; 
      $res = verifyTokenAccess();
      if(!$res) return; 
      $usuarios = $this->User_model->findActive();
      $data['usuarios'] = $usuarios;
      $response = ['status' => 'success','users'=>$usuarios];
      return _send_json_response($this, 200, $response);
    }
    public function setStateUser($id) {
      if (!validate_http_method($this, ['POST'])) return; 
      $res = verifyTokenAccess();
      if(!$res) return; 
      $body = json_decode(file_get_contents('php://input'), true);
      $estado = $body['estado']??null;
      if ($estado === null || ($estado !== 'Activo' && $estado !== 'Inactivo')) {
        $response = ['status' => 'error', 'message' => 'El estado proporcionado no es válido. Debe ser "Activo" o "Inactivo".'];
        return _send_json_response($this, 400, $response); 
      }
      if (!$this->User_model->findById($id)) {
        $response = ['status' => 'error', 'message' => 'No se encontro el usuario.'];
        return _send_json_response($this, 400, $response); 
      }
      if ($this->User_model->setUserStatus($id, $estado)) {
          $response = ['status' => 'success', 'message' => 'Estado del usuario actualizado con éxito a ' . $estado . '.'];
          _send_json_response($this, 200, $response);
      } else {
          $response = ['status' => 'error', 'message' => 'No se pudo actualizar el estado del usuario.'];
          _send_json_response($this, 500, $response); 
      }
    }
    public function getButtonsAccesUser($id_acces) {
      if (!validate_http_method($this, ['GET'])) return; 
      $res = verifyTokenAccess();
      if(!$res) return; 
      $user = $res->user;
      $id_user = $user->id_usuario;
      $buttons = $this->User_model->getButtonsAccesUser($id_user,$id_acces);
      $response = ['status' => 'success','buttons'=>$buttons];
      return _send_json_response($this, 200, $response);
    }
}
