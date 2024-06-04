<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

require_once (APPPATH . 'libraries/vendor/autoload.php');

use phpish\shopify;

class Auth extends CI_Controller {

    protected $crud;

    protected $free_stores = array("redretarget.myshopify.com");

    function __construct() {
        parent::__construct();
        
        $this->load->helper('form');
        $this->load->library('form_validation');
        $this->load->library('session');
        
        $this->load->model('Trackify_DB');
    }
    
    function index() {
        redirect("install");
    }
    
    function install() {
        if (!empty($_SESSION['oauth_token'])) {
            if ($this->Trackify_DB->is_store_registered($_SESSION['shop'])) {
                try {
                    $shopify = shopify\client($_SESSION['shop'], SHOPIFY_APP_API_KEY, $_SESSION['oauth_token']);
                    if ($shop = $shopify('GET /admin/shop.json', array('has_storefront' => 'true'))) {            
                        redirect("track");
                    }
                } catch (Exception $e) {
                    
                }
            }
        }
        $this->load->template('install');
    }

    function logout() {
        unset($_SESSION['oauth_token']);        
        $this->session->sess_destroy();
        redirect("install");
    }
    
    function shopify_oauth() {
        $this->form_validation->set_rules('shop', 'Shop URL', 'trim|required|xss_clean');        
        if ($this->form_validation->run() == FALSE) {
            $this->load->template('install');
        } else {
            $shop_url = $this->input->post('shop');
            redirect("//{$shop_url}/admin/api/auth?api_key=" . SHOPIFY_APP_API_KEY);
        }
    }
    
    function req_billing($fprice, $fdays, $upgrade = 0) {
        $store = $_SESSION['shop'];        
        $oauth_token = $_SESSION['oauth_token']; 
        $code = $_GET['code'];
        $shopify = shopify\client($_SESSION['shop'], SHOPIFY_APP_API_KEY, $_SESSION['oauth_token']);
        if ($upgrade == 0) {
            $name = "Trackify Gold";
        } else {
            $name = "Trackify Platinum";
        }
        $billing = $shopify('POST /admin/recurring_application_charges.json', 
                        array('recurring_application_charge' => array(
                                'name' => $name, 
                                'price' => $fprice,
                                'return_url'=> base_url() . "Auth/process_billing/{$store}/{$upgrade}",
                                'trial_days' => $fdays)
                        )
                    );
        $bill_url = $billing['confirmation_url'];
        redirect($bill_url);
    }

    function upgrade() {
        check_permission();
        $this->req_billing(APP_HIGHER_PRICE, 0, 1);
    }

    function downgrade() {
        check_permission();
        $this->req_billing(APP_PRICE, 0, 0);
    }
    
    function uninstall($store) {        
        $this->Trackify_DB->delete_settings($store);
        $this->Trackify_DB->insert_uninstalled_shop($store);
    }
    
    function update_shop($store) {
        if ($row = $this->Trackify_DB->get_settings($store)) {
            $access_token = $row["access_token"];
            $shopify = shopify\client($store, SHOPIFY_APP_API_KEY, $access_token);
        
            $shop = $shopify('GET /admin/shop.json', array());
            $currency = $shop['currency'];
            $this->Trackify_DB->update_settings(array("currency" => $currency), array("store_name" => $store));                
        }
    }

    function update_product($store) {
        if ($row = $this->Trackify_DB->get_settings($store)) {
            $this->Trackify_DB->update_channels(array("action_required" => 1), array("store" => $store));           
        }
    }
    
