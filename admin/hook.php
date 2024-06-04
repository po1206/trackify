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
	$Hook2 = $shopify('POST /admin/webhooks.json', 
array('webhook' => array('topic' => 'app/uninstalled', 'address'=> 'http://app.redretarget.com/sapp/uninstall.php?shop='.$store_name ,'format' => 'json' )));



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