<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

use phpish\shopify;

class MY_Shopify extends CI_Controller {

    public $first_init = 0;
    function __construct() {
        //error_reporting(0);
        parent::__construct();        
        $this->load->model('Trackify_DB');
    }
    
    function add_quotes($string) {
        return '"' . implode('","', explode(',', $string)) . '"';
    }
    
    function related_functions($print = 1) {
        $output = <<<EOF
            function getCookie(cname) {
                var name = cname + '=';
                var ca = document.cookie.split(';');
                for(var i = 0; i < ca.length; i++) {
                    var c = ca[i];
                    while (c.charAt(0)==' ') c = c.substring(1);
                    if (c.indexOf(name) == 0) return c.substring(name.length, c.length);
                }
                return '';
            } 

            function createCookie(name, value, days) {
                if (days) {
                    var date = new Date();
                    date.setTime(date.getTime() + (days*24*60*60*1000));
                    var expires = '; expires=' + date.toGMTString();
                }
                else var expires = '';
                document.cookie = name + '=' + value + expires + '; path=/';
            }

            function appendurl(url) {
                 var script = document.createElement('script');
                 script.setAttribute('type', 'text/javascript');
                 script.setAttribute('src', url);
                 document.body.appendChild(script); 
            }
EOF;
    
        $output = trim(preg_replace('/\s+/', ' ', $output));        
        
        if ($print == 1) echo $output;
        else return $output;
    }
    
    function fb_pixel_script($print = 1) {
        $output = "!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','//connect.facebook.net/en_US/fbevents.js');
";
        
        if ($print == 1) echo $output;
        else return $output;
    }

    function pinterest_pixel_script($shop) {
        if (empty($shop["pinterest_pixel"])) return;
        $output = '!function(e){if(!window.pintrk){window.pintrk=function(){window.pintrk.queue.push(Array.prototype.slice.call(arguments))};var n=window.pintrk;n.queue=[],n.version="3.0";var t=document.createElement("script");t.async=!0,t.src=e;var r=document.getElementsByTagName("script")[0];r.parentNode.insertBefore(t,r)}}("https://s.pinimg.com/ct/core.js");        
';
        
        echo $output;
    }

    function get_content_timedata($shop) {
        try {
            $shopify = shopify\client($shop["store_name"], SHOPIFY_APP_API_KEY, $shop["access_token"]);
            $response = $shopify('GET /admin/shop.json?fields=iana_timezone,timezone', array());
            $timezone = $response['iana_timezone'];
        } catch (Exception $e) {
            handle_exception($e);
        }
        date_default_timezone_set($timezone);
        return date("M_j_Y_l_\H\o\u\\r_H");
    }
    
    function trackify_init($shop) {
        $this->related_functions();
        $this->fb_pixel_script();
        $this->pinterest_pixel_script($shop);
    }

    function init_with_customer_info($shop, $pixel_id) {
        try {
            $customer_id = $_GET['customer'];
            $shopify = shopify\client($shop["store_name"], SHOPIFY_APP_API_KEY, $shop["access_token"]);
            $customer = $shopify("GET /admin/customers/{$customer_id}.json?fields=email,first_name,last_name,default_address", array());
            //print_r($customer);

            $em = strtolower($customer['email']);
            $ph = strtolower($customer['default_address']['phone']);
            $fn = strtolower($customer['first_name']);
            $ln = strtolower($customer['last_name']);
            $ct = strtolower($customer['default_address']['city']);
            $st = strtolower($customer['default_address']['province']);
            $zp = strtolower($customer['default_address']['zip']);
            
            $params = "{
                em: \"{$em}\",
                ph: \"{$ph}\",
                fn: \"{$fn}\",
                ln: \"{$ln}\",
                ct: \"{$ct}\",
                st: \"{$st}\",
                zp: \"{$zp}\"
            }";

            $output = $this->init_pixel($pixel_id, $params);
        } catch (Exception $e) {
            $output = $this->init_pixel($pixel_id);
        }
        return $output;
    }

    function init_pixel($pixel, $params = "", $force_init = 0) {
        if ($this->first_init == 0) {
            if ($params == "") {
                $output = "fbq('init', '" . $pixel . "');
";
            } else {
                $output = "fbq('init', '" . $pixel . "', {$params});
";
            }
            $this->first_init = 1;
        } else {
            if ($force_init == 1) {
                if ($params == "") {
                    $output = "fbq('init', '" . $pixel . "');
";
                } else {
                    $output = "fbq('init', '" . $pixel . "', {$params});
";
                }
            } else {
                if ($params == "") {
                    $output = "fbq('init', '" . $pixel . "');
";
                } else {
                    $output = "fbq('init', '" . $pixel . "', {$params});
";
                }
            }
        }
        return $output;
    }

    function global_pixels($shop, $page, $print = 1) {
        $output = "";

        if (!empty($shop["ca"])) {
            if ($page == "checkout") {
                $output .= $this->init_with_customer_info($shop, $shop["ca"]);
            } else {
                $output .= $this->init_pixel($shop["ca"]);
            }
        }
        
        if (!empty($shop["ca_2nd"])) {
            if ($page == "checkout") {
                $output .= $this->init_with_customer_info($shop, $shop["ca_2nd"]);
            } else {
                $output .= $this->init_pixel($shop["ca_2nd"]);
            }
        }

        if (!empty($shop["kpv"])) {            
            $output .=  "fbq('track', '{$shop["kpv"]}', {'value':'0.00', 'currency':'{$shop["currency"]}'});
";
        }

        if (!empty($shop["pinterest_pixel"])) {
            $output .= "pintrk('load','{$shop["pinterest_pixel"]}'); 
pintrk('page', { page_name: '{$page}', page_category: '{$page}' });
";
        }
        
        if ($print == 1) echo $output;
        else return $output;
    }

    function get_cart_code($type) {
        $result = "";

        if ($type == "ajax") {
            $result = "
            if (__st['rtyp'] == 'product' || __st['rtyp'] == 'cart' || __st['rtyp'] == 'collection') {
                var add_btn = document.getElementsByName('add');
                var trackify_addToCart = function() {
                    var source = '" . base_url() . "cart/' + Shopify.shop + '/?pd_id=' + pd_id + '&ajax=1';
                    appendurl(source);
                }
                if (typeof add_btn[0] != 'undefined') {
                    add_btn[0].addEventListener('click', trackify_addToCart, false);
                }
                if (document.getElementById('buy_it_now') != null) document.getElementById('buy_it_now').addEventListener('click', trackify_addToCart, false);
                if (document.getElementById('addToCart') != null) document.getElementById('addToCart').addEventListener('click', trackify_addToCart, false);
                if (document.getElementById('AddToCart') != null) document.getElementById('AddToCart').addEventListener('click', trackify_addToCart, false);
                if (document.getElementById('add') != null) document.getElementById('add').addEventListener('click', trackify_addToCart, false);
                
                var atc_btns = ['add', 'addtocart', 'add_to_cart', 'scn-addtocart', 'add-to-cart'];
                for (j = 0; j < atc_btns.length; j++) {
                    classname = document.getElementsByClassName(atc_btns[j]);
                    for (i = 0; i < classname.length; i++) {
                        classname[i].addEventListener('click', trackify_addToCart, false);
                    }
                }
            } ";
        }

        if ($type == "nonajax") {
            $result = "else if (pageurl.indexOf('/cart') != -1) {
                setTimeout(function() {
                    xmlhttp = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject('Microsoft.XMLHTTP');
                    xmlhttp.onreadystatechange = function () {
                        if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                            var data = JSON.parse(xmlhttp.responseText);
                            var total_price = (data['total_price']/100);
                            var line_items=data['items'];
                            if (Object.keys(line_items).length>0) {
                                var pd_id = '';
                                for (var i=0; i < Object.keys(line_items).length; i++) {
                                    pd_id += line_items[i]['product_id']+',';
                                }
                                pd_id = pd_id.slice(0, -1);
                                var source = '" . base_url() . "cart/' + Shopify.shop + '/?pd_id=' + pd_id + '&total_price=' + total_price + '&ajax=0';
                                appendurl(source);
                            }
                        }
                    };
                    xmlhttp.open('GET', '/cart.js', true);
                    xmlhttp.send();
                }, 10);
            } ";
        }

        return $result;
    }
    
    function buy_now_event($print = 1) {
        $output = "
            if (page != 'checkout') {=
                $('input[name=\"add\"],button[name=\"add\"],#buy_it_now,#addToCart,.addtocart, #AddToCart').click(function(e) {
                    var source = '" . base_url() . "cart/' + Shopify.shop + '/?pd_id=' + pd_id;
                    appendurl(source);
                });
            }
        ";
            
        //$script = trim(preg_replace('/\s+/', ' ', $script));
        if ($print == 1) echo $output;
        else return $output;
    }
    
    function initiate_checkout_event($print = 1) {
        $output = "
            document.querySelector('body').addEventListener('click', function(event) {
                if (typeof event.target.name != 'undefined' && event.target.name.toLowerCase() === 'checkout') {
                    fbq('track', 'InitiateCheckout');
                }
            });
            /*var trackify_initiateCheckout = function() {
                fbq('track', 'InitiateCheckout');
            }
            var checkout_btns = ['checkout'];
            for (i = 0; i < checkout_btns.length; i++) {
                checkout_btn = document.getElementsByName(checkout_btns[i]);
                if (typeof checkout_btn[0] != 'undefined') {
                    checkout_btn[0].addEventListener('click', trackify_initiateCheckout, false);
                }
            }*/";
        if ($print == 1) echo $output;
        else return $output;
    }

    function ptag($store) {
        //if (empty($store)) return;
        $shop = $this->Trackify_DB->get_user_settings($store); 
        if ($shop == false || $shop["fire"] == 0 || empty($shop["ca"])) return;
        
        $this->trackify_init($shop);        
        
        //echo "setTimeout(function() {";
        $product = "
            var pageurl = __st['pageurl'];
            var pd_id = __st['rid'];
            
            var trackify_quickView = function() {
                if (Shopify.shop == 'ridergal.myshopify.com') {
                    pd_id = $(this).attr('href').replace(/\D/g,'');
                } else if (Shopify.shop == 'lindastars.myshopify.com') {
                    pd_id = $(this).attr('data-fancybox-href').replace(/\D/g,'');
                } else if (Shopify.shop == 'waterfowl-is-life.myshopify.com') {
                    pd_id = $(this).attr('data-fancybox-href').replace(/\D/g,'');
                } else {
                    pd_id = $(this).attr('data-fancybox-href').replace(/\D/g,'');
                }
                var source = '" . base_url() . "product/' + Shopify.shop + '/?pd_id=' + pd_id;
                setTimeout(function() {
                    appendurl(source);
                }, " . $shop["vc_delay"] * 1000 . ");
            };

            var classname = document.getElementsByClassName('quick_shop');
            for (var i = 0; i < classname.length; i++) {
                classname[i].addEventListener('click', trackify_quickView, false);
            }
            classname = document.querySelectorAll('.quickview > a');
            for (var i = 0; i < classname.length; i++) {
                classname[i].addEventListener('click', trackify_quickView, false);
            }
            " . $this->initiate_checkout_event(0) . "
            
            if (Shopify.shop == 'anywherelounger.myshopify.com' && __st['p'] == 'home') {
                pd_id = '8211927305';
                var source = '" . base_url() . "product/' + Shopify.shop + '/?pd_id=' + pd_id;
                setTimeout(function() {
                    appendurl(source);     
                }, " . $shop["vc_delay"] * 1000 . ");
            }
            if (__st['rtyp'] == 'product') {
                var source = '" . base_url() . "product/' + Shopify.shop + '/?pd_id=' + pd_id;
                setTimeout(function() {
                    appendurl(source);
                }, " . $shop["vc_delay"] * 1000 . ");
            } else if (__st['rtyp'] == 'collection') {
                collection_name = pageurl.substring(pageurl.indexOf('collections/') + 12);
                collection_name = collection_name.replace(new RegExp('/', 'g'), ',');
                " . $this->global_pixels($shop, "collection", 0) . "
                fbq('track', 'PageView');
                fbq('track', 'ViewCategory', {
                    content_name: 'Collection [' + collection_name + ']',
                });
                ";

        if (!empty($shop["pinterest_pixel"])) {
            $product .= "pintrk('track', 'viewcategory');
                ";
        }
        $product .= "
            } ";

        $output = $product;
        
        if (!empty($shop["ajax"])) {
            $cart = $this->get_cart_code("ajax");
        } else {
            $cart = $this->get_cart_code("nonajax");
        }

        if ($store == "ridergal.myshopify.com" || $store == "lindastars.myshopify.com") {
            $cart = $this->get_cart_code("ajax") . $this->get_cart_code("nonajax");
        }

        //$cart = trim(preg_replace('/\s+/', ' ', $cart));
        $output .= $cart;

        $checkout = "else if (typeof Shopify != 'undefined' && typeof Shopify.checkout != 'undefined') {
                if (Shopify.checkout['token'] != 'undefined' && getCookie(Shopify.checkout['token']) != '1') {
                    var updated_at = new Date(Shopify.checkout.updated_at).getTime();
                    var current_date = new Date().getTime();

                    console.log((current_date - updated_at) / 1000);
                    if ((current_date - updated_at) < 300000) {
                    
                        var line_items=Shopify.checkout['line_items'];
                        var total_price=Shopify.checkout['total_price'];

                        var pd_id = '';
                        for (var i=0; i < Object.keys(line_items).length; i++) {
                            pd_id += line_items[i]['product_id']+',';
                        }
                        var vr_id = '';
                        for(var i=0; i < Object.keys(line_items).length; i++) {
                            vr_id += line_items[i]['variant_id']+',';
                        }
                        
                        pd_id = pd_id.slice(0, -1);
                        vr_id = vr_id.slice(0, -1);
                        var source = '" . base_url() . "checkout/' + Shopify.shop + '/?pd_id=' + pd_id + '&vr_id=' + vr_id + '&total_price=' + total_price + '&customer=' + Shopify.checkout.customer_id;
                        appendurl(source); 
                        createCookie(Shopify.checkout['token'], '1', 90);
                    }
                }
            } else if (__st['rtyp'] != 'collection') {
                " . $this->global_pixels($shop, "other", 0) . "
                fbq('track', 'PageView');
            }