    function process_billing($store, $upgrade) {
        try {
            $oauth_token = $_SESSION['oauth_token'];

            $shopify = shopify\client($store, SHOPIFY_APP_API_KEY, $oauth_token);

            $crgid = $_GET['charge_id'];
            $charge = $shopify('GET /admin/recurring_application_charges/'.$crgid.'.json');
            
            if ($charge['status'] === 'accepted') {            
                $activate = $shopify('POST /admin/recurring_application_charges/'.$crgid.'/activate.json', 
                                array('recurring_application_charge' => array(
                                    'id' => $charge['id'], 'name' => $charge['name'], 
                                    'api_client_id' => $charge['api_client_id'],
                                    'price' => $charge['price'],
                                    'status' => 'accepted',
                                    'return_url' => $charge['return_url'],
                                    'billing_on' => $charge['billing_on'],
                                    'created_at' => $charge['created_at'],
                                    'updated_at' => $charge['updated_at'],
                                    'test' => $charge['test'],
                                    'activated_on' => $charge['activated_on'], 
                                    'trial_ends_on' => $charge['trial_ends_on'],
                                    'cancelled_on' => $charge['cancelled_on'],
                                    'trial_days' => $charge['trial_days'], 
                                    'decorated_return_url' => $charge['decorated_return_url'])
                                )
                            );
            }

            $charge = $shopify('GET /admin/recurring_application_charges/'.$crgid.'.json');

            if ($charge['status'] === 'active') {
                if ($this->Trackify_DB->is_store_registered($store)) {                    
                    $row = $this->Trackify_DB->get_settings($store);                
                    $billing = $row["billing"];        
                    
                    $this->register_hook();
                    $this->Trackify_DB->update_settings(array("access_token" => "$oauth_token", "billing" => $crgid, "upgraded" => $upgrade), array("store_name" => $store));                
                    redirect(base_url() . "welcome?install=1");
                } else {
                    $this->install_app($crgid);
                }
            } else {
                if ($upgrade == 1) {
                    redirect("settings/");
                } else {
                    if ($this->Trackify_DB->is_store_registered($store)) {         
                        header("Location: https://".$store."/admin/apps");
                    } else {
                        $this->register_hook();
                        $this->Trackify_DB->insert_settings(array("store_name" => "$store", "access_token" => "$oauth_token", "smode" => "mode1", "billing" => "rejected", "upgraded" => $upgrade));
                    }
                    redirect("https://" . $store . "/admin/apps");
                }
            }
        } catch (Exception $e) {
            handle_exception($e);
        }
    }
    
    function is_valid_request() {
        $signature_data = "code={$_GET['code']}&shop={$_GET['shop']}&state={$_GET['state']}&timestamp={$_GET['timestamp']}";
        $calculatedHmac = hash_hmac('sha256', $signature_data, SHOPIFY_APP_SHARED_SECRET);
        if ($calculatedHmac == $_GET['hmac']) return true;
        else return false;
    }

    function oauth() {
        global $sc;

        $db = DB();
        
        # Step 2: http://docs.shopify.com/api/authentication/oauth#asking-for-permission
        if (!isset($_GET['code'])) {
            $permission_url = shopify\authorization_url($_GET['shop'], SHOPIFY_APP_API_KEY, array('read_script_tags', 'write_script_tags', 'read_themes', 'write_themes', 'read_products', 'write_products', 'read_customers', 'read_orders'));
            $permission_url = $permission_url . "&redirect_uri=" . base_url() . "oauth/&state=" . rand();
            redirect($permission_url);
        }
        
        if (!$this->is_valid_request()) redirect(APPSTORE_URL);
        # Step 3: http://docs.shopify.com/api/authentication/oauth#confirming-installation
        try {
            # shopify\access_token can throw an exception
            $oauth_token = shopify\access_token($_GET['shop'], SHOPIFY_APP_API_KEY, SHOPIFY_APP_SHARED_SECRET, $_GET['code']);
            
            $_SESSION['oauth_token'] = $oauth_token;
            $_SESSION['shop'] = $_GET['shop'];
            
            $sc = shopify\client($_SESSION['shop'], SHOPIFY_APP_API_KEY, $_SESSION['oauth_token']);

            $shared_secret = SHOPIFY_APP_SHARED_SECRET;
            $code = $_GET["code"];
            $timestamp = $_GET["timestamp"];
            $state = $_GET["state"];
            $store = $_GET['shop'];
            
            /*$signature_data = "code={$_GET['code']}&shop={$_GET['shop']}&state={$_GET['state']}&timestamp={$_GET['timestamp']}";
            $calculatedHmac = hash_hmac('sha256', $signature_data, $shared_secret);
            echo $calculatedHmac; die();
            // Use signature data to check that the response is from Shopify or not
            if ($calculatedHmac != $_GET['hmac']) redirect(APPSTORE_URL);*/
            

            $price = APP_PRICE; //19.95;
            $days = 10;
            
            $sql = "SELECT id, billing, access_token FROM tbl_usersettings WHERE store_name='$store'";
            $result = $db->query($sql);
            
            // if exists in db
            if ($result->num_rows() > 0) {
                $row = $result->result_array()[0];                
                $billing = $row["billing"];
                
                $this->Trackify_DB->update_settings(array("access_token" => $oauth_token), array("store_name" => $store));
                if (!empty($billing) && $billing !== 'rejected') {                    
                    $this->register_hook();
                    redirect(base_url() . "welcome?install=2");
                } else {
                    $this->req_billing($price, 0);
                }
            } else {
                $sql = "SELECT * FROM coupon WHERE store_name='$store'";
                $result = $db->query($sql);
                // if has a coupon or discount
                if ($result->num_rows() > 0) {
                    $rows = $result->result_array();
                    foreach ($rows as $row) { 
                        if ($row["days"] > 1000) {
                             $this->install_app();
                            return;
                        } else {
                            $this->req_billing($row["price"], $row["days"]);
                        }
                    }
                } else {
                    $sql2 = "SELECT id FROM uninstalled_shop WHERE store_name='$store'";
                    $result2 = $db->query($sql2);
                    
                    if (in_array($store, $this->free_stores)) {
                        $this->install_app();
                        return;
                    }
                    // if app was uninstalled once
                    if ($result2->num_rows() > 0) {              
                        $this->req_billing($price, 0);
                    } else { // if fresh install
                        $this->req_billing($price, $days);
                    }
                }
            }
        } catch (Exception $e) {
            print_r($e);
            //redirect("install");
            handle_exception($e);
        }
    }

