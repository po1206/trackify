<?php

use phpish\shopify;

class MY_Loader extends CI_Loader {
    public function template($template_name, $vars = array(), $return = FALSE)
    {
        $common_vars['current_template'] = $template_name;
        
        if (isset($_SESSION['oauth_token'])) {
            try {
                $shopify = shopify\client($_SESSION['shop'], SHOPIFY_APP_API_KEY, $_SESSION['oauth_token']);            
                if ($shop = $shopify('GET /admin/shop.json', array('has_storefront' => 'true'))) {            
                    $common_vars['shop'] = $shop;
                }

                global $db;

                $query = $db->query("SELECT * FROM tbl_usersettings WHERE store_name='{$_SESSION['shop']}'");
                $common_vars["settings"] = $query->result_array()[0];
        
            } catch (Exception $e) {
                session_destroy();
                redirect("install/");
            }
        }
        
        $common_vars['token'] = md5(uniqid(rand(), TRUE));
        $common_vars['token_time'] = time();
        
        

        if (isset($vars["body_class"])) {
            $common_vars["body_class"] = $vars["body_class"];
        } else {
            $common_vars["body_class"] = "";
        }
        
        if ($return) {
            $content  = $this->view('header', $common_vars, $return);
            $content .= $this->view('sidebar', $common_vars, $return);
            $content .= $this->view($template_name, $vars, $return);
            $content .= $this->view('footer', $common_vars, $return);

            return $content;
        } else {
            $this->view('header', $common_vars);
            if (strpos($common_vars["body_class"], "full-width") === false) {
                $this->view('sidebar', $common_vars);
            }
            $this->view($template_name, $vars);
            $this->view('footer', $common_vars);
        }
    }
}