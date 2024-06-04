<?php

use Facebook\Facebook;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;

use FacebookAds\Api;
use FacebookAds\Object\AdAccount;
use FacebookAds\Object\Business;
use FacebookAds\Object\CustomAudience;
use FacebookAds\Object\AdsPixel;
use FacebookAds\Object\Fields\CustomAudienceFields;
use FacebookAds\Object\Values\CustomAudienceSubtypes;
use FacebookAds\Object\ProductFeed;
use FacebookAds\Object\Fields\ProductFeedScheduleFields;
use FacebookAds\Object\Fields\ProductFeedFields;
use FacebookAds\Object\ProductFeedUpload;
use FacebookAds\Object\Fields\ProductFeedUploadFields;
use FacebookAds\Object\ProductCatalog;
use FacebookAds\Object\Fields\ProductCatalogFields;

//convert an ISO8601 date to a different format
function vm_date($date) { // 
    $time = strtotime($date);
    $fixed = date('Y-m-d H:i:s', $time);
    return $fixed;
}

function getFacebookLoginURL($redirect = '') {
    $_SESSION["fb"] = new Facebook([
            'app_id' => FB_APP_ID,
            'app_secret' => FB_APP_SECRET,
        ]);
    $helper = $_SESSION["fb"]->getRedirectLoginHelper();
    $permissions = ['ads_management', 'email', 'ads_read', 'business_management'];
    $loginUrl = $helper->getLoginUrl($redirect, $permissions);
    
    return $loginUrl;
}

function getFacebookAccessToken() {
    $helper = $_SESSION["fb"]->getRedirectLoginHelper();

    try {
      $accessToken = $helper->getAccessToken();
    } catch(FacebookResponseException $e) {
      // When Graph returns an error
      echo 'Graph returned an error: ' . $e->getMessage();
      handleException($e);      
      exit;
    } catch(FacebookSDKException $e) {
      // When validation fails or other local issues
      echo 'Facebook SDK returned an error: ' . $e->getMessage();
      exit;
    }

    if (! isset($accessToken)) {
      if ($helper->getError()) {
        header('HTTP/1.0 401 Unauthorized');
        echo "Error: " . $helper->getError() . "\n";
        echo "Error Code: " . $helper->getErrorCode() . "\n";
        echo "Error Reason: " . $helper->getErrorReason() . "\n";
        echo "Error Description: " . $helper->getErrorDescription() . "\n";
      } else {
        header('HTTP/1.0 400 Bad Request');
        echo 'Bad request';
      }
      exit;
    }
    
    // The OAuth 2.0 client handler helps us manage access tokens
    $oAuth2Client = $_SESSION["fb"]->getOAuth2Client();

    // Get the access token metadata from /debug_token
    $tokenMetadata = $oAuth2Client->debugToken($accessToken);

    // Validation (these will throw FacebookSDKException's when they fail)
    $tokenMetadata->validateAppId(FB_APP_ID); // Replace {app-id} with your app id
    // If you know the user ID this access token belongs to, you can validate it here
    //$tokenMetadata->validateUserId('123');
    $tokenMetadata->validateExpiration();

    if (! $accessToken->isLongLived()) {
      // Exchanges a short-lived access token for a long-lived one
      try {
        $accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
      } catch (FacebookSDKException $e) {
        echo "<p>Error getting long-lived access token: " . $helper->getMessage() . "</p>\n\n";
        exit;
      }
    }

    return (string) $accessToken;
}

function getProfile($token) {
    try {        
        $response = $_SESSION["fb"]->get('/me?fields=id,name,email,picture,timezone', $token);
    } catch(FacebookResponseException $e) {
        echo 'Graph returned an error: 102' . $e->getMessage();
        handleException($e);
        exit;
    } catch(FacebookSDKException $e) {
        echo 'Facebook SDK returned an error: ' . $e->getMessage();
        exit;
    }

    $user = $response->getGraphUser();
    
    return $user;
}

function checkToken($endpoint) {
    if (empty($_SESSION["facebook_access_token"])) {
        $redirect = base_url() . "MY_Facebook/oauth_finish/?redirect={$endpoint}";
        $data['url'] = getFacebookLoginURL($redirect);
        redirect($data['url']);
    }
}

