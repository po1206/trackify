<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Audiences extends MY_Controller {

    function __construct() {
        parent::__construct();
        $this->load->library('form_validation');
    }

    function create($type = "custom", $ad_account_id = "") {
        checkToken("Audiences/create");

        $data["settings"] = $this->Trackify_DB->get_settings($_SESSION["shop"]);

        if (isset($_POST["submit"])) {
            if ($type == "custom") {
                $result = createAdvancedCustomAudience($_POST);
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
        
        $this->load->template("audiences/create_{$type}", $data);
    }
}