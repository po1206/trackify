<?php

require_once (APPPATH . 'core/functions.php');

use Facebook\Facebook;
use phpish\shopify;

class MY_Controller extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model('Trackify_DB');
        $this->load->library('session');
        
        global $db, $sc;
        $db = DB();

        $_SESSION["fb"] = new Facebook([
            'app_id' => FB_APP_ID,
            'app_secret' => FB_APP_SECRET,
        ]);
        
        if (isset($_SESSION['shop']) && !empty($_SESSION['shop']) && isset($_SESSION['oauth_token']) && !empty($_SESSION['oauth_token'])) {
            
            if (!$this->Trackify_DB->is_store_registered($_SESSION['shop'])) {
                redirect("install?p=1");
            } 
            try {
                $sc = shopify\client($_SESSION['shop'], SHOPIFY_APP_API_KEY, $_SESSION['oauth_token']);
            } catch (Exception $e) {
                handle_exception($e);
            }
        } else {
            redirect("install?p=2");
        }
    }
}