function getBusinesses($token) {
    try {
        $response = $_SESSION["fb"]->get('/me/businesses', $token);
        $businesses = $response->getGraphEdge();
    } catch (Exception $e) {
        $code = $e->getCode();
        $message = $e->getMessage();
        if ($code == 200 && strpos($message, "Requires extended permission: business_management") !== false) {
            $_SESSION["message_display"] = "You will need to re-authoize Trackify on FB. To re-authorize:<br/>
1. Go to your FB app settings: <a href='https://www.facebook.com/settings?tab=applications'>https://www.facebook.com/settings?tab=applications</a> and remove Trackify.<br/>
2. Please log out from Trackify app and open it again on Shopify apps.";
        } else {
            $_SESSION["message_display"] = $message;
        }
        return array();
    }
    
    return $businesses->asArray();
}

function getMyBusiness($token, $username) {
    $businesses = getBusinesses($token);
    
    foreach ($businesses as $business) {
        if ($business['name'] == $username) {
            return $business;
        }
    }
    
    return $businesses[0];
}

function getMyAdAccounts($facebook_user_id, $token) {
    $response = $_SESSION["fb"]->get('/me/adaccounts?fields=id,account_id,name,currency,timezone_name,users,owner&limit=255', $token);
    $ad_accounts = $response->getGraphEdge();
    return $ad_accounts;
    
    $result = [];
    foreach ($ad_accounts as $ad_account) {        
        foreach ($ad_account["users"] as $user) {
            if ($user["role"] == 1001 && $user["id"] == $facebook_user_id) {
                $result[] = $ad_account;
                break;
            }
        }
    }
    return $result;
}

function getAdAccountsByBusiness($business_id, $token) {
    $response = $_SESSION["fb"]->get("/{$business_id}/adaccounts?fields=id,name,adspixels&limit=255", $token);
    $ad_accounts = $response->getGraphEdge();
    $r = $ad_accounts->asArray();
    $result = array();
    if (!empty($r)) {
        for ($i = 0; $i < count($r); $i++) {
            if ($r[$i]["name"] == "") $r[$i]["name"] = $r[$i]["id"];
            
            if (isset($r[$i]["adspixels"])) {
                $r[$i]["adspixels"][0] = $r[$i]["adspixels"][count($r[$i]["adspixels"]) - 1];
                $r[$i]["pixel_id"] = $r[$i]["adspixels"][count($r[$i]["adspixels"]) - 1]["id"];
                $result[] = array("adaccount" => array("id" => $r[$i]["id"], "account_id" => $r[$i]["account_id"], "name" => $r[$i]["name"]), "pixel_id" => $r[$i]["pixel_id"]);
            }
        }
    }
    return $result;
}

function getMyAdAccountsWithPixels($facebook_user_id, $token) {
    try {
        $response = $_SESSION["fb"]->get('/me/adaccounts?fields=id,account_id,name,currency,timezone_name,users,owner&limit=255', $token);
        $ad_accounts = $response->getGraphEdge()->asArray();
        $result = [];

        Api::init(
                FB_APP_ID,
                FB_APP_SECRET,
                $_SESSION["facebook_access_token"]        
            ); 

        foreach ($ad_accounts as $ad_account) {
            $a = new AdAccount($ad_account["id"]);
            $pixels = $a->getAdsPixels();
            $pixels->end();
            
            if (!empty($pixels->current())) {
                $default_pixel_id = $pixels->current()->id;
            } else {
                $default_pixel_id = "";
            }
            if ($default_pixel_id != "")
                $result[] = array("adaccount" => $ad_account, "pixel_id" => $default_pixel_id);
        }
    } catch (Exception $e) {
        redirect("install");
    }
    return $result;
}

