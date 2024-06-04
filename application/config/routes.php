<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	http://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'Auth';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

$route['install']               = 'Auth/install';
$route['install/(:any)']        = 'Auth/install';
$route['oauth']                 = 'Auth/oauth';
$route['oauth/(:any)']          = 'Auth/oauth';
$route['logout']                = 'Auth/logout';

$route['ptag/(:any)']           = 'MY_Shopify/ptag/$1';
$route['prod/(:any)']           = 'MY_Shopify/prod/$1';
$route['product/(:any)']        = 'MY_Shopify/product/$1';
$route['cart/(:any)']           = 'MY_Shopify/cart/$1';
$route['checkout/(:any)']       = 'MY_Shopify/checkout/$1';

$route['track']                 = 'Track/index';
$route['settings']              = 'Track/settings';
$route['help']                  = 'Track/help';
$route['manage/(:any)']         = 'Track/manage/$1';
$route['welcome']              	= 'Track/welcome';

$route['build-feed']         	= 'Catalog/build_feed';
$route['edit-feed/(:any)']      = 'Catalog/edit_feed/$1';
$route['save-feed/(:any)']      = 'Catalog/save_feed/$1';
$route['Catalog/search_cat/(:any)'] = 'Catalog/search_cat/$1';

$route['is_trackify_installed']      		= 'MY_Shopify/is_trackify_installed';
$route['is_trackify_installed/(:any)']      = 'MY_Shopify/is_trackify_installed/$1';

$route['facebook-feeds']         		= 'MY_Facebook/feeds';
$route['facebook-feeds/(:any)']       	= 'MY_Facebook/feeds/$1';

$route['create-feed']         			= 'MY_Facebook/create_feed';
$route['create-feed/(:any)']       		= 'MY_Facebook/create_feed/$1';
$route['create-catalog']         		= 'MY_Facebook/create_catalog';
$route['create-catalog/(:any)']       	= 'MY_Facebook/create_catalog/$1';

$route['custom_audiences']         				= 'MY_Facebook/custom_audiences';
$route['custom_audiences/(:any)']       		= 'MY_Facebook/custom_audiences/$1';

$route['create_audience/custom']       			= 'MY_Facebook/create_audience/custom';
$route['create_audience/custom/(:any)']       	= 'MY_Facebook/create_audience/custom/$1';

$route['create_audience/lookalike']      		= 'MY_Facebook/create_audience/lookalike';
$route['create_audience/lookalike/(:any)']      = 'MY_Facebook/create_audience/lookalike/$1';

$route['audiences/create/(:any)/(:any)']      	= 'Audiences/create/$1/$2';

$route['ocu']      				= 'MY_Shopify/ocu';
$route['pixel']      			= 'MY_Shopify/pixel';
$route['feeds/(:any)']       	= 'MY_Shopify/feeds/$1';
$route['update_feeds']   		= 'MY_Shopify/update_feeds';
$route['update_feeds/(:any)']   = 'MY_Shopify/update_feeds/$1';

$route['guidedinstall']   		= 'Report/guidedinstall';