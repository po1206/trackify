<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MY_Facebook extends MY_Controller {

    function __construct() {
        parent::__construct();
        $this->load->library('form_validation');
    }

    function oauth_finish() {
        try {
            $access_token = getFacebookAccessToken();
            $code = $this->input->get("code");
            $state = $this->input->get("state");
            
            $_SESSION['facebook_access_token'] = $access_token;         
            $_SESSION["FB_USER"] = getProfile($_SESSION["facebook_access_token"]);
        } catch (Exception $e) {
            handleException($e);
            redirect("track");
        }
        redirect($_GET["redirect"]);
    }

    function check_token() {
        if (empty($_SESSION["facebook_access_token"])) {
            $redirect = base_url() . "MY_Facebook/oauth_finish/?redirect=MY_Facebook/custom_audiences";
            $data['url'] = getFacebookLoginURL($redirect);
            redirect($data['url']);
        }
    }

    function custom_audiences($ad_account_id = "") {
        $this->check_token();

        $ad_accounts = getMyAdAccountsWithPixels($_SESSION["FB_USER"]["id"], $_SESSION["facebook_access_token"]);        
        $data["ad_accounts"] = $ad_accounts;
        $data["settings"] = $this->Trackify_DB->get_settings($_SESSION["shop"]);

        if ($ad_account_id == "") {
            if (!empty($data["settings"]["ad_account"])) {
                $ad_account_id = $data["settings"]["ad_account"];    
            } else {
                if (!empty($data["ad_accounts"]))
                    $ad_account_id = $data["ad_accounts"][0]["adaccount"]["id"];
            }
        }
        $audiences = getCustomAudiences($ad_account_id);

        if (!empty($audiences)) {
            $i = 0;
            foreach ($audiences as $row) {
                $audiences[$i]["subtype"] = ucfirst(strtolower($audiences[$i]["subtype"]));
                $audiences[$i]["time_created"] = date("m/d/Y, H:i", $audiences[$i]["time_created"]);
                $audiences[$i]["last_updated"] = date("m/d/Y, H:i", $audiences[$i]["time_updated"]);
                switch ($audiences[$i]["operation_status"]["code"]) {
                    case 422:
                        $audiences[$i]["short_desc"] = "Pixel not active";
                        break;
                    case 200:
                        if ($audiences[$i]["approximate_count"] == 20)
                            $audiences[$i]["short_desc"] = "Audience too small";
                        else 
                            $audiences[$i]["short_desc"] = "Ready";
                        break;
                    default:
                        $audiences[$i]["short_desc"] = $audiences[$i]["operation_status"]["description"];
                }
                $i++;
            }
        } else {
            $_SESSION['message_display'] = "You have no audience.";
        }

        $data["ad_account_id"] = $ad_account_id;
        $data["audiences"] = $audiences;
        
        $this->load->template("custom_audiences", $data);
    }

    function create_audience($type, $ad_account_id = "") {
        $this->check_token();

        $data["settings"] = $this->Trackify_DB->get_settings($_SESSION["shop"]);

        if (isset($_POST["submit"])) {
            if ($type == "custom") {
                $result = createCustomAudience($_POST);
            } else {
                $result = createLookalikeAudience($_POST);
            }
        
            if (!empty($result["audiences"])) {
                $_SESSION["message_display"] = "Created successfully.";
            }

            redirect("MY_Facebook/custom_audiences/{$_POST['ad_account']}");
        }
        
        $results = getMyAdAccountsWithPixels($_SESSION["FB_USER"]["id"], $_SESSION['facebook_access_token']);
        $i = 0;
        $data['ad_accounts'] = array();
        foreach ($results as $result) {            
            $account_name = $result['adaccount']['name'];
            
            $data['ad_accounts'][$i] = array(
                    'id'            => $result['adaccount']['id'],
                    'account_id'    => $result['adaccount']['account_id'],
                    'name'          => $account_name,
                    'currency'      => $result['adaccount']['currency'],
                    'users'         => end($result['adaccount']['users']),
                    'time_zone'     => $result['adaccount']['timezone_name'],                    
                );
            
            $i++;
        }

        if ($ad_account_id == "") {
            $data["ad_account_id"] = $data["settings"]["ad_account"];
            $ad_account_id = $data["settings"]["ad_account"];
        } else {
            $data["ad_account_id"] = $ad_account_id;    
        }

        if ($type == "custom") {
            $data["lookback"] = array(1, 3, 7, 14, 30, 60, 90, 120, 180);

        } else if ($type == "lookalike") {
            $audiences = getCustomAudiences($ad_account_id);
            foreach ($audiences as $a) {
                if ($a["subtype"] != "LOOKALIKE") {
                    $data["audiences"][] = $a;
                }
            }
            $data["audience_size"] = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 15, 20);
        }
        
        $this->load->template("create_{$type}_audience", $data);
    }

    function create_LAAs($ad_account_id = "") {
        $this->check_token();

        $data["settings"] = $this->Trackify_DB->get_settings($_SESSION["shop"]);

        if (isset($_POST["create_laas"])) {
            $result = createLAAs($_POST);
            if (!empty($result["audiences"])) {
                $_SESSION["message_display"] = "Created successfully.";
            }
            redirect("MY_Facebook/custom_audiences/{$_POST['ad_account']}");
        }
        
        $results = getMyAdAccountsWithPixels($_SESSION["FB_USER"]["id"], $_SESSION['facebook_access_token']);
        $i = 0;
        $data['ad_accounts'] = array();
        foreach ($results as $result) {            
            $account_name = $result['adaccount']['name'];
            
            $data['ad_accounts'][$i] = array(
                    'id'            => $result['adaccount']['id'],
                    'account_id'    => $result['adaccount']['account_id'],
                    'name'          => $account_name,
                    'currency'      => $result['adaccount']['currency'],
                    'users'         => end($result['adaccount']['users']),
                    'time_zone'     => $result['adaccount']['timezone_name'],                    
                );
            
            $i++;
        }

        if ($ad_account_id == "") {
            $data["ad_account_id"] = $data["settings"]["ad_account"];
            $ad_account_id = $data["settings"]["ad_account"];
        } else {
            $data["ad_account_id"] = $ad_account_id;    
        }

        $audiences = getCustomAudiences($ad_account_id);
        foreach ($audiences as $a) {
            if ($a["subtype"] != "LOOKALIKE") {
                $data["audiences"][] = $a;
            }
        }
        $data["audience_size"] = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 15, 20);
        
        $this->load->template("create_laas", $data);
    }

    function delete_ca($ad_account_id, $audience_id) {
        $this->check_token();
        
        deleteCustomAudience($ad_account_id, $audience_id);
        $_SESSION["message_display"] = "Deleted successfully.";
        redirect("MY_Facebook/custom_audiences/{$ad_account_id}");
    }    

    function feeds($business_id = "") {
        $this->check_token();

        $data["businesses"] = getBusinesses($_SESSION["facebook_access_token"]);
        if (!empty($data["businesses"])) {
            if ($business_id == "" && !empty($data["businesses"])) {
                $business_id = $data["businesses"][0]["id"];
            }
            $data["catalogs"] = getProductCatalogs($business_id);
            $_SESSION["FB_USER"] = getProfile($_SESSION["facebook_access_token"]);
            if ($_SESSION["FB_USER"]["timezone"] > 0) {
                $data["timezone"] = "+" . $_SESSION["FB_USER"]["timezone"] .":00";
            } else {
                $data["timezone"] = "-" . $_SESSION["FB_USER"]["timezone"] .":00";
            }
        } else {
            //$_SESSION["message_display"] .= "You have no catalogs created.";
        }
        
        $data["business_id"] = $business_id;
        $this->load->template("fb_feeds", $data);
    }

    function create_feed($catalog_id) {
        if ($catalog_id == "") redirect("facebook-feeds");
        if (isset($_POST["submit"])) {
            $result = createProductFeed($catalog_id);
            redirect("facebook-feeds/$catalog_id");
        }

        $data["catalog_id"] = $catalog_id;
        $data["catalog_name"] = getObjectName($catalog_id);
        $this->load->template("create_feed", $data);
    }

    function create_catalog($business_id) {
        if ($business_id == "") redirect("facebook-feeds");

        if (isset($_POST["submit"])) {
            $result = createProductCatalog($business_id);
            redirect("facebook-feeds/$business_id");
        }

        $data["channels"] = $this->Trackify_DB->get_channels($_SESSION["shop"]);        
        $data["settings"] = $this->Trackify_DB->get_settings($_SESSION["shop"]);        
        $data["ad_accounts"] = getMyOwnAdAccountsWithPixels($_SESSION["FB_USER"]["id"], $_SESSION["facebook_access_token"]);
        $data["business_id"] = $business_id;
        $this->load->template("create_catalog", $data);
    }

    function redirect($method) {
        redirect("{$method}/{$_POST['object_id']}");
    }

    function get_adaccounts_by_business($business_id) {
        $result = getAdAccountsByBusiness($business_id, $_SESSION["facebook_access_token"]);
        header('Content-Type: application/json');
        echo json_encode($result);
    }

    function get_pixels_by_adaccount($adaccount_id) {
        $result = getPixelsByAdAccount($adaccount_id, $_SESSION["facebook_access_token"]);
        header('Content-Type: application/json');
        echo json_encode($result);
    }

    function test() {
        test123();
    }
    
}    
?>