<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

use phpish\shopify;

class Track extends MY_Controller {
    
    function __construct() {
        parent::__construct();
        
        $this->load->library('form_validation');
        $this->load->model('Shopify');
    }
    
    function index() {
        $store = $_SESSION["shop"];
        if ($this->Trackify_DB->is_store_registered($store)) {
            $data["settings"] = $this->Trackify_DB->get_settings($store);
            if ($data["settings"]["ca"] == "" && $data["settings"]["ca_2nd"] == "") {
                redirect("settings?redirect");
            }
            $data["tcodes"] = $this->Trackify_DB->get_tcode($store);
        }
        
        $this->load->template('track_' . $data["settings"]["track_with"], $data);    
    }
    
    function add_tcode($track_with = "conversion") {
        $this->form_validation->set_rules('tag', 'Tag', 'trim|required|xss_clean');
        
        if ($track_with == "conversion") {
            $this->form_validation->set_rules('tcode0', 'Tracking code for product page', 'trim|xss_clean');
            $this->form_validation->set_rules('tcode1', 'Tracking code for cart', 'trim|xss_clean');
            $this->form_validation->set_rules('tcode2', 'Tracking code for checkout', 'trim|xss_clean');
        } else {
            $this->form_validation->set_rules('pixel_name', 'Facebook Pixel Name', 'trim|xss_clean');
            $this->form_validation->set_rules('pixel_id', 'Facebook Pixel ID', 'trim|xss_clean');
        }
                
        if ($this->form_validation->run() == TRUE) {
            $tag = str_replace(" ", "_", $this->input->post('tag'));
            if ($track_with == "conversion") {
                $data = array(
                    'store'     => $_SESSION['shop'],
                    'tags'      => $tag,
                    'code0'     => $this->input->post('tcode0'),                
                    'code1'     => $this->input->post('tcode1'),
                    'code2'     => $this->input->post('tcode2'),
                    'mode'      => ''
                );
            } else {
                $data = array(
                    'store'         => $_SESSION['shop'],
                    'tags'          => $tag,
                    'pixel_name'    => $this->input->post('pixel_name'),                
                    'pixel_id'      => $this->input->post('pixel_id'),                    
                    'mode'          => ''
                );
            }
            $result = $this->Trackify_DB->add_tcode($data);
            if ($result == 1) {
                $_SESSION['message_display'] = 'New Tag Created!';
            } else if ($result == 2){
                $_SESSION['message_display'] = 'Duplicate Entry Not Allowed!';                
            } else {
                $_SESSION['message_display'] = 'Error!';
            }
        }
        
        redirect("track/");
    }
    
    function update_tcode($id) {        
        $this->form_validation->set_rules('tag', 'Tag', 'trim|required|xss_clean');
        $track_with = $_GET["track_with"];
        
        if ($track_with == "conversion") {
            $this->form_validation->set_rules('tcode0', 'Tracking code for product page', 'trim|xss_clean');
            $this->form_validation->set_rules('tcode1', 'Tracking code for cart', 'trim|xss_clean');
            $this->form_validation->set_rules('tcode2', 'Tracking code for checkout', 'trim|xss_clean');
        } else {
            $this->form_validation->set_rules('pixel_name', 'Facebook Pixel Name', 'trim|xss_clean');
            $this->form_validation->set_rules('pixel_id', 'Facebook Pixel ID', 'trim|xss_clean');
        }
                
        if ($this->form_validation->run() == TRUE) {
            if ($track_with == "conversion") {
                $data = array(
                    'tags'      => $this->input->post('tag'),
                    'code0'     => $this->input->post('tcode0'),                
                    'code1'     => $this->input->post('tcode1'),
                    'code2'     => $this->input->post('tcode2'),
                );
            } else {
                $data = array(
                    'tags'          => $this->input->post('tag'),
                    'pixel_name'    => $this->input->post('pixel_name'),                
                    'pixel_id'      => $this->input->post('pixel_id'),                    
                );
            }
            
            $result = $this->Trackify_DB->update_tcode($data, array("id" => $id));
            if ($result == 1) {
                $_SESSION['message_display'] = 'Record updated successfully!';
            } else if ($result == 2){
                $_SESSION['message_display'] = 'Duplicate Entry Not Allowed!';                
            } else {
                $_SESSION['message_display'] = 'Error!';
            }
            
            if ($_POST["old_tag"] != $_POST["tag"]) {
                $old_tag = $_POST["old_tag"];
                $tag = $_POST["tag"];
                
                global $sc;
                $products = get_products();                
                foreach ($products as $product) {                                
                    if (strpos($product['tags'], $old_tag) !== FALSE) {                        
                        $new_tag = str_replace($old_tag, $tag, $product["tags"]);
                        $result = $sc("PUT /admin/products/{$product['id']}.json", array(), array('product' => array('id' => $product['id'], 'tags' => $new_tag)));
                    }
                }    
            }
        }
        
        redirect("manage/" . $this->input->post('tag'));
    }
    
    function delete_tcode($id) {
        $result = $this->Trackify_DB->delete_tcode($id);
        if ($result == true) {
            $_SESSION['message_display'] = 'Record deleted successfully!';
        } else {
            $_SESSION['message_display'] = 'Error!';
        }
        
        redirect("track/");
    }
    
    function add_tag($tag) {
        global $sc;
        $pid = $_GET["pid"];
        if (!empty($pid)) {
            $product = get_product($pid);
            if (strpos($product['tags'], $tag) === false) {
                try {            
                    $new_tag = $product['tags'] . ", " . $tag;
                    $result = $sc("PUT /admin/products/$pid.json", array(), array('product' => array('id' => $pid, 'tags' => $new_tag)));
                } catch (Exception $e) {
                    handle_exception($e);
                }
            }
        }
    }
    