function getMyOwnAdAccountsWithPixels($facebook_user_id, $token) {
    try {
        
        $result = [];

        Api::init(
                FB_APP_ID,
                FB_APP_SECRET,
                $_SESSION["facebook_access_token"]
            ); 

        $response = $_SESSION["fb"]->get('/me/adaccounts?fields=id,account_id,name,currency,timezone_name,users,owner&limit=255', $token);
        $dumb = $response->getGraphEdge()->asArray();
        
        foreach ($dumb as $row) {
            foreach ($row["users"] as $key => $user) {
                if ($user["role"] == 1001 && $user["id"] == $facebook_user_id && $key == (count($row["users"]) - 1)) {
                    $ad_accounts[] = $row;
                }
            }
        }

        foreach ($ad_accounts as $ad_account) {
            $a = new AdAccount($ad_account["id"]);
            $pixels = $a->getAdsPixels();
            $pixels->end();
            
            if (!empty($pixels->current())) {
                $default_pixel_id = $pixels->current()->id;
            } else {
                $default_pixel_id = "";
            }
            if ($default_pixel_id != "")
                $result[] = array("adaccount" => $ad_account, "pixel_id" => $default_pixel_id);
        }
    } catch (Exception $e) {
        redirect("install");
    }
    return $result;
}

function createCustomAudience($data) {
    $default_pixel_id = "";
    $ids = array();

    try {
        Api::init(
            FB_APP_ID,
            FB_APP_SECRET,
            $_SESSION["facebook_access_token"]        
        );        
        
        $adaccount_id = $data['ad_account'];
        if (empty($adaccount_id)) return;
        
        $ad_account = new AdAccount($adaccount_id);
        $pixels = $ad_account->getAdsPixels();

        if (!empty($pixels->current())) {
            $default_pixel_id = $pixels->current()->id;
        } else {
            $error = "Couldn't get pixel ID for your ad account. Please create pixel ID for your ad account in Ad Manager.";
            throw new Exception($error);
        }

        foreach ($data["eventtype"] as $eventtype) {
            foreach ($data["custom_look_back_days"] as $custom_look_back_day) {
                if ($eventtype == "ViewContent") {
                    $et = "VC";
                } else if ($eventtype == "AddToCart") {
                    $et = "ATC";
                } else {
                    $et = $eventtype;
                }
                $audience_name = $data["custom_audience_name"] . " - " . $et . " " . $custom_look_back_day . " days";

                if ($data["filtertype"] == "url") {
                    $rule = '{"url": {"i_contains": "' . $data["filtervalue"] . '"}}';
                } else {
                    $rule = '{"and": [
                                {"event": {"i_contains": "' . $eventtype . '"}},
                                {"' . $data["filtertype"] . '": {"i_contains": "' . $data["filtervalue"] . '"}}
                            ]}';
                }
                $rule = json_decode($rule, true);

                $c = new CustomAudience(null, $adaccount_id);
                $c->setData(array(
                    CustomAudienceFields::PIXEL_ID => $default_pixel_id,
                    CustomAudienceFields::NAME => $audience_name,
                    CustomAudienceFields::SUBTYPE => CustomAudienceSubtypes::WEBSITE,
                    CustomAudienceFields::RETENTION_DAYS => $custom_look_back_day,      
                    CustomAudienceFields::RULE => $rule,
                    CustomAudienceFields::PREFILL => true,
                ));
                
                $dumb = $c->create();
                $dumb = $dumb->getData();
                $ids[] = $dumb["id"];
            }
        }
	   	
        
    } catch (Exception $e) {
        handleException($e);
    }
    return array("ad_account" => $adaccount_id, "pixel" => $default_pixel_id, "audiences" => $ids);
}