";
        
        $output .= $checkout;
        if (ENVIRONMENT == "production")
            $output = trim(preg_replace('/\s+/', ' ', $output));
        echo $output;
        
        //echo "}, 1000);";
    }

    function product($store) {
        $pids = explode(',', rtrim($_GET['pd_id'], ','));
        $shop = $this->Trackify_DB->get_user_settings($store);
        
        try {
            $shopify = shopify\client($shop["store_name"], SHOPIFY_APP_API_KEY, $shop["access_token"]);
            $product = $shopify('GET /admin/products/' . $pids[0] . '.json', array());
            $price = $product['variants'][0]['price'];

            // log
            $this->Trackify_DB->log($store, "product", $_GET['pd_id'], $product["handle"]);

        } catch (Exception $e) {
            handle_exception($e);
        }
        
        $this->global_pixels($shop, "product");
        $result = $this->niche_tracking($shop, $pids, "product");
        $rr_tag = $result['rr_tag'];
            
        $catalog_line = $value_line = $cont_type = '';
        if (!empty($shop["ca"])) {
            if (!empty($shop["fb_value"])) {
                $value_line = "value:'" . $price . "',
    currency: '" . $shop["currency"] . "',";
                if (!empty($shop["pvtc"])) {
                    $catalog_line = "
    product_catalog_id: '" . $shop["pvtc"] . "',";
                }
            } else {
                $value_line = '';
                if (!empty($shop["pvtc"])) {
                    $catalog_line = "
    product_catalog_id: '" . $shop["pvtc"] . "',";
                }
            }
            
            if ($shop["pes"] === 'pdid') {
                $cont_type = "'product_group'";
            } else {
                $cont_type = "'product'";
            }
        }
        
        $content_timedata = $this->get_content_timedata($shop);
        echo "fbq('track', 'PageView');
fbq('track', 'ViewContent', {
    content_name: 'Trackify ViewContent: {$rr_tag}',
    content_ids: [{$pids[0]}],
    content_type: {$cont_type},
    content_timedata: '{$content_timedata}',
    " . $value_line . $catalog_line . "
});
";
        
        if (!empty($shop["pinterest_pixel"])) {
            echo "pintrk('track', 'pagevisit');
";
        }
    }
    
    function niche_tracking($shop, $pids, $ptype, $print = 1) {        
        
        $total_price = isset($_GET['total_price']) ? $_GET['total_price'] : "0.00";
        $rr_tag = array();
        $track = "";
        try {
            $shopify = shopify\client($shop["store_name"], SHOPIFY_APP_API_KEY, $shop["access_token"]);
            
            if ($shop["track_with"] == "conversion") {
                if ($ptype == "product") {
                    $key1 = "code0";
                    $key2 = "cd0";
                } else if ($ptype == "cart") {
                    $key1 = "code1";
                    $key2 = "cd1";
                } else if ($ptype == "checkout") {
                    $key1 = "code2";
                    $key2 = "cd2";
                    if ($shop["conv_value"] == TRUE) {
                        $price = $total_price;
                    } else {
                        $price = "0.00";
                    }
                }
            } else {
                $key1 = "pixel_id";
                $key2 = "pixel_count";    
            }
            
            $price_sum = 0;
            foreach ($pids as $pid) {

                $product = $shopify('GET /admin/products/' . $pid . '.json?fields=tags,variants', array());
                $price = $product['variants'][0]['price'];
                $tags = explode(',', str_replace(' ', '', $product['tags']));
                
                foreach ($tags as $tag) {
                    if (strpos($tag, 'rr_track') !== false) {
                        $dumb = $this->Trackify_DB->get_tcode_by_tag($tag, $shop["store_name"]);
                        if (!empty($dumb)) {
                            $row = $dumb[0];                        
                            if (!empty($row) && !empty($row[$key1])) {
                                if ($shop["track_with"] == "facebook") {
                                    if ($ptype == "checkout") {
                                        $track .= $this->init_with_customer_info($shop, $row[$key1]);
                                    } else {
                                        $track .= $this->init_pixel($row[$key1]);
                                    }
                                    $price_sum += $price;
                                } else {
                                    $track .= "fbq('track', '{$row[$key1]}', {'value': '{$total_price}', 'currency': '{$shop["currency"]}'});
";
                                    $price_sum += $price;
                                }
                            }
                            $count = $row[$key2] + 1;        
                            $this->Trackify_DB->update_tcode(array($key2 => "{$count}"), array("tags" => "{$tag}", "store" => "{$shop["store_name"]}"));
                        }
                        if (!in_array($tag, $rr_tag)) {
                            $rr_tag[] = $tag;
                        }
                    }
                }
            }
        } catch (Exception $e) {
            //handle_exception($e);
            return array("rr_tag" => "", "total_price" => $price_sum, "track" => $track);
        }        
        
        if ($print == 1) {
            echo $track;
        }
        return array("rr_tag" => implode(" ", $rr_tag), "total_price" => $price_sum, "track" => $track);
    }
    
    function cart($store) {
        $pids = explode(',', rtrim($_GET['pd_id'], ','));
        $shop = $this->Trackify_DB->get_user_settings($store);
        
        try {
            $shopify = shopify\client($shop["store_name"], SHOPIFY_APP_API_KEY, $shop["access_token"]);
            $product = $shopify('GET /admin/products/' . $pids[0] . '.json', array());
            $price = $product['variants'][0]['price'];

            // log
            $this->Trackify_DB->log($store, "cart", $_GET['pd_id'], $product["handle"]);

        } catch (Exception $e) {
            handle_exception($e);
        }        
        
        if ($shop["ajax"] != 1) {
            
        }
        $this->global_pixels($shop, "cart");
        $result = $this->niche_tracking($shop, $pids, "cart");
        $rr_tag = $result['rr_tag'];
        $total_price = $result['total_price'];
        
        $catalog_line = $cont_type = $fb_value_line = '';
        if (!empty($shop["ca"])) {

            /* Set catalog line if Catalog ID is set */
            if (!empty($shop["pvtc"])) {
                $catalog_line = 'product_catalog_id: "' . $shop["pvtc"] . '",';
            }

            /* Build code for using product id or variant id according to settings */
            if ($shop["pes"] === 'pdid') {
                $cont_type = "'product_group'";
            } else {
                $cont_type = "'product'";
            }
        }
        
        if (!empty($shop["fb_value"])) {
            $fb_value_line = "value: '{$price}', currency: '{$shop["currency"]}'";
        }
        
        if ($shop["ajax"] != 1) {
            echo "fbq('track', 'PageView');
";
        }
        
        $content_timedata = $this->get_content_timedata($shop);
        
        echo "fbq('track', 'AddToCart', {
    content_name: 'Trackify Cart: {$rr_tag}',
    content_ids: [" . implode(",", $pids) . "],
    content_type: {$cont_type},
    content_timedata: '{$content_timedata}',
    {$catalog_line}{$fb_value_line}
});
";
        
        if (!empty($shop["atc"])) {
            echo "fbq('track', '{$shop["atc"]}', {'value':'{$price}', 'currency':'{$shop["currency"]}'});
";
        }

        if (!empty($shop["pinterest_pixel"])) {
            echo "pintrk('track', 'addtocart');
";
        }
    }
            
    function checkout($store) {
        // log
        $this->Trackify_DB->log($store, "purchase", $_GET['pd_id']);

        $pids = explode(',', rtrim($_GET['pd_id'], ','));   
        $pd_id = $_GET['pd_id'];       
        $vr_id = $_GET['vr_id'];       
        $total_price = $_GET['total_price'];
        
        $shop = $this->Trackify_DB->get_user_settings($store);
        $this->global_pixels($shop, "checkout");
        $result = $this->niche_tracking($shop, $pids, "checkout");
        $rr_tag = $result['rr_tag'];
        
        $tglobal = $catalog_line = $cont_type = $pd_or_vr_id = '';        
        if (!empty($shop["ca"])) {

            /* Set catalog line if Catalog ID is set */
            if (!empty($shop["pvtc"])) {
                $catalog_line = 'product_catalog_id: "'.$shop["pvtc"].'",';
            }

            /* Build code for using product id or variant id according to settings */
            if ($shop["pes"] === 'pdid') {
                $pd_or_vr_id = $pd_id;
                $cont_type = "'product_group'";
            } else {
                $pd_or_vr_id = $vr_id;
                $cont_type = "'product'";
            }
        }
        
        $content_timedata = $this->get_content_timedata($shop);
        echo "fbq('track', 'PageView');
";
        echo "fbq('track', 'Purchase', {
                content_name: 'Trackify Purchase: " . $rr_tag . "',
                content_ids: [{$pd_or_vr_id}],
                content_type: {$cont_type},
                content_timedata: '{$content_timedata}',
                value: {$total_price},
                currency: '{$shop["currency"]}',
                {$catalog_line}
            });