    function remove_tag($tag) {
        global $sc;
        $pid = $_GET["pid"];
        if (!empty($pid)) {
            $product = get_product($pid);
            if (strpos($product['tags'], $tag) !== false) {
                try {            
                    $new_tag = str_replace($tag, "", $product['tags']);
                    $result = $sc("PUT /admin/products/$pid.json", array(), array('product' => array('id' => $pid, 'tags' => $new_tag)));
                } catch (Exception $e) {
                    handle_exception($e);
                }
            }
        }
        redirect("manage/{$tag}");
    }
    
    function manage($tag) {
        if (isset($_GET['pid'])) {
            if (isset($_GET['add'])) { // add tag to product
                $this->add_tag($tag);    
            } else if (isset($_GET['remove'])) {
                $this->remove_tag($tag);    
            }
        }
        
        $data["settings"] = $this->Trackify_DB->get_settings($_SESSION['shop']);
        $data["tcode"] = $this->Trackify_DB->get_tcode_by_tag($tag, $_SESSION['shop'])[0];
        $data["products"] = get_products();
        
        $tagged_products = array();
        $warning_products = array();
        foreach ($data["products"] as $product) {            
            if (strpos($product['tags'], $tag) !== false) {
                $tagged_products[] = $product;
                if (substr_count($product['tags'], "rr_track") > 1) {
                    $warning_products[] = $product;
                }
            }
        }
        
        $data["tagged_products"] = $tagged_products;
        $data["warning_products"] = $warning_products;
        $data["tag"] = $tag;
        
        $this->load->template('manage_' . $data["settings"]["track_with"], $data);
    }

    function welcome() {
        $settings = $this->Trackify_DB->get_settings($_SESSION['shop']);
        if ($settings["ca"] != "" || $settings["ca_2nd"] != "") {
            redirect("track");
        } else {
            $data["body_class"] = "full-width";
            $this->load->template("welcome", $data);
        }
    }
    
    function settings() {
        if (empty($_SESSION["facebook_access_token"])) {
            $redirect = base_url() . "MY_Facebook/oauth_finish/?redirect=settings";
            $data['url'] = getFacebookLoginURL($redirect);
            redirect($data['url']);
        }
        
        $submit = $this->input->post('submit');
        if (isset($submit)) {
            if (isset($_POST["fire"]) && $_POST["fire"] == "1") $fire = 1;
            else $fire = 0;
            if (isset($_POST['business_account'])) $business_account = $_POST['business_account'];
            else $business_account = "";

            $data["settings"] = array(
                "fb_business"    => $business_account,
                "ad_account"     => $_POST['ad_account'],
                "ad_account2"    => $_POST['ad_account2'],
                "global"         => htmlspecialchars($_POST['global']),
                "ca"             => htmlspecialchars($_POST['ca']),
                "ca_2nd"         => htmlspecialchars($_POST['ca_2nd']),
                "pes"            => htmlspecialchars($_POST['pes']),
                "kpv"            => htmlspecialchars($_POST['kpv']),
                "atc"            => htmlspecialchars($_POST['atc']),
                "pvtc"           => htmlspecialchars($_POST['pvtc']),
                "ajax"           => htmlspecialchars($_POST['ajax']),
                "conv_value"     => htmlspecialchars($_POST['conv_value']),
                "fb_value"       => htmlspecialchars($_POST['fb_value']),
                "fire"           => $fire,
                "vc_delay"       => intval($_POST['vc_delay']),
                "pinterest_pixel" => $_POST["pinterest_pixel"]
            );

            $result = $this->Trackify_DB->update_settings($data["settings"], array("store_name" => $_SESSION["shop"]));
            $this->Shopify->update_store($data["settings"]);
            
            $data["show_modal"] = 1;
        }

        $data["settings"] = $this->Trackify_DB->get_settings($_SESSION["shop"]);

        $data["businesses"] = getBusinesses($_SESSION["facebook_access_token"]);
        $data["ad_accounts"] = getMyAdAccountsWithPixels($_SESSION["FB_USER"]["id"], $_SESSION["facebook_access_token"]);
        if ($data["settings"]["fb_business"] != "") {
            $data["business_ad_accounts"] = $data["ad_accounts"]; //getAdAccountsByBusiness($data["settings"]["fb_business"], $_SESSION["facebook_access_token"]);
        } else {
            $data["business_ad_accounts"] = $data["ad_accounts"];
        }
        
        $this->load->template('settings', $data);
    }
    
    function fetch_tcode() {
        $products = get_products();
        $dumb = $this->Trackify_DB->get_tcode($_SESSION['shop']);
        $tags = array_column($dumb, "tags");
        
        $new_tags = array();
        foreach ($products as $product) {            
            $ptags = explode(", ", $product["tags"]);
            
            foreach ($ptags as $t) {
                if (strpos($t, "rr_track_") !== FALSE && !in_array($t, $tags) && !in_array($t, $new_tags)) {
                    $new_tags[] = $t;    
                }
            }
        }
        
        if (!empty($new_tags)) {
            $result = $this->Trackify_DB->add_new_tags($new_tags);
        }
        
        echo json_encode(array("status" => 1, "result" => array("tags" => $new_tags, "count" => count($new_tags))));
        die();
    }
    
    function help() {
        $data = array();
        $this->load->template('help', $data);    
    }    
    
    function switch_tracking($type) {
        $result = $this->Trackify_DB->update_settings(array("track_with" => $type), array("store_name" => $_SESSION["shop"]));
        redirect("track");
    }
    
    
}
?>