function createAdvancedCustomAudience($data) {
    $default_pixel_id = "";
    $ids = array();

    try {
        Api::init(
            FB_APP_ID,
            FB_APP_SECRET,
            $_SESSION["facebook_access_token"]
        );
        
        $adaccount_id = $data['ad_account'];
        if (empty($adaccount_id)) return;

        $default_pixel_id = $data['pixel_id'];
        
        $ad_account = new AdAccount($adaccount_id);
        
        for ($i = 0; $i < count($data["filtertype"]); $i++) {
            $rule = array();
            $rule_url = array();
            $values = explode(", ", $data["filtervalue"][$i]);
            for ($j = 0; $j < count($values); $j++) {
                $value = $values[$j];

                if ($data["filtertype"][$i] == "url") {
                    $rule_url["or"][$j]["url"][$data["compare"][$i]] = $value;
                } else if ($data["filtertype"][$i] == "custom_parameter") {
                    $rule["or"][$j][$data["filtertype"][$i]][$data["compare"][$i]] = $value;
                } else {                    
                    $rule["or"][$j][$data["filtertype"][$i]][$data["compare"][$i]] = $value;
                }
            }
            if (!empty($rule)) $rules[] = $rule;
            if (!empty($rule_url)) $rules_url[] = $rule_url;
        }
        foreach ($data["eventtype"] as $eventtype) {
            foreach ($data["custom_look_back_days"] as $custom_look_back_day) {
                if ($eventtype == "ViewContent") {
                    $et = "VC";
                } else if ($eventtype == "AddToCart") {
                    $et = "ATC";
                } else {
                    $et = $eventtype;
                }
                $audience_name = $data["custom_audience_name"] . " - " . $et . " " . $custom_look_back_day . " days";
                                
                $event_rule["event"]["i_contains"] = $eventtype;

                if (!empty($rules)) {
                    if ($data["rule_type"] == "and") {
                        $rules[count($rules)] = $event_rule;
                        $final_rules = $rules;
                    } else {
                        if (count($rules) == 1) {
                            $rules[count($rules)] = $event_rule;
                            $final_rules = $rules;
                        } else {
                            $final_rules = array(0 => $event_rule, 1 => array($data["rule_type"] => $rules));    
                        }
                    }

                    $ca_rule = array("and" => $final_rules);
                } else {
                    $final_rules = $event_rule;
                    $ca_rule = array("and" => $final_rules);
                }
                if (!empty($rules_url)) {
                    if (!isset($rules)) {
                        $ca_rule = array($data["rule_type"] => $rules_url);
                    } else {
                        if (count($rules_url) == 1) {
                            $ca_rule = array("or" => array(0 => $rules_url[0], 1 => $ca_rule));
                        } else {
                            $ca_rule = array("or" => array(0 => array("or" => $rules_url), 1 => $ca_rule));
                        }
                    }
                }

                $c = new CustomAudience(null, $adaccount_id);
                $c->setData(array(
                    CustomAudienceFields::PIXEL_ID => $default_pixel_id,
                    CustomAudienceFields::NAME => $audience_name,
                    CustomAudienceFields::SUBTYPE => CustomAudienceSubtypes::WEBSITE,
                    CustomAudienceFields::RETENTION_DAYS => $custom_look_back_day,
                    CustomAudienceFields::RULE => $ca_rule,
                    CustomAudienceFields::PREFILL => true,
                    //'data_source' => array('sub_type' => 'WEB_PIXEL_COMBINATION_EVENTS', 'creation_params' => array('device_type' => 'mobile')),
                ));
                
                $dumb = $c->create();
                $dumb = $dumb->getData();
                $ids[] = $dumb["id"];
            }
        }
    } catch (Exception $e) {
        print_r($e); die();
        handleException($e);
    }
    return array("ad_account" => $adaccount_id, "pixel" => $default_pixel_id, "audiences" => $ids);
}

