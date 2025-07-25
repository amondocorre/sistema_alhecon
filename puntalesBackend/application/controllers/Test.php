<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Test extends CI_Controller{
    public function testApi() {
        echo json_encode(array('status' => 'success'));
    }
}