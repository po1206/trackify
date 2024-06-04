<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');       

class Report extends MY_Controller {

    function __construct() {
        parent::__construct();
        
        $this->load->library('form_validation');
    }

    function ajax_purchase() {
        $shop = $this->Trackify_DB->get_user_settings($_SESSION["shop"]);

        $start = $_REQUEST['start'];
        $length = $_REQUEST['length'];
        $result = get_purchase_report($shop, $start, $length);
        echo json_encode(array("data" => $result["data"], "start" => $start, "draw" => $_REQUEST['draw'], "recordsTotal"=> $length, "recordsFiltered"=> $result["count"]));
    }

    function purchase() {
        $data = [];
        $this->load->template('report_purchase', $data);
    }

    function guidedinstall() {
        $data = [];
        $this->load->template('guidedinstall', $data);
    }
    
}

?>