function createLookalikeAudience($data) {
    $default_pixel_id = "";
    $ids = array();

    try {
        Api::init(
            FB_APP_ID,
            FB_APP_SECRET,
            $_SESSION["facebook_access_token"]        
        );        
        
        $adaccount_id = $data['ad_account'];
        if (empty($adaccount_id)) return;
        
        $ad_account = new AdAccount($adaccount_id);
        $pixels = $ad_account->getAdsPixels();

        if (!empty($pixels->current())) {
            $default_pixel_id = $pixels->current()->id;
        } else {
            $error = "Couldn't get pixel ID for your ad account. Please create pixel ID for your ad account in Ad Manager.";
            throw new Exception($error);
        }

        $custom_audience = new CustomAudience($data["audiences"]);
        $custom_audience->read(array("name"));        

        foreach ($data["audience_size"] as $audience_size) {
            $audience_name = "Lookalike (" . $data["country"] . ", " . $audience_size . "%) - " . $custom_audience->name;
        
            $c = new CustomAudience(null, $adaccount_id);
            $c->setData(array(
                /*CustomAudienceFields::PIXEL_ID => $default_pixel_id,*/
                CustomAudienceFields::NAME => $audience_name,
                CustomAudienceFields::SUBTYPE => CustomAudienceSubtypes::LOOKALIKE,
                CustomAudienceFields::ORIGIN_AUDIENCE_ID => $data["audiences"],      
                CustomAudienceFields::LOOKALIKE_SPEC => array(
                    'type' => 'custom_ratio',
                    'ratio' => $audience_size / 100,
                    'country' => $data["country"],
                ),
            ));       
            
            $dumb = $c->create();
            $dumb = $dumb->getData();
            $ids[] = $dumb["id"];
        }
    } catch (Exception $e) {
        handleException($e);
    }
    return array("ad_account" => $adaccount_id, "pixel" => $default_pixel_id, "audiences" => $ids);
}

function createLAAs($data) {
    $default_pixel_id = "";
    $ids = array();

    try {
        Api::init(
            FB_APP_ID,
            FB_APP_SECRET,
            $_SESSION["facebook_access_token"]        
        );        
        
        $adaccount_id = $data['ad_account'];
        if (empty($adaccount_id)) return;
        
        $ad_account = new AdAccount($adaccount_id);
        $pixels = $ad_account->getAdsPixels();

        if (!empty($pixels->current())) {
            $default_pixel_id = $pixels->current()->id;
        } else {
            $error = "Couldn't get pixel ID for your ad account. Please create pixel ID for your ad account in Ad Manager.";
            throw new Exception($error);
        }

        foreach ($data["audiences"] as $audience_id) {
            $custom_audience = new CustomAudience($audience_id);
            $custom_audience->read(array("name"));

            foreach ($data["audience_size"] as $audience_size) {
                $audience_name = "Lookalike (" . $data["country"] . ", " . $audience_size . "%) - " . $custom_audience->name;
            
                $c = new CustomAudience(null, $adaccount_id);
                $c->setData(array(
                    /*CustomAudienceFields::PIXEL_ID => $default_pixel_id,*/
                    CustomAudienceFields::NAME => $audience_name,
                    CustomAudienceFields::SUBTYPE => CustomAudienceSubtypes::LOOKALIKE,
                    CustomAudienceFields::ORIGIN_AUDIENCE_ID => $audience_id,      
                    CustomAudienceFields::LOOKALIKE_SPEC => array(
                        'type' => 'custom_ratio',
                        'ratio' => $audience_size / 100,
                        'country' => $data["country"],
                    ),
                ));       
                
                $dumb = $c->create();
                $dumb = $dumb->getData();
                $ids[] = $dumb["id"];
            }
        }
    } catch (Exception $e) {
        handleException($e);
    }
    return array("ad_account" => $adaccount_id, "pixel" => $default_pixel_id, "audiences" => $ids);
}

function deleteCustomAudience($ad_account_id, $audience_id) {
    try {
        Api::init(
            FB_APP_ID,
            FB_APP_SECRET,
            $_SESSION["facebook_access_token"]
        );

        $audience = new CustomAudience($audience_id, $ad_account_id);
        $audience->delete();
    } catch (Exception $e) {
        handleException($e);
    }
}

function getCustomAudiences($ad_account_id) {
    try {
        $token = $_SESSION["facebook_access_token"];
        $response = $_SESSION["fb"]->get("/{$ad_account_id}/customaudiences?fields=id,name,subtype,approximate_count,operation_status,time_created,time_updated&limit=9999999", $token);
        $results = $response->getGraphEdge();
    } catch (Exception $e) {
        handleException($e);
        return array();
    }

    return ($results->asArray());
}

function getPixelsByAdAccount($ad_account_id) {
    try {
        $token = $_SESSION["facebook_access_token"];
        $response = $_SESSION["fb"]->get("/{$ad_account_id}/adspixels?fields=id,name&limit=999", $token);
        $results = $response->getGraphEdge();

    } catch (Exception $e) {
        handleException($e);
        return array();
    }

    return ($results->asArray());
}