    function register_hook() {
        global $sc;

        $sc = shopify\client($_SESSION['shop'], SHOPIFY_APP_API_KEY, $_SESSION['oauth_token']);
                              
        $hook = $sc('GET /admin/webhooks/count.json?topic=app/uninstalled', array());        
        if( $hook < 1 ) {            
            $hook = $sc('POST /admin/webhooks.json', array('webhook' => array('topic' => 'app/uninstalled', 'address' => base_url() . 'Auth/uninstall/' . $_SESSION['shop'], 'format' => 'json' )));
        }

        $hook = $sc('GET /admin/webhooks/count.json?topic=shop/update', array());
        if($hook < 1) {
            $hook = $sc('POST /admin/webhooks.json', array('webhook' => array('topic' => 'shop/update', 'address' => base_url() . 'Auth/update_shop/' . $_SESSION['shop'], 'format' => 'json' )));
        }

        $hook = $sc('GET /admin/webhooks/count.json?topic=products/update', array());
        if($hook < 1) {
            $hook = $sc('POST /admin/webhooks.json', array('webhook' => array('topic' => 'products/update', 'address' => base_url() . 'Auth/update_product/' . $_SESSION['shop'], 'format' => 'json' )));
        }

        $hook = $sc('GET /admin/webhooks/count.json?topic=products/create', array());
        if($hook < 1) {
            $hook = $sc('POST /admin/webhooks.json', array('webhook' => array('topic' => 'products/create', 'address' => base_url() . 'Auth/update_product/' . $_SESSION['shop'], 'format' => 'json' )));
        }
        
        $hook = $sc('GET /admin/webhooks/count.json?topic=products/delete', array());
        if($hook < 1) {
            $hook = $sc('POST /admin/webhooks.json', array('webhook' => array('topic' => 'products/delete', 'address' => base_url() . 'Auth/update_product/' . $_SESSION['shop'], 'format' => 'json' )));
        }
    }
    
    function install_app($billing = 1) {
        global $sc;

        $this->register_hook();

        $script = $sc('POST /admin/script_tags.json', array('script_tag' => array('event' => 'onload', 'src'=> base_url() . "ptag/{$_SESSION['shop']}" )));
    
        $db = DB();
        $sql = "INSERT INTO tbl_usersettings (store_name, access_token, smode, billing ) VALUES ('{$_SESSION['shop']}', '{$_SESSION['oauth_token']}', 'mode1', '{$billing}')";
        $query = $db->query($sql);

        $query = $db->query("SELECT * FROM tbl_subscribers WHERE shop='{$_SESSION['shop']}'");

        if ($query->num_rows() == 0) {
            if ($shop = $sc('GET /admin/shop.json', array('has_storefront' => 'true'))) {
                $row = array(
                        "email_address"     => $shop["email"],
                        "first_name"        => $shop["shop_owner"],
                        "shop"              => $_SESSION["shop"],
                        "shop_owner"        => $shop["shop_owner"],
                        "shop_name"         => $shop["name"],
                        "shop_email"        => $shop["email"],
                        "shop_settings"     => serialize($shop),
                    );
                $this->Trackify_DB->insert_subscriber($row);    
            }        
        }

        redirect(base_url() . "welcome/?install=1");
    }
}
?>