";
        
        if (!empty($shop["global"])) {
            echo "fbq('track', '{$shop["global"]}', {'value': '{$total_price}', 'currency': '{$shop["currency"]}'});
";
        }

        if (!empty($shop["pinterest_pixel"])) {
            echo "pintrk('track', 'checkout');
";
        }
    }

    // cron
    function update_feeds($store = "") {
        global $db, $sc;
        $db = DB();

        set_time_limit(0);
        $channels = $this->Trackify_DB->get_channels_updated($store);        
        
        foreach ($channels as $channel) {
            $_SESSION["shop"] = $channel["store"];
            echo $channel["store"] . "<br/>";

            $dumb = $this->Trackify_DB->get_settings($channel["store"]);
            $_SESSION['oauth_token'] = $dumb["access_token"];
            $sc = shopify\client($_SESSION['shop'], SHOPIFY_APP_API_KEY, $_SESSION['oauth_token']);

            $this->Trackify_DB->reset_feed_categories($channel["id"]);
            data_map($channel["id"]);
            $this->Trackify_DB->update_channels(array("action_required" => 0), array("store" => $channel["store"]));
        }
    }

    function feeds($id) {
        $id = base64_decode(@pack("H*", $id));
        $channel = $this->Trackify_DB->get_channels_by_id($id);
        //echo $channel["feed_file"];
        if (!empty($channel) && !empty($channel[0]["feed_file"])) {
            redirect($channel[0]["feed_file"] . "?" . time());    
        } else {
            echo "Something went wrong";
        }
    }

    function ocu() {

        $standard_events = array("InitiateCheckout", "AddPaymentInfo", "Purchase", "ViewContent", "AddToCart");
        
        //https://app.redretarget.com/sapp/ocu?store_name=moc-store.myshopify.com&event_type=FirstUpsellProductPurchase&product_ids%5B%5D=22856989959&value=18.9&currency=CAD&num_items=1
        //http://88.80.131.147/redtargeting/sapp/MY_Shopify/ocu?store_name=moc-store.myshopify.com&event_type=FirstUpsellProductPurchase&product_ids%5B%5D=22856989959&value=18.9&currency=CAD&num_items=1
        
        $store = $_GET["store_name"];
        if (!$this->__is_trackify_installed($store)) {
            echo "Error: Trackify is not installed.";
            return;
        }

        $shop = $this->Trackify_DB->get_user_settings($store);
        $access_token = $shop["access_token"];

        $vid = (!empty($_GET['product_ids'])) ? $_GET["product_ids"] : "";

        $pid = (!empty($vid)) ? get_pid_from_vid($store, $access_token, $vid) : "";
        
        $event_type = $_GET["event_type"];
        
        $content_ids    = (!empty($pid)) ? "content_ids: [" . implode(',', $pid) . "]," : "";
        $content_type   = (!empty($_GET["content_type"])) ? "content_type: '" . $_GET["content_type"] . "'," : "content_type: 'product_group',";
        $value          = (!empty($_GET["value"])) ? "value: {$_GET["value"]}," : "";
        $num_items      = (!empty($_GET["num_items"])) ? "num_items: {$_GET["num_items"]}," : "";
        $currency       = (!empty($_GET["currency"])) ? "currency: '" . $_GET["currency"] . "'" : "";
        
        if ($event_type == "AddPaymentInfo") {
            $content_type = "";
        }

        $track = $this->global_pixels($shop, "ocu", 0);        
        $track .= "fbq('track', 'PageView');
";
        
        $_GET['total_price'] = (!empty($_GET["value"])) ? $_GET["value"] : 0;

        if (!empty($pid)) {
            $result = $this->niche_tracking($shop, $pid, "checkout", 0);
            $rr_tag = $result['rr_tag'];
            $track .= $result['track'];
        }

        if (!empty($rr_tag)) {
            $content_name = "content_name: 'Trackify " . $event_type . ": {$rr_tag}',";
        } else {
            $content_name = "content_name: 'Trackify " . $event_type . "',";
        }

        $content_timedata = "content_timedata: '" . $this->get_content_timedata($shop) . "',";
        if (in_array($event_type, $standard_events)) {
            $track .= "fbq('track', '" . $event_type . "', {
    {$content_name}
    {$content_ids}
    {$content_type}
    {$content_timedata}
    {$value}
    {$num_items}
    {$currency}
});
";    
        } else {
            $content_location = $event_type;
            $track .= "fbq('track', 'Purchase', {
    {$content_name}
    content_location: '{$event_type}',
    {$content_ids}
    {$content_type}
    {$content_timedata}
    {$value}
    {$num_items}
    {$currency}
});
";
        }

        echo $track;
        if (!empty($pid)) {
            $this->Trackify_DB->log($store, "ocu", implode(",", $pid), $track);    
        } else {
            $this->Trackify_DB->log($store, "ocu", "", $track);
        }
        
    }

    function pixel() {

        $standard_events = array("InitiateCheckout", "AddPaymentInfo", "Purchase", "ViewContent", "AddToCart");
        
        //https://app.redretarget.com/sapp/ocu?store_name=moc-store.myshopify.com&event_type=FirstUpsellProductPurchase&product_ids%5B%5D=22856989959&value=18.9&currency=CAD&num_items=1
        //http://88.80.131.147/redtargeting/sapp/MY_Shopify/ocu?store_name=moc-store.myshopify.com&event_type=FirstUpsellProductPurchase&product_ids%5B%5D=22856989959&value=18.9&currency=CAD&num_items=1
        
        $store = $_GET["store_name"];
        if (!$this->__is_trackify_installed($store)) {
            echo "Error: Trackify is not installed.";
            return;
        }

        $shop = $this->Trackify_DB->get_user_settings($store);
        $access_token = $shop["access_token"];

        $vid = (!empty($_GET['product_ids'])) ? $_GET["product_ids"] : "";

        //$pid = (!empty($vid)) ? get_pid_from_vid($store, $access_token, $vid) : "";
        $pid = (!empty($vid)) ? $vid : "";
        
        $event_type = $_GET["event_type"];
        
        $content_ids    = (!empty($pid)) ? "content_ids: [" . implode(',', $pid) . "]," : "";
        $content_type   = (!empty($_GET["content_type"])) ? "content_type: '" . $_GET["content_type"] . "'," : "content_type: 'product_group',";
        $value          = (!empty($_GET["value"])) ? "value: {$_GET["value"]}," : "";
        $num_items      = (!empty($_GET["num_items"])) ? "num_items: {$_GET["num_items"]}," : "";
        $currency       = (!empty($_GET["currency"])) ? "currency: '" . $_GET["currency"] . "'" : "";
        
        if ($event_type == "AddPaymentInfo") {
            $content_type = "";
        }

        $this->trackify_init($shop);
        
        $track = $this->global_pixels($shop, "ocu", 0);        
        $track .= "fbq('track', 'PageView');
";

        $_GET['total_price'] = (!empty($_GET["value"])) ? $_GET["value"] : 0;

        if (!empty($pid)) {
            $result = $this->niche_tracking($shop, $pid, "checkout", 0);
            $rr_tag = $result['rr_tag'];
            $track .= $result['track'];
        }

        if (!empty($rr_tag)) {
            $content_name = "content_name: 'Trackify " . $event_type . ": {$rr_tag}',";
        } else {
            $content_name = "content_name: 'Trackify " . $event_type . "',";
        }

        $content_timedata = "content_timedata: '" . $this->get_content_timedata($shop) . "',";
        if (in_array($event_type, $standard_events)) {
            $track .= "fbq('track', '" . $event_type . "', {
    {$content_name}
    {$content_ids}
    {$content_type}
    {$content_timedata}
    {$value}
    {$num_items}
    {$currency}
});
";    
        } else {
            $content_location = $event_type;
            $track .= "fbq('track', 'Purchase', {
    {$content_name}
    content_location: '{$event_type}',
    {$content_ids}
    {$content_type}
    {$content_timedata}
    {$value}
    {$num_items}
    {$currency}
});
";
        }

        echo $track;

        $this->Trackify_DB->log($store, "ocu", implode(",", $pid), $track);
    }

    function __is_trackify_installed($store = "") {
        if (!empty($store) && $this->Trackify_DB->is_store_registered($store)) {
            return 1;            
        } else {
            return 0;
        }
    }

    function is_trackify_installed($store = "") {
        header("Content-Type: application/json");
        
        if (!empty($store) && $this->Trackify_DB->is_store_registered($store)) {
            echo json_encode(array("status" => 1, "message" => "Trackify is installed."));
            
        } else {
            echo json_encode(array("status" => 0, "message" => "Trackify is not installed."));
        }
        die();
    }

    function cron_test() {
        $this->Trackify_DB->cron_test();
    }
}
?>