function getProductCatalogs($business_id) {    
    try {
        $token = $_SESSION["facebook_access_token"];
        $response = $_SESSION["fb"]->get("/{$business_id}/product_catalogs?fields=id,name,product_count,feed_count,business,product_feeds.limit(999999){id,name,created_time,product_count,latest_upload{start_time,end_time,errors},schedule}&limit=9999999", $token);
        $results = $response->getGraphEdge()->asArray();
    } catch (Exception $e) {
        handleException($e);
        return array();
    }

    return $results;
}

function getProductFeeds($catalog_id) {
    try {
        $token = $_SESSION["facebook_access_token"];
        $response = $_SESSION["fb"]->get("/{$catalog_id}/product_feeds?fields=id,name,created_time,product_count,latest_upload{start_time,end_time,errors},schedule&limit=9999999", $token);
        $results = $response->getGraphEdge();
    } catch (Exception $e) {
        return array();
    }

    return ($results->asArray());
}

function getObjectName($obj_id) {
    try {
        $token = $_SESSION["facebook_access_token"];
        $response = $_SESSION["fb"]->get("/{$obj_id}?fields=id,name", $token);
        $results = $response->getGraphNode();
    } catch (Exception $e) {
        handleException($e);
        return "";
    }

    return $results["name"];
}

function createProductCatalog($business_id) {    
    try {
        Api::init(
            FB_APP_ID,
            FB_APP_SECRET,
            $_SESSION["facebook_access_token"]
        );

        $product_catalog = new ProductCatalog(null, $business_id);
        $product_catalog->setData(array(
            ProductCatalogFields::NAME => $_POST["catalog_name"],
        ));
        $product_catalog->create();

        $product_catalog->setExternalEventSources(array(
            $_POST["pixel_id"]
        ));
        
        $catalog_id = $product_catalog->id;
        $product_feed = new ProductFeed(null, $catalog_id);
        $product_feed->setData(array(
            ProductFeedFields::NAME => $_POST['feed_name'],
            ProductFeedFields::SCHEDULE => array(
                ProductFeedScheduleFields::INTERVAL => 'DAILY',
                ProductFeedScheduleFields::URL => $_POST['feed_url'],
                ProductFeedScheduleFields::HOUR => 2,
            ),
        ));

        $result = $product_feed->create();
        
        $feed_id = $result->id;
        $feed_upload = new ProductFeedUpload(null, $feed_id);

        $date = new DateTime();
        $start_time = $date->format(DateTime::ISO8601);
        $feed_upload->setData(array(
            ProductFeedUploadFields::URL => $_POST['feed_url'],
            ProductFeedUploadFields::START_TIME => $start_time,
        ));
        
        $dumb = $feed_upload->create();
    } catch (Exception $e) {
        handleException($e);
        return false;
    }
    
    return $feed_id;
}

function handleException($e) {
    //echo "<div class='vm_error alert-danger' style='padding-left: 230px; margin-top: 60px'>";
    $_SESSION['message_display'] = "Error Code: " . $e->getCode() . "<br/>";
    $_SESSION['message_display'] .= "Error Message: " . $e->getMessage() . "<br/>";
    //echo "Error User Title: " . $e->getErrorUserTitle() . "\n";
    //echo "</div>";
}

function test123() {
    try {
        Api::init(
            FB_APP_ID,
            FB_APP_SECRET,
            $_SESSION["facebook_access_token"]
        );

        $custom_audience = new CustomAudience("6054460956394");
        $custom_audience->read(array(
                "account_id",
                "approximate_count",
                "data_source",
                "delivery_status",
                "description",
                "id",
                "lookalike_audience_ids",
                "lookalike_spec",
                "name",
                "operation_status",
                "opt_out_link",
                "permission_for_actions",
                "pixel_id",
                "retention_days",
                "rule",
                "subtype",
                "time_updated"
            ));

        print_r($custom_audience);
    } catch (Exception $e) {
        handleException($e);
    }
}