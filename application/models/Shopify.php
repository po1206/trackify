<?php

class Shopify extends CI_Model {
    
    function update_store($data) {
    
        global $sc;
        $parent_url = base_url();
        $store = $_SESSION["shop"];
        
        // reinstall begin
        $theme = $sc('GET /admin/themes.json', array("role" => "main"));
   
        /*
        foreach ($theme as $val) {
            if ($val['previewable'] == 1) {

                $cartify = $sc('GET /admin/themes/' . $val['id'] . '/assets.json?asset[key]=templates/cart.liquid&theme_id=' . $val['id'], array());
                if (strpos($cartify['value'], '"trackify2"') == true) {
                    $myfile = fopen("cart.liquid", "w") or die("unable");
                    $txt = str_replace('{% include "trackify2" %}', '', $cartify['value']);
                    fwrite($myfile, $txt);
                    fclose($myfile);

                    $cart = $sc('PUT /admin/themes/'.$val['id'].'/assets.json', array('asset' => array('key' => 'templates/cart.liquid', 'src' => $par_url . 'cart.liquid')));
                }

                $proify = $sc('GET /admin/themes/' . $val['id'] . '/assets.json?asset[key]=templates/product.liquid&theme_id=' . $val['id'], array());
                if (strpos($proify['value'], '"trackify2"') == true || strpos($proify['value'], '"ajax-trackify"') == true) {
                    $myfile2 = fopen("product.liquid", "w") or die("unable");
                    $txt2 = str_replace('{% include "ajax-trackify" %}', '', $proify['value']);
                    $txt3 = str_replace('{% include "trackify2" %}', '', $txt2);
                    fwrite($myfile2, $txt3);
                    fclose($myfile2);
                       
                    $product = $sc('PUT /admin/themes/' . $val['id'] . '/assets.json', array('asset' => array('key' => 'templates/product.liquid', 'src' => $par_url . 'product.liquid')));
                }

                $themify = $sc('GET /admin/themes/' . $val['id'] . '/assets.json?asset[key]=layout/theme.liquid&theme_id=' . $val['id'], array());
                if (strpos($themify['value'], '"trackify2"') == false) {                    
                    if (strpos($themify['value'], '</body>') !== false) {
                        $myfile = fopen("theme.liquid", "w") or die("unable");
                        $theme = str_replace('</body>', '{% include "trackify2" %}</body>', $themify['value']);
                        fwrite($myfile, $theme);
                        fclose($myfile);
                    } else {
                        $myfile = fopen("theme.liquid", "w") or die("unable");
                        $theme = str_replace('{{ content_for_layout }}', '{% include "trackify2" %}{{ content_for_layout }}', $themify['value']);
                        fwrite($myfile, $theme);
                        fclose($myfile);
                    }

                    $cart = $sc('PUT /admin/themes/' . $val['id'] . '/assets.json', array('asset' => array('key' => 'layout/theme.liquid', 'src' => $par_url.'theme.liquid')));
                } else {
                    $myfile = fopen("theme.liquid", "w") or die("unable");
                    $theme = str_replace('{% include "trackify2" %}', '', $themify['value']);
                    $theme1 = str_replace('</body>', '{% include "trackify2" %}</body>', $theme);
                    fwrite($myfile, $theme1);
                    fclose($myfile);
                    $cart = $sc('PUT /admin/themes/' . $val['id'] . '/assets.json', array('asset' => array('key' => 'layout/theme.liquid', 'src' => $par_url.'theme.liquid')));
                }
            }
        }
        */
        
        $script = $sc('GET /admin/script_tags.json', array());
        foreach ($script as $val) {
            $delete = $sc('DELETE /admin/script_tags/' . $val['id'] . '.json');
        }

        $script = $sc('POST /admin/script_tags.json', array('script_tag' => array('event' => 'onload', 'src' => $parent_url . 'ptag/' . $store )));

        $shop = $sc('GET /admin/shop.json', array());
        $currency = $shop['currency'];
        $this->db->query("UPDATE tbl_usersettings SET currency='$currency' WHERE store_name = '$store'");            

        $hook = $sc('GET /admin/webhooks/count.json?topic=app/uninstalled', array());
        if ($hook < 1) {
            $hook2 = $sc('POST /admin/webhooks.json', array('webhook' => array('topic' => 'app/uninstalled', 'address' => $parent_url . 'Auth/uninstall/' . $store, 'format' => 'json' )));
        }
        
        $hook = $sc('GET /admin/webhooks/count.json?topic=shop/update', array());
        if ($hook < 1) {
            $hook3 = $sc('POST /admin/webhooks.json', array('webhook' => array('topic' => 'shop/update', 'address' => $parent_url . 'Auth/update_shop/' . $store, 'format' => 'json' )));
        }
        
        $theme = $sc('GET /admin/themes.json', array('role' => 'main'));
        foreach ($theme as $val) {
            if ($val['previewable'] == 1) {
                //$trackify = $sc('PUT /admin/themes/' . $val['id'] . '/assets.json', array('asset' => array('key' => 'snippets/trackify2.liquid', 'value' => '')));
            }
        }
        
        
    }
}    
  
?>