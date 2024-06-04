<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');       

class Catalog extends MY_Controller {

    function __construct() {
        parent::__construct();
        
        $this->load->library('form_validation');
    }

    function check_token() {
        if (empty($_SESSION["facebook_access_token"])) {
            $redirect = base_url() . "MY_Facebook/oauth_finish/?redirect=build-feed";
            $data['url'] = getFacebookLoginURL($redirect);
            redirect($data['url']);
        }
    }
    
    function build_feed($business_id = "") {
        $this->check_token();

        $data["businesses"] = getBusinesses($_SESSION["facebook_access_token"]);
        if (!empty($data["businesses"])) {
            if ($business_id == "" && !empty($data["businesses"])) {
                $shop = $this->Trackify_DB->get_user_settings($_SESSION["shop"]);
                $business_id = $shop["fb_business"];
            }            
        } else {
            //$_SESSION["message_display"] .= "You have no catalogs created.";
        }
        
        $data["business_id"] = $business_id;

        $data["channels"] = $this->Trackify_DB->get_channels($_SESSION["shop"]);
        if (empty($data["channels"])) {
            data_map();
            $data["channels"] = $this->Trackify_DB->get_channels($_SESSION["shop"]);
        }
        $this->load->template('build_feed', $data);
    }
    
    function edit_feed($id) {
        $categories_map = $this->Trackify_DB->get_channels_categories($id);
        $data["channel"] = $id;
        $data["categories"] = $categories_map;
        $data["id"] = $id;
        $data["default_google_prod_cat"] = $this->Trackify_DB->get_default_google_prod_cat($id);
        $this->load->template('edit_feed', $data);    
    }
    
    function save_feed($channel_id) {
        if (isset($_POST["save"])) {
            $data = array("ids" => $_POST["ids"], "conditions" => $_POST["conditions"], "categories" => $_POST["categories"],"default_google_product_cat" => $_POST["default_google_product_cat"]);
            $this->Trackify_DB->save_feed($channel_id, $data);
        }
        
        redirect("edit-feed/$channel_id");
    }

    function update_store_domain() {
        if (isset($_POST["default_store_name"])) {
            $store = $_SESSION["shop"];
            if(!empty($_POST['default_store_name']) || !empty($store)){
                $default_store_name = strtolower($_POST['default_store_name']);
                $default_domain_protocol = strtolower($_POST['default_domain_protocol']);

                if (false === strpos($default_store_name, '://')) {
                        $default_store_name = $default_domain_protocol.$default_store_name;
                } else {
                    $default_store_name = preg_replace("(^https?://)", $default_domain_protocol, $default_store_name );
                }

                $result = $this->Trackify_DB->update_store_domain($store, $default_store_name);
                if($result){
                     $array_ret = array('msg' => $result, 'default_store_name'=> $default_store_name);
                     echo json_encode($array_ret);
                }
            }
        }
        die();
    }

    function search_cat($term) {
        if (isset($term)) {
            $result = $this->Trackify_DB->search_google_prod_taxonomy($term);
            echo json_encode($result);
        }
        die();
    }
    
    function check_category_map($channel_id) {
        $status = get_feed_status($channel_id);
        echo json_encode($status);
        die();
    }

    /* Reset Default categories and build Feed again*/
    function re_set_categories($channel_id) {
        $iret = $this->Trackify_DB->reset_feed_categories($channel_id);
        store_defaulte_google_cat($channel_id);
        data_map($channel_id);
        echo json_encode(array("result" => 1));
        die();  
    }

    function datamap($channel_id) {  
        data_map($channel_id);        
        echo json_encode(array("result" => 1));
        die();        
    }
    
}

?>
