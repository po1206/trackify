<?php

	session_start();
if(!isset($_SESSION['user']))
{
	header("Location: index.php");
}
	require __DIR__.'/vendor/autoload.php';
	use phpish\shopify;

	require __DIR__.'/conf.php';

	
if(isset($_POST['ajax']))
{
$store_name = $_POST['store_name'];
$access_token = $_POST['access_token'];
$click ="#".$_POST['click'];



try
	{
		
$shopify = shopify\client($store_name, SHOPIFY_APP_API_KEY, $access_token);	
$theme = $shopify('GET /admin/themes.json', array());

foreach($theme as $val) {
if($val['role'] == 'main'){
$trackify1 = $shopify('GET /admin/themes/'.$val['id'].'/assets.json?asset[key]=snippets/trackify.liquid&theme_id='.$val['id'], array());
$a = $trackify1['value'];

}
}

$myfile = fopen("ajax-trackify.liquid", "w") or die("unable");
$ajax = str_replace('input[name="add"],button[name="add"],#buy_it_now',$click ,$a);

fwrite($myfile, $ajax);
fclose($myfile);
	
foreach($theme as $val) {
	if($val['role'] == 'main'){
$trackify = $shopify('PUT /admin/themes/'.$val['id'].'/assets.json', 
	 array('asset' => array('key' => 'snippets/trackify.liquid', 'src'=> 'http://app.redretarget.com/sapp/admin/ajax-trackify.liquid')));
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
}
?>