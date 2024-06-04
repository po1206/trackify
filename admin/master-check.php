<?php

	session_start();

	require __DIR__.'/vendor/autoload.php';
	use phpish\shopify;

	require __DIR__.'/conf.php';
	
$store_name = $_GET['store_name'];
$access_token = $_GET['access_token'];
if(!isset($_SESSION['user']))
{
	header("Location: index.php");
}
	
try
	{
	$shopify = shopify\client($store_name, SHOPIFY_APP_API_KEY, $access_token);	
$theme = $shopify('GET /admin/themes.json', array());
	
foreach($theme as $val) {
if($val['role'] == 'main'){
$trackify = $shopify('GET /admin/themes/'.$val['id'].'/assets.json?asset[key]=snippets/trackify.liquid&theme_id='.$val['id'], array());
$a = $trackify['value'];
echo $a.'<br/><br/><br/>';
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

?>