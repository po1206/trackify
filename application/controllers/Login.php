<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once (APPPATH . 'core/facebook.php');

class Login extends CI_Controller {

    public function __construct() {
        parent::__construct();
                
        $this->load->helper('form');
        
        $this->load->library('form_validation');        
        
        $this->load->model('User');        
    }

    // Show login page
    public function index() {        
        $redirect = base_url() . 'oauth_finish';
        $data['url'] = getFacebookLogin($redirect);
        $data['message'] = $this->input->get("message");
        $this->load->template('login', $data);
    }
    
    public function logout() {        
        
        $this->session->sess_destroy();
        
        $message = 'Successfully Logout';
        
        $redirect = base_url() . 'oauth_finish';
        $data['url'] = getFacebookLogin($redirect);
        
        redirect('login?message=' . $message);
    }
}

?>