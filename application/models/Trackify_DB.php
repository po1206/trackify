<?php

class Trackify_DB extends CI_Model {
    
    function get_user_settings($store) {                
        $query = $this->db->query("SELECT * FROM tbl_usersettings WHERE store_name='$store'");    
        if ($query->num_rows() > 0)
            $dumb = $query->result_array()[0];
        else return false;
        if (empty($dumb['currency'])) $dumb['currency'] = 'USD';
        
        return $dumb;
    }
    
    function get_tcode_by_tag($val, $store) {        
        $query = $this->db->query("SELECT * FROM tcode WHERE tags='$val' AND store='$store'");    
        return $query->result_array();
    }
    
    function get_tcode($store, $track_with = "conversion") {        
        $query = $this->db->query("SELECT * FROM tcode WHERE store='$store' ORDER BY id DESC");
        return $query->result_array();
    }
    
    function delete_tcode($id) {
        $result = $this->db->query("DELETE FROM tcode WHERE store='" . $_SESSION['shop'] . "' AND id=" . $id);
        return $result;
    }
    
    function update_tcode($data, $where) {
        $this->db->where($where);
        return $this->db->update("tcode", $data);
    }
    
    function add_tcode($data) {
        $condition = "tags='" . $data['tags'] . "' AND store='" . $_SESSION['shop'] . "'";
        $this->db->select('*');
        $this->db->from('tcode');
        $this->db->where($condition);
        $this->db->limit(1);
        $query = $this->db->get();
        if ($query->num_rows() == 0) {
            $this->db->insert('tcode', $data);
            if ($this->db->affected_rows() > 0) {
                return 1;
            } else {
                return 0;
            }
        } else {
            return 2;
        }
    }
    
    function add_new_tags($tags) {
        foreach ($tags as $tag) {
            $values[] = array("tags" => $tag, "store" => $_SESSION['shop']);
        }
        //$query = $this->db->query("INSERT INTO tcode(tags, store) VALUES " . implode(", ", $values));
        $query = $this->db->insert_batch("tcode", $values);
        
        return $query;
    }

    function log($log_data) {        
        $this->db->insert("log", $log_data);       
    }
    
    function is_store_registered($store) {
        $query = $this->db->query("SELECT * FROM tbl_usersettings WHERE store_name='$store'");
        if ($query->num_rows() > 0) {
            return true;
        } else {
            return false;
        }
    }
    
    function insert_uninstalled_shop($store) {
        $this->db->insert('uninstalled_shop', array("store_name" => $store));
        if ($this->db->affected_rows() > 0) {
            return 1;
        } else {
            return 0;
        }
    }
    
    function get_settings($store) {
        $query = $this->db->query("SELECT * FROM tbl_usersettings WHERE store_name='$store'");
        return $query->result_array()[0];
    }
    
    function insert_settings($data, $store) {
        $this->db->insert('tbl_usersettings', $data);
        if ($this->db->affected_rows() > 0) {
            return 1;
        } else {
            return 0;
        }
    }

    function insert_subscriber($data) {
        $query = $this->db->get_where('tbl_subscribers', array('shop' => $data["shop"], 'email_address' => $data["email_address"]));
        if (empty($query->result_array())) {
            return $this->db->insert("tbl_subscribers", $data);
        } else {
            $this->db->where(array('shop' => $data["shop"], 'email_address' => $data["email_address"]));
            return $this->db->update("tbl_subscribers", $data);
        }
    }
    
    function update_settings($data, $where) {
         $this->db->where($where);
         return $this->db->update("tbl_usersettings", $data); 
    }

    function update_channels($data, $where) {
         $this->db->where($where);
         return $this->db->update("channels", $data); 
    }
    
    function delete_settings($store) {
        $result = $this->db->query("DELETE FROM tbl_usersettings WHERE store_name='{$store}'");
        return $result;
    }
    
    function get_channels_categories($id) {
        $query = $this->db->query("SELECT * FROM channels_categories_map WHERE channel_id='$id'");    
        return $query->result_array();
    }

    function reset_feed_categories($channel_id) {
        $query = $this->db->query("DELETE FROM channels_categories_map WHERE channel_id='{$channel_id}'");
    }
        
    function get_default_channels_categories() {

    }

    function get_channels($store) {
        $query = $this->db->query("SELECT * FROM channels WHERE store='$store' ORDER BY id DESC");
        return $query->result_array();
    }

    function get_channels_by_id($id) {
        $query = $this->db->get_where('channels', array('id' => $id));
        return $query->result_array();
    }

    function get_channels_updated($store) {
        if (!empty($store)) {
            $query = $this->db->query("SELECT * FROM channels WHERE store='{$store}' ORDER BY id DESC");    
        } else {
            $query = $this->db->query("SELECT * FROM channels WHERE action_required=1 ORDER BY id DESC");    
        }
        
        return $query->result_array();   
    }
    
    function get_default_google_prod_cat($channel_id) {
        $query = $this->db->query("SELECT default_google_prod_tax_id FROM channels WHERE id='$channel_id' LIMIT 1");
        return $query->result_array();
    }
    
    function search_google_prod_taxonomy($tagcat) {        
        $query = $this->db->query('SELECT * FROM google_prod_taxonomy WHERE taxonomy LIKE "%' . $tagcat . '%" LIMIT 10');        
        return $query->result_array();
    }
    
    function update_store_domain($store, $default_store_name) {
        $query = $this->db->query("SELECT * FROM channels_settings WHERE store_name = '$store' LIMIT 1");
        $sql_str = 'INSERT INTO channels_settings (domain_name, store_name) VALUE("' . $default_store_name . '", "' . $store . '")';
        if ($query->num_rows() > 0) {
             $sql_str = 'UPDATE channels_settings SET domain_name="' . $default_store_name . '" WHERE store_name="' . $store . '" LIMIT 1';
        }
        $query2 = $this->db->query($sql_str);
        return $query2;
    }
    
    function save_feed($channel_id, $data) {
        if(isset($data["ids"])){
            $query = $this->db->query("DELETE FROM channels_categories_map WHERE channel_id={$channel_id} AND id NOT IN (" . implode(",", $data["ids"]) . ")");
        }else{
            //if no ids clean up reset all categories 
            //$query = $this->db->query("DELETE FROM channels_categories_map WHERE channel_id='{$channel_id}'");
        }
        //Update default google category 
        $this->db->query('UPDATE channels SET default_google_prod_tax_id="' . $data['default_google_product_cat'] . '" WHERE id=' . $channel_id . ' LIMIT 1');

        $i = 0;
        $arr_ids = $data["ids"];
        if(!empty($arr_ids)){
            foreach ( $arr_ids as $id) {
                if ($id == -1) {
                    $dumb = array("channel_id" => $channel_id, "category_id" => $data['categories'][$i], "conditions" => "{$data['conditions'][$i]}");
                    $this->db->insert('channels_categories_map', $dumb);
                } else {
                    $dumb = array("category_id" => $data['categories'][$i], "conditions" => "{$data['conditions'][$i]}");
                    $this->db->where(array("id" => $id));
                    $this->db->update('channels_categories_map', $dumb);
                }
                $i++;
            }
        }
    }

    function cron_test() {
        //$note = file_get_contents("php://input");
        //$note = json_decode($note);
        $this->db->insert("users", array("username" => "Valentin", "email" => "111dev.valentin2013@gmail.com" . rand(0, 999999), "password" => "password", "note" => ""));
    }
    
}
  
?>
