<?php

    require_once (APPPATH . 'libraries/vendor/autoload.php');
    require_once (APPPATH . 'core/facebook.php');

    use phpish\shopify;

    function handle_exception($e) {
        //print_r($e);
        echo $e->getCode() . " : " . $e->getMessage();
    }

    function get_products() {
        global $sc;
        $result = array();

        $limit = 250;
        
        try {            
            $count = $sc('GET /admin/products/count.json', array());
            $total = ceil($count / $limit) + 1;
            for ($i = 1; $i <= $total; $i++) {
                $products = $sc('GET /admin/products.json?fields=id,title,image,tags&limit=' . $limit . '&page=' . $i, array());
                $result = array_merge($result, $products);
            }
            
        } catch (Exception $e) {
            handle_exception($e);
        }
        return $result;
    }

    function get_product($pid) {
        global $sc; 
        $result = array();
        
        try {            
            $result = $sc("GET /admin/products/$pid.json", array());
        } catch (Exception $e) {
            handle_exception($e);
        }
        
        return $result;
    }
    
    function get_google_prod_taxonomy($id) {
        global $db;
        $query = $db->query("SELECT * FROM google_prod_taxonomy WHERE id='$id' LIMIT 1");
        $ret_result = $query->result_array();
        if(isset($ret_result[0])){
            return $ret_result[0];
        }else{
            return false;
        }
    }
    
    function get_prodcat($product_type) {
        global $db;

        /* strip prefix */
        $skip_product_type_words = array("product");
        $product_type = str_replace($skip_product_type_words, "", $db->escape($product_type));
        $product_type = trim($product_type,"'");
        $product_type = strtolower($product_type);
        $sql_345 = 'SELECT * FROM google_prod_taxonomy WHERE taxonomy LIKE "%' . $product_type . '%" LIMIT 1';
        //echo $sql_345;
        $query = $db->query($sql_345);
        if ($query->num_rows() > 0) {
            return $query->result_array()[0];
        } else {
            $stringIn = $product_type;
            $array = explode(' ', strtolower($stringIn));
            $arrayReplace = array("shirt", "tank", "top", "hoodie", "shirts", "tanks", "tops", "hoodies");
            $result_array = array_values(array_intersect($arrayReplace, $array));
            $match_string = "";
            //print_r($result_array);

            if (count($result_array) > 0) {
                $match_string = $result_array[0];
            } else {
                $array2 = explode( '-', $stringIn);
                $result_array = array_values(array_intersect($arrayReplace, $array2));
                if (isset($result_array[0])) {
                    $match_string = $result_array[0];
                }
            }
            if (!empty($match_string)) {
                $query = $db->query('SELECT * FROM google_prod_taxonomy WHERE taxonomy LIKE "%' . $match_string . '%" LIMIT 1');                
                if ($query->num_rows() > 0) {
                    return $query->result_array()[0];                    
                } else {
                    $query = $db->query("SELECT * FROM google_prod_taxonomy WHERE taxonomy LIKE '%shirts%' LIMIT 1");
                    if ($query->num_rows() > 0) {
                        return $query->result_array()[0];
                    }
                }
            } else {
                if (count($array) > 0) {
                    foreach ($array as $k => $v) {
                         $v = trim($db->escape($v),"'");
                        $query = $db->query('SELECT * FROM google_prod_taxonomy WHERE taxonomy LIKE "%' . $v . '%" LIMIT 1');
                        if ($query->num_rows() > 0) {
                            return $query->result_array()[0];
                        }
                    }
                }
            }
        }
    }

    function get_feed_status($channel_id) {
        global $db;
        $store = $_SESSION["shop"];
        
        /* Get all products types id */
        $query = $db->query("SELECT * FROM channels WHERE id='{$channel_id}' LIMIT 1");
        $product_type_str = "";
        if ($query->num_rows() > 0){
            $row1 = $query->result_array()[0];
            $product_type_str = $row1['product_types'];
        }        
        
        if (strlen($product_type_str) > 0) {
            $re_arr_products_type = explode(', ', $product_type_str);
            
            if (!empty($re_arr_products_type)) {
                $arrret = array();
                foreach ($re_arr_products_type as $key => $val) {
                    if (empty($val) || !is_numeric($val)) continue;

                    $sql = "SELECT c.category_id FROM global_product_types as p join channels_categories_map as c on p.product_types=c.conditions WHERE c.channel_id={$channel_id} AND p.id={$val}";

                    $query = $db->query($sql);
                    
                    if ($query->num_rows() == 0) {
                        return array("result" => false, "message" => "Action Required - Please verify new product types.");
                    } else {
                        //Product type matched but category is missing !
                        $row2 = $query->result_array()[0];
                        if ($row2['category_id'] == 0) {
                            return array("result" => false, "message" => "Action Required - Product type matched but category is missing.");
                        }
                    }
                }
                
            }
        } else {
            return array("result" => false, "message" => "Action Required - Please verify new product types.");
        }
        
        $query = $db->query("SELECT action_required FROM channels WHERE id='{$channel_id}' LIMIT 1");
        if ($query->num_rows() > 0) {
            $row = $query->result_array()[0];
            if ($row["action_required"] == 1) {
                return array("result" => false, "message" => "Warning - Your products are updated.");
            }
        }
        
        return array("result" => true, "message" => "OK");
    }
    
    function is_category_mapped($channel_id) {
        global $db;
        $store = $_SESSION["shop"];
        
        /* Get all products types id */
        $query = $db->query("SELECT * FROM channels WHERE id='{$channel_id}' LIMIT 1");
        $product_type_str = "";
        if ($query->num_rows() > 0){
            $row1 = $query->result_array()[0];
            $product_type_str = $row1['product_types'];
        }        
        
        if (strlen($product_type_str) > 0) {
            $re_arr_products_type = explode(',', $product_type_str);
             
            if (count($re_arr_products_type) > 0) {
                if (is_array($re_arr_products_type)) {
                    $arrret = array();
                    foreach ($re_arr_products_type as $key => $val) {
                        if (empty($val) || !is_numeric($val)) continue;
                        $query = $db->query("SELECT id, product_types FROM global_product_types WHERE id={$val} LIMIT 1");
                        
                        if ($query->num_rows() > 0) {
                            $row2 = $query->result_array()[0];
                            //$sql_3 = 'SELECT * FROM channels_categories_map WHERE channel_id="' . $channel_id . '" AND conditions=' . $db->escape($row2['product_types']) . ' LIMIT 1';
                            // echo $val;
                            $db->select("category_id");
                            $db->where(array("channel_id" => $channel_id, "conditions" => $row2['product_types']));
                            $query = $db->get("channels_categories_map");

                            //$query = $db->query($sql_3);
                            
                            //Any Product type is not match
                            if ($query->num_rows() == 0) {
                                return false;
                            } else {
                                //Product type matched but category is missing !
                                $row3 = $query->result_array()[0];
                                if (intval($row3['category_id']) == 0) {
                                    return false;
                                }
                            }
                        }
                    }
                }
            }
        }
        
        return true;
    }
    
    function get_store_name_from_channels_set($store) {
        global $db;
        
        $query = $db->query("SELECT domain_name FROM channels_settings WHERE store_name='$store' LIMIT 1");
        
        if ($query->num_rows() > 0) {
            $row = $query->result_array()[0];
            return strtolower($row['domain_name']);
            
        }
        /* if store not saved by user get from shopify default */
        return strtolower($_SESSION['shop']);
    }
    
    function get_feed_details($channel_id){
        global $db;
        
        $query = $db->query("SELECT * FROM channels WHERE id={$channel_id}");
        if ($query->num_rows() > 0) {
            return $query->result_array()[0];
        }
        
        return false;
    }
    
    function get_default_google_prod_cat_name($channel_id) {
        global $db;
        
        $query = $db->query("SELECT taxonomy FROM `google_prod_taxonomy` INNER JOIN channels ON google_prod_taxonomy.id=channels.default_google_prod_tax_id WHERE channels.id=" . $channel_id . " LIMIT 1");
        if ($query->num_rows() > 0) {
            return $query->result_array()[0]['taxonomy'];
        }
        return "Apparel & Accessories > Clothing > Shirts & Tops";
    }
    function get_default_google_prod_cat_id($channel_id) {
        global $db;
        
        $query = $db->query("SELECT google_prod_taxonomy.id as g_cat_id FROM `google_prod_taxonomy` INNER JOIN channels ON google_prod_taxonomy.id=channels.default_google_prod_tax_id WHERE channels.id=" . $channel_id . " LIMIT 1");
        if ($query->num_rows() > 0) {
            return $query->result_array()[0]['g_cat_id'];
        }
        return "";
    }


    function store_defaulte_google_cat($channel_id) {
        global $db;
        
        if (is_category_mapped($channel_id) !== true) {
            $row_feed = get_feed_details($channel_id);
            
            if ($row_feed && !empty($row_feed['product_types'])) {
                
                $list_prod_type_id = trim($row_feed['product_types'], ",");
                $arr_prod_type_id = explode(',', $list_prod_type_id);
                $prev_exsisting_cat = "";
                if (is_array($arr_prod_type_id)) {
                    foreach ($arr_prod_type_id as $key_prodtype => $val_prodtype) {
                        //get prod type name 
                        $query = $db->query('SELECT * FROM global_product_types WHERE id="' . intval($val_prodtype) . '" LIMIT 1');                        
                        
                        if ($query->num_rows() > 0) {
                            $row_prod_type_name = $query->result_array()[0];
                            $sql_45 = 'SELECT * FROM channels_categories_map WHERE channel_id="' . $channel_id . '" AND conditions=' . $db->escape($row_prod_type_name['product_types']) . ' LIMIT 1';
                            $query2 = $db->query($sql_45);
                            
                            //Any Product type is not found in categories_map
                            if ($query2->num_rows() == 0) {                                

                                    $condition = $db->escape($row_prod_type_name['product_types']);
                                    $condition = trim($condition,"'");
                                    $condition = trim($condition,"\'");

                                    $condition = $row_prod_type_name['product_types'];

                                    if(!empty($condition)){
                                         //Find Good match from google category id and map it 
                                        $prodcat = get_prodcat($condition);
                                        
                                        $category_id = $prodcat['id'];
                                        $input_type = 'product_type';
                                        $match_type = '2';
                                        
                                        if(empty($category_id)){
                                            //if this is empty try to assign default category
                                            $category_id = get_default_google_prod_cat_id($channel_id);
                                        }
                                        if(!empty($category_id)){
                                            //temp Save for assign if cat not found 
                                            $prev_exsisting_cat = $category_id;
                                        }else{
                                            $category_id = $prev_exsisting_cat;
                                        }
                                        //die($condition);
                                        if(!empty($category_id)) {
                                            $query = $db->insert("channels_categories_map",
                                                    array("channel_id" => $channel_id, 
                                                        "category_id" => $category_id, 
                                                        "input_type" => $input_type, 
                                                        "match_type" => $match_type, 
                                                        "conditions" => $condition, 
                                                    )
                                                );
                                        }
                                    }
                            }
                        }
                    }
                }            
            }
        }
    }

    function get_products_type_arr($channel_id){
        global $db;

      $sql23 = "SELECT * FROM `channels` WHERE `id`='{$channel_id}' LIMIT 1";
      $query23 = $db->query($sql23);

      $product_type_str = "";
      if ($query23->num_rows() > 0) {
         $row23 = $query23->result_array()[0];
         $product_type_str = $row23['product_types'];
      }
      if(strlen($product_type_str) > 0){
         $re_arr_products_type = explode(',', $product_type_str);
         //print_r($re_arr_products_type);
           if (count($re_arr_products_type)>0){
            if(is_array($re_arr_products_type)){
               $arrret = array();
               foreach($re_arr_products_type as $key=>$val){
                  $val = strtolower($val);
                  $sql24 = "SELECT id,product_types FROM `global_product_types` WHERE `id` = '{$val}' LIMIT 1";
                  $query24 = $db->query($sql24);
                  if ($query24->num_rows() > 0) {
                     $row24 = $query24->result_array()[0];
                     $arrret[$val] = $row24['product_types'];
                  }
               }
               if(is_array($arrret)){
                    $arr_ret = array_unique($arrret);
                    $arr_google_cat = array();
                    if(is_array($arr_ret) && count($arr_ret) > 0):
                        foreach($arr_ret as $kkss=>$vvss){
                            echo $vvss."<br/>";
                            $prodcat = get_prodcat($vvss); 
                            $arr_google_cat[]= $prodcat;
                        }
                    endif;
                  return $arr_google_cat;
               }
               
            }
            
           }
      }
      return false;
   }

    function add_n_get_product_type_id($product_type) {
        global $db;

        $product_type = strtolower($product_type);
                
        $db->select("id");
        $db->where("product_types", $product_type);
        $query = $db->get("global_product_types");
        
        if ($query->num_rows() == 0) {            
            $query = $db->insert("global_product_types", array("product_types" => $product_type));
            if ($query === TRUE) {
                return $db->insert_id();
            } else {
                /*basue no product type added error */
                return 0;
            }
        } else {
            $row = $query->result_array()[0];
            return $row['id'];
        }
    }
    
    function clean_html_code($inn_html){
        return  $cleanString = htmlspecialchars(trim(preg_replace('/\s+/', ' ', strip_tags($inn_html))), ENT_QUOTES);
    }
    
    function get_description($body_html, $variant) {
        $result = html_entity_decode(htmlspecialchars(trim(preg_replace('/\s+/', ' ', strip_tags($body_html))), ENT_QUOTES));
        $result = preg_replace('/\s+/', ' ', $result);
        if(ctype_space($result) || $result === "" || $result === null){
            $result = $variant['title'] . ' ' . $variant['title'];
        }

        if (ctype_upper(preg_replace('/\W+/', '',trim($result)))) {
            $result = ucwords(strtolower($result));
        }
        if (strlen(trim($result)) <= 1) {
            $result = strtolower($result);
        }

        return $result;
    }

    function get_image($variant, $product) {
        $result = "";
        if (!empty($variant['image_id'])) {
            if (is_array($product['images'])) {
                foreach ($product['images'] as $img_k => $img_v) {
                    if ($img_v['id'] == $variant['image_id']) {
                        $result = $img_v['src'];
                    }
                }
            }
        } else {
            if (is_array($product['images']) && isset($product['images'][0]["src"])) {
                $result = $product['images'][0]["src"];
            }
        }
        return $result;
    }

    function get_tags($product) {
        $result = array();
        if (!empty($product['tags'])) {
            $tags = explode(',', trim($product['tags']));
            if (is_array($tags)) {
                foreach ($tags as $tag) {
                    if (strpos($tag, 'rr_track') !== false) {
                        $result[] = trim($tag, ',');
                    }
                }               
            }
        }
        return implode($result, ",");
    }

    function get_product_category($product, $channel_id) {
        global $db;

        $current_item_product_type = $product['product_type'];

        if (!empty($current_item_product_type)) {
            $sql = "SELECT taxonomy FROM google_prod_taxonomy gpt join channels_categories_map ccm on gpt.id=ccm.category_id WHERE ccm.conditions='" . addslashes($current_item_product_type) . "' AND ccm.channel_id={$channel_id}";
            
            $query = $db->query($sql);
            if ($query->num_rows() > 0) {
                $row = $query->result_array()[0];
                $pgoogle_product_category = $row['taxonomy'];                
            } else {
                $pgoogle_product_category = get_default_google_prod_cat_name($channel_id);
            }
        } else {
            $pgoogle_product_category = get_default_google_prod_cat_name($channel_id);
        }

        return $pgoogle_product_category;

    }

    function data_map($channel_id = 0) {
        global $db, $sc;
        
        try {
            $store = $_SESSION["shop"];
            if ($shop = $sc('GET /admin/shop.json', array('has_storefront' => 'true'))) {
                $default_domain = get_store_name_from_channels_set($store);
                    
                //print_r($shop);
                $xmlTemplateHeader = '<?xml version="1.0"?>
                    <rss xmlns:g="http://base.google.com/ns/1.0" version="2.0">
                    <channel>
                    <title>' . clean_html_code($shop['name']) . '</title>
                    <link>https://' . $shop['myshopify_domain'] . '</link>';
                $xmlBody = "";            
                $xmlTemplateFooter = '</channel></rss>';
                    
                $product_type_id_array = array();

                //Get Feed if no feed found 
                
                $query = $db->query("SELECT id, feed_file, store FROM channels WHERE store='$store' ORDER BY id DESC");
                $isOldFeed = $query->num_rows();

                $myFile = "feeds/" . sha1($store) . ".xml";

                /*Lock Db For First Run*/
                if ($isOldFeed == 0) {
                    $query = $db->query("INSERT INTO channels (feed_file, store, product_types, product_count) VALUES ('{$myFile}', '{$store}', '', '')");
                    if ($query === TRUE) {
                        $channel_id = $db->insert_id();
                    }
                    return;
                }

                if ($channel_id) {

                    //Count Published pages only 
                    if ($totalproductCount = $sc('GET /admin/products/count.json?published_status=published')) {
                        if ($totalproductCount > 0) {
                            $plimit = 50;
                            $totalPages = ceil($totalproductCount / $plimit);
                            $product_count = 0;                            
                            
                            for ($ipcount = 1; $ipcount <= $totalPages; $ipcount++) {
                                //Get Product Array 
                                $apiReq = 'GET /admin/products.json?published_status=published&limit=' . $plimit . '&page=' . $ipcount;
                                
                                if ($response_body_prod_arr = $sc($apiReq)) {
                                    
                                    if (is_array($response_body_prod_arr)) {
                                        foreach ($response_body_prod_arr as $prod_k => $product) {

                                            if (is_array($product['variants'])) {
                                                $variant = $product['variants'][0];
                                                $pid = $variant["id"];
                                                $pdescription = get_description($product['body_html'], $variant);
                                                $pimg_link = get_image($variant, $product);

                                                $ptags = $pproduct_type = htmlspecialchars(get_tags($product), ENT_QUOTES);

                                                $plink = trim($default_domain, '/') . '/products/' . $product["handle"] . '?variant=' . $variant['id'] . '&utm_source=facebook&utm_medium=catret&utm_campaign=' . $product["handle"] . '&utm_content=' . $ptags;
                                                if (!preg_match("~^(?:f|ht)tps?://~i", $plink)) {
                                                    $plink = 'https://' . $plink;
                                                }
                                                $plink = htmlspecialchars(trim(preg_replace('/\s+/', ' ', $plink)), ENT_QUOTES);

                                                $ptitle = $product['title'];
                                                $ptitle = htmlspecialchars($ptitle, ENT_QUOTES);

                                                $pprice = $variant["price"] . ' ' . $shop['currency'];
                                                $pcompare_at_price = $variant["compare_at_price"] . ' ' . $shop['currency'];
                                                
                                                if (!empty($product['product_type'])) {
                                                    $product_type_id_array[] = add_n_get_product_type_id(trim($product['product_type'], "'"));
                                                }

                                                $pgoogle_product_category = get_product_category($product, $channel_id);
                                                $pgoogle_product_category = htmlspecialchars($pgoogle_product_category, ENT_QUOTES);

                                                $brand = htmlspecialchars($product["vendor"], ENT_QUOTES);
                                                
                                                $item = "
                                                    <item>
                                                        <g:id>{$pid}</g:id>
                                                        <g:availability>in stock</g:availability>
                                                        <g:condition>new</g:condition>
                                                        <description>{$pdescription}</description>
                                                        <g:image_link>{$pimg_link}</g:image_link>
                                                        <link>{$plink}</link>
                                                        <title>{$ptitle}</title>
                                                        <g:price>{$pprice}</g:price>
                                                        <g:compare_at_price>{$pcompare_at_price}</g:compare_at_price>
                                                        <g:gtin/>
                                                        <g:brand>{$brand}</g:brand>
                                                        <g:item_group_id>{$product["id"]}</g:item_group_id>
                                                        <g:product_type>{$ptags}</g:product_type>
                                                        <g:google_product_category>{$pgoogle_product_category}</g:google_product_category>
                                                    </item>
                                                    ";

                                                $xmlBody .= $item;
                                                $product_count++;

                                            }
                                        }
                                    }                                    
                                }
                                sleep(1);
                            }                            
                            
                            $feedFileUrl = FCPATH . $myFile;

                            if ($channel_id == 745559) {
                                try {
                                    //$feedFile = base_url() ."feeds/3f7d9d6c9a3336625281d82ceec34c97fea46eb4.xml";
                                    $feedFile = base_url() . "feeds/aabbccc.xml";
                                    $feedFile = "https://app.redretarget.com/sapp/feeds/aabbccc.xml";

                                    echo $feedFile . "<br/>";
                                    echo $feedFileUrl . "<br/>";

                                    $theme = $sc('GET /admin/themes.json', array("role" => "main"), array());
                                    $asset = $sc("PUT /admin/themes/{$theme[0]["id"]}/assets.json", array(), array("asset" => array(
                                            "key" => "assets/" . sha1($store) . ".xml",
                                            "src" => $feedFile,
                                            //"src" => "https://88.80.131.147/redtargeting/feeds%20errors.txt",
                                            
                                            //"content_type" => "application/xml"
                                        )));
                                    print_r($asset); die();
                                    $feedFile = substr_replace($asset["public_url"], "", strpos($asset["public_url"], "?"));
                                    if (file_exists($feedFileUrl)) {
                                        //@unlink($feedFileUrl);
                                    }
                                } catch (Exception $e) {
                                    print_r($e); die();
                                }
                            } else {
                                $fh = fopen($feedFileUrl, 'w') or die("can't open file");
                                fwrite($fh, $xmlTemplateHeader . $xmlBody . $xmlTemplateFooter);
                                fclose($fh);
                                $feedFile = base_url() . $myFile;

                                /*if (filesize($feedFileUrl) < 8388608) {
                                    try {
                                        echo base_url() ."feeds/3f7d9d6c9a3336625281d82ceec34c97fea46eb4.xml";
                                        echo $feedFile;
                                        $theme = $sc('GET /admin/themes.json', array("role" => "main"), array());
                                        $asset = $sc("PUT /admin/themes/{$theme[0]["id"]}/assets.json", array(), array("asset" => array(
                                                "key" => "assets/" . sha1($store) . ".xml",
                                                "src" => $feedFile, //base_url() ."feeds/3f7d9d6c9a3336625281d82ceec34c97fea46eb4.xml",
                                                "content_type" => "application/xml"
                                            )));
                                        $feedFile = substr_replace($asset["public_url"], "", strpos($asset["public_url"], "?"));
                                        if (file_exists($feedFileUrl)) {
                                            //@unlink($feedFileUrl);
                                        }
                                    } catch (Exception $e) {
                                        print_r($e); die();
                                    }
                                }*/
                            }
                                    
                            $product_type_id_array = array_unique($product_type_id_array, SORT_NUMERIC);
                            $str_product_type_ids = implode(", ", $product_type_id_array);
                            
                            $query = $db->query('UPDATE channels set feedurl_changed=1, action_required=0, product_count="' . $product_count . '", product_types="' . $str_product_type_ids . '", feed_file="' . $feedFile . '" WHERE id=' . $channel_id . ' AND store="' . $store . '"');
                        }
                    }
                }
            } else {
                redirect("Login");
            }
        } catch (Exception $e) {
            print_r($e);
            handle_exception($e);
        }
    }

    function check_permission() {
        if (empty($_SESSION['shop'])) redirect("install/");
    }

    function detect_device() {
        $tablet_browser = 0;
        $mobile_browser = 0;
         
        if (preg_match('/(tablet|ipad|playbook)|(android(?!.*(mobi|opera mini)))/i', strtolower($_SERVER['HTTP_USER_AGENT']))) {
            $tablet_browser++;
        }
         
        if (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|android|iemobile)/i', strtolower($_SERVER['HTTP_USER_AGENT']))) {
            $mobile_browser++;
        }
         
        if ((strpos(strtolower($_SERVER['HTTP_ACCEPT']),'application/vnd.wap.xhtml+xml') > 0) or ((isset($_SERVER['HTTP_X_WAP_PROFILE']) or isset($_SERVER['HTTP_PROFILE'])))) {
            $mobile_browser++;
        }
         
        $mobile_ua = strtolower(substr($_SERVER['HTTP_USER_AGENT'], 0, 4));
        $mobile_agents = array(
            'w3c ','acs-','alav','alca','amoi','audi','avan','benq','bird','blac',
            'blaz','brew','cell','cldc','cmd-','dang','doco','eric','hipt','inno',
            'ipaq','java','jigs','kddi','keji','leno','lg-c','lg-d','lg-g','lge-',
            'maui','maxo','midp','mits','mmef','mobi','mot-','moto','mwbp','nec-',
            'newt','noki','palm','pana','pant','phil','play','port','prox',
            'qwap','sage','sams','sany','sch-','sec-','send','seri','sgh-','shar',
            'sie-','siem','smal','smar','sony','sph-','symb','t-mo','teli','tim-',
            'tosh','tsm-','upg1','upsi','vk-v','voda','wap-','wapa','wapi','wapp',
            'wapr','webc','winw','winw','xda ','xda-');
         
        if (in_array($mobile_ua,$mobile_agents)) {
            $mobile_browser++;
        }
         
        if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']),'opera mini') > 0) {
            $mobile_browser++;
            //Check for tablets on opera mini alternative headers
            $stock_ua = strtolower(isset($_SERVER['HTTP_X_OPERAMINI_PHONE_UA'])?$_SERVER['HTTP_X_OPERAMINI_PHONE_UA']:(isset($_SERVER['HTTP_DEVICE_STOCK_UA'])?$_SERVER['HTTP_DEVICE_STOCK_UA']:''));
            if (preg_match('/(tablet|ipad|playbook)|(android(?!.*mobile))/i', $stock_ua)) {
              $tablet_browser++;
            }
        }
         
        if ($tablet_browser > 0) {
           // do something for tablet devices
           return 'tablet';
        }
        else if ($mobile_browser > 0) {
           // do something for mobile devices
           return 'mobile';
        }
        else {
           // do something for everything else
           return 'desktop';
        }
    }

    function get_pid_from_vid($shop, $access_token, $vid) {
        try {
            $sc = shopify\client($shop, SHOPIFY_APP_API_KEY, $access_token);
            foreach ($vid as $id) {
                $p = $sc("GET /admin/variants/{$id}.json?fields=product_id", array());
                $result[] = $p["product_id"];
            }
        } catch (Exception $e) {
            return array();
        }

        return array_unique($result);
    }

    function get_purchase_report($shop, $start, $length) {
        global $db;

        date_default_timezone_set($shop["timezone"]);

        $page = floor($start / $length) + 1;
        try {
            $sc = shopify\client($shop["store_name"], SHOPIFY_APP_API_KEY, $shop["access_token"]);
            $count = $sc("GET /admin/orders/count.json", array());

            $orders = $sc("GET /admin/orders.json?fields=number,id,gateway,updated_at,total_price,currency,landing_site,referring_site&limit={$length}&page={$page}", array());

            for ($i = 0; $i < count($orders); $i++) {
                $query = $db->query("SELECT * FROM log WHERE shop_id={$shop["id"]} AND event='purchase' AND order_id={$orders[$i]['id']}");
                if ($query->num_rows() > 0) {
                    $h = $query->result_array()[0];
                    $orders[$i]["fired"] = 1;
                    $orders[$i]["pixel_date"] = $h["pixel_date"];
                } else {
                    $orders[$i]["fired"] = 0;
                    $orders[$i]["pixel_date"] = '';
                }

                $utm_campaign = $utm_source = $utm_medium = $utm_term = $utm_content = "";
                if (isset($orders[$i]["landing_site"])) {
                    $parts = parse_url($orders[$i]["landing_site"]);
                    if (isset($parts['query'])) {
                        parse_str($parts['query'], $query);
                        if (isset($query["utm_campaign"])) $utm_campaign = $query["utm_campaign"];
                        if (isset($query["utm_source"])) $utm_source = $query["utm_source"];
                        if (isset($query["utm_medium"])) $utm_medium = $query["utm_medium"];
                        if (isset($query["utm_term"])) $utm_term = $query["utm_term"];
                        if (isset($query["utm_content"])) $utm_content = $query["utm_content"];
                    }
                }

                if ($utm_campaign == "" && isset($orders[$i]["referring_site"])) {
                    $parts = parse_url($orders[$i]["referring_site"]);
                    if (isset($parts['query'])) {
                        parse_str($parts['query'], $query);
                        if (isset($query["utm_campaign"])) $utm_campaign = $query["utm_campaign"];
                        if (isset($query["utm_source"])) $utm_source = $query["utm_source"];
                        if (isset($query["utm_medium"])) $utm_medium = $query["utm_medium"];
                        if (isset($query["utm_term"])) $utm_term = $query["utm_term"];
                        if (isset($query["utm_content"])) $utm_content = $query["utm_content"];
                    }
                }
                
                $orders[$i]["utm_campaign"] = $utm_campaign;
                $orders[$i]["utm_source"] = $utm_source;
                $orders[$i]["utm_medium"] = $utm_medium;
                $orders[$i]["utm_term"] = $utm_term;
                $orders[$i]["utm_content"] = $utm_content;

                $orders[$i]["updated_at"] = date("M j \a\\t g:i:s A", strtotime($orders[$i]["updated_at"]));
                $orders[$i]["event"] = "Purchase";
                $orders[$i]["amount"] = $orders[$i]["currency"] . " " . $orders[$i]["total_price"];
            }

        } catch (Exception $e) {
            print_r($e);
        }

        return array("count" => $count, "data" => $orders);
    }

    function country_html($print = 0) {
        $country = '<select name="country" id="country" class="form-control select2" required>
<option value="">Select a country</option>
<option value="AF">Afghanistan</option>
<option value="AL">Albania</option>
<option value="DZ">Algeria</option>
<option value="AS">American Samoa</option>
<option value="AD">Andorra</option>
<option value="AG">Angola</option>
<option value="AI">Anguilla</option>
<option value="AG">Antigua and Barbuda</option>
<option value="AR">Argentina</option>
<option value="AA">Armenia</option>
<option value="AW">Aruba</option>
<option value="AU">Australia</option>
<option value="AT">Austria</option>
<option value="AZ">Azerbaijan</option>
<option value="BS">Bahamas</option>
<option value="BH">Bahrain</option>
<option value="BD">Bangladesh</option>
<option value="BB">Barbados</option>
<option value="BY">Belarus</option>
<option value="BE">Belgium</option>
<option value="BZ">Belize</option>
<option value="BJ">Benin</option>
<option value="BM">Bermuda</option>
<option value="BT">Bhutan</option>
<option value="BO">Bolivia</option>
<option value="BL">Bonaire</option>
<option value="BA">Bosnia and Herzegovina</option>
<option value="BW">Botswana</option>
<option value="BR">Brazil</option>
<option value="BC">British Indian Ocean Ter</option>
<option value="BN">Brunei</option>
<option value="BG">Bulgaria</option>
<option value="BF">Burkina Faso</option>
<option value="BI">Burundi</option>
<option value="KH">Cambodia</option>
<option value="CM">Cameroon</option>
<option value="CA">Canada</option>
<option value="IC">Canary Islands</option>
<option value="CV">Cape Verde</option>
<option value="KY">Cayman Islands</option>
<option value="CF">Central African Republic</option>
<option value="TD">Chad</option>
<option value="CD">Channel Islands</option>
<option value="CL">Chile</option>
<option value="CN">China</option>
<option value="CI">Christmas Island</option>
<option value="CS">Cocos Island</option>
<option value="CO">Colombia</option>
<option value="CC">Comoros</option>
<option value="CG">Congo</option>
<option value="CK">Cook Islands</option>
<option value="CR">Costa Rica</option>
<option value="CT">Cote D\'Ivoire</option>
<option value="HR">Croatia</option>
<option value="CU">Cuba</option>
<option value="CB">Curacao</option>
<option value="CY">Cyprus</option>
<option value="CZ">Czech Republic</option>
<option value="DK">Denmark</option>
<option value="DJ">Djibouti</option>
<option value="DM">Dominica</option>
<option value="DO">Dominican Republic</option>
<option value="TM">East Timor</option>
<option value="EC">Ecuador</option>
<option value="EG">Egypt</option>
<option value="SV">El Salvador</option>
<option value="GQ">Equatorial Guinea</option>
<option value="ER">Eritrea</option>
<option value="EE">Estonia</option>
<option value="ET">Ethiopia</option>
<option value="FA">Falkland Islands</option>
<option value="FO">Faroe Islands</option>
<option value="FJ">Fiji</option>
<option value="FI">Finland</option>
<option value="FR">France</option>
<option value="GF">French Guiana</option>
<option value="PF">French Polynesia</option>
<option value="FS">French Southern Ter</option>
<option value="GA">Gabon</option>
<option value="GM">Gambia</option>
<option value="GE">Georgia</option>
<option value="DE">Germany</option>
<option value="GH">Ghana</option>
<option value="GI">Gibraltar</option>
<option value="GB">Great Britain</option>
<option value="GR">Greece</option>
<option value="GL">Greenland</option>
<option value="GD">Grenada</option>
<option value="GP">Guadeloupe</option>
<option value="GU">Guam</option>
<option value="GT">Guatemala</option>
<option value="GN">Guinea</option>
<option value="GY">Guyana</option>
<option value="HT">Haiti</option>
<option value="HW">Hawaii</option>
<option value="HN">Honduras</option>
<option value="HK">Hong Kong</option>
<option value="HU">Hungary</option>
<option value="IS">Iceland</option>
<option value="IN">India</option>
<option value="ID">Indonesia</option>
<option value="IA">Iran</option>
<option value="IQ">Iraq</option>
<option value="IR">Ireland</option>
<option value="IM">Isle of Man</option>
<option value="IL">Israel</option>
<option value="IT">Italy</option>
<option value="JM">Jamaica</option>
<option value="JP">Japan</option>
<option value="JO">Jordan</option>
<option value="KZ">Kazakhstan</option>
<option value="KE">Kenya</option>
<option value="KI">Kiribati</option>
<option value="NK">Korea North</option>
<option value="KS">Korea South</option>
<option value="KW">Kuwait</option>
<option value="KG">Kyrgyzstan</option>
<option value="LA">Laos</option>
<option value="LV">Latvia</option>
<option value="LB">Lebanon</option>
<option value="LS">Lesotho</option>
<option value="LR">Liberia</option>
<option value="LY">Libya</option>
<option value="LI">Liechtenstein</option>
<option value="LT">Lithuania</option>
<option value="LU">Luxembourg</option>
<option value="MO">Macau</option>
<option value="MK">Macedonia</option>
<option value="MG">Madagascar</option>
<option value="MY">Malaysia</option>
<option value="MW">Malawi</option>
<option value="MV">Maldives</option>
<option value="ML">Mali</option>
<option value="MT">Malta</option>
<option value="MH">Marshall Islands</option>
<option value="MQ">Martinique</option>
<option value="MR">Mauritania</option>
<option value="MU">Mauritius</option>
<option value="ME">Mayotte</option>
<option value="MX">Mexico</option>
<option value="MI">Midway Islands</option>
<option value="MD">Moldova</option>
<option value="MC">Monaco</option>
<option value="MN">Mongolia</option>
<option value="MS">Montserrat</option>
<option value="MA">Morocco</option>
<option value="MZ">Mozambique</option>
<option value="MM">Myanmar</option>
<option value="NA">Nambia</option>
<option value="NU">Nauru</option>
<option value="NP">Nepal</option>
<option value="AN">Netherland Antilles</option>
<option value="NL">Netherlands (Holland, Europe)</option>
<option value="NV">Nevis</option>
<option value="NC">New Caledonia</option>
<option value="NZ">New Zealand</option>
<option value="NI">Nicaragua</option>
<option value="NE">Niger</option>
<option value="NG">Nigeria</option>
<option value="NW">Niue</option>
<option value="NF">Norfolk Island</option>
<option value="NO">Norway</option>
<option value="OM">Oman</option>
<option value="PK">Pakistan</option>
<option value="PW">Palau Island</option>
<option value="PS">Palestine</option>
<option value="PA">Panama</option>
<option value="PG">Papua New Guinea</option>
<option value="PY">Paraguay</option>
<option value="PE">Peru</option>
<option value="PH">Philippines</option>
<option value="PO">Pitcairn Island</option>
<option value="PL">Poland</option>
<option value="PT">Portugal</option>
<option value="PR">Puerto Rico</option>
<option value="QA">Qatar</option>
<option value="ME">Republic of Montenegro</option>
<option value="RS">Republic of Serbia</option>
<option value="RE">Reunion</option>
<option value="RO">Romania</option>
<option value="RU">Russia</option>
<option value="RW">Rwanda</option>
<option value="NT">St Barthelemy</option>
<option value="EU">St Eustatius</option>
<option value="HE">St Helena</option>
<option value="KN">St Kitts-Nevis</option>
<option value="LC">St Lucia</option>
<option value="MB">St Maarten</option>
<option value="PM">St Pierre &amp; Miquelon</option>
<option value="VC">St Vincent &amp; Grenadines</option>
<option value="SP">Saipan</option>
<option value="SO">Samoa</option>
<option value="AS">Samoa American</option>
<option value="SM">San Marino</option>
<option value="ST">Sao Tome &amp; Principe</option>
<option value="SA">Saudi Arabia</option>
<option value="SN">Senegal</option>
<option value="RS">Serbia</option>
<option value="SC">Seychelles</option>
<option value="SL">Sierra Leone</option>
<option value="SG">Singapore</option>
<option value="SK">Slovakia</option>
<option value="SI">Slovenia</option>
<option value="SB">Solomon Islands</option>
<option value="OI">Somalia</option>
<option value="ZA">South Africa</option>
<option value="ES">Spain</option>
<option value="LK">Sri Lanka</option>
<option value="SD">Sudan</option>
<option value="SR">Suriname</option>
<option value="SZ">Swaziland</option>
<option value="SE">Sweden</option>
<option value="CH">Switzerland</option>
<option value="SY">Syria</option>
<option value="TA">Tahiti</option>
<option value="TW">Taiwan</option>
<option value="TJ">Tajikistan</option>
<option value="TZ">Tanzania</option>
<option value="TH">Thailand</option>
<option value="TG">Togo</option>
<option value="TK">Tokelau</option>
<option value="TO">Tonga</option>
<option value="TT">Trinidad &amp; Tobago</option>
<option value="TN">Tunisia</option>
<option value="TR">Turkey</option>
<option value="TU">Turkmenistan</option>
<option value="TC">Turks &amp; Caicos Is</option>
<option value="TV">Tuvalu</option>
<option value="UG">Uganda</option>
<option value="UA">Ukraine</option>
<option value="AE">United Arab Emirates</option>
<option value="GB">United Kingdom</option>
<option value="US">United States of America</option>
<option value="UY">Uruguay</option>
<option value="UZ">Uzbekistan</option>
<option value="VU">Vanuatu</option>
<option value="VS">Vatican City State</option>
<option value="VE">Venezuela</option>
<option value="VN">Vietnam</option>
<option value="VB">Virgin Islands (Brit)</option>
<option value="VA">Virgin Islands (USA)</option>
<option value="WK">Wake Island</option>
<option value="WF">Wallis &amp; Futana Is</option>
<option value="YE">Yemen</option>
<option value="ZR">Zaire</option>
<option value="ZM">Zambia</option>
<option value="ZW">Zimbabwe</option>
</select>';
        
        if ($print == 1) {
            echo $country;
        } else {
            return $country;
        }
    }


?>