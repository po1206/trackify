<?php

	session_start();
if(!isset($_SESSION['user']))
{
	header("Location: index.php");
}
	require __DIR__.'/vendor/autoload.php';
	use phpish\shopify;

	require __DIR__.'/conf.php';
if(isset($_POST['update']))
{

$store_name = $_POST['store_name'];
$access_token = $_POST['access_token'];

}
	
try
	{
	$shopify = shopify\client($store_name, SHOPIFY_APP_API_KEY, $access_token);	
$theme = $shopify('GET /admin/themes.json', array());
	
foreach($theme as $val) {
	if($val['role'] == 'main'){
if($val['previewable'] == 'true'){

$trackify = $shopify('PUT /admin/themes/'.$val['id'].'/assets.json', 
	 array('asset' => array('key' => 'snippets/trackify.liquid', 'src'=> 'http://app.redretarget.com/sapp/admin/master-trackify.liquid')));

$cartify = $shopify('GET /admin/themes/'.$val['id'].'/assets.json?asset[key]=templates/cart.liquid&theme_id='.$val['id'], array());

if (strpos($cartify['value'],'"trackify"') == false) {
$myfile = fopen("cart.liquid", "w") or die("unable");
$txt = '{% include "trackify" %}'.$cartify['value'];
fwrite($myfile, $txt);
fclose($myfile);
	

$cart = $shopify('PUT /admin/themes/'.$val['id'].'/assets.json', 
array('asset' => array('key' => 'templates/cart.liquid', 'src'=> 'http://app.redretarget.com/sapp/admin/cart.liquid')));
}


$proify = $shopify('GET /admin/themes/'.$val['id'].'/assets.json?asset[key]=templates/product.liquid&theme_id='.$val['id'], array());

if (strpos($proify['value'],'"trackify"') == false) {
$myfile2 = fopen("product.liquid", "w") or die("unable");
$txt2 = '{% include "trackify" %}'.$proify['value'];
fwrite($myfile2, $txt2);
fclose($myfile2);
	

$product = $shopify('PUT /admin/themes/'.$val['id'].'/assets.json', 
array('asset' => array('key' => 'templates/product.liquid', 'src'=> 'http://app.redretarget.com/sapp/admin/product.liquid')));

}

}
}
}

		}
	catch (shopify\ApiException $e)
	{
		# HTTP status code was >= 400 or response contained the key 'errors'
		echo $e;
		print_R($e->getRequest());
		print_R($e->getResponse());
	}
	catch (shopify\CurlException $e)
	{
		# cURL error
		echo $e;
		print_R($e->getRequest());
		print_R($e->getResponse());
	}
header("Location: http://app.redretarget.com/sapp/admin/admin.php?install=success");
?>