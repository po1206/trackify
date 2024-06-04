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
	
	
	function trackify() {

$shopify = shopify\client($_SESSION['shop'], SHOPIFY_APP_API_KEY, $_SESSION['oauth_token']);	

$script = $shopify('POST /admin/script_tags.json', 
array('script_tag' => array('event' => 'onload', 'src'=> 'https://app.redretarget.com/sapp/script-tag.php?shop='.$store )));

    $Hook2 = $shopify('POST /admin/webhooks.json', 
             array('webhook' => array('topic' => 'app/uninstalled', 'address'=> 'http://app.redretarget.com/sapp/uninstall.php?shop='.$store ,'format' => 'json' )));
			 


$theme = $shopify('GET /admin/themes.json', array('role' => 'main'));

foreach($theme as $val) {

if($val['role'] == 'main'){
if($val['previewable'] == 'true'){

$trackify = $shopify('PUT /admin/themes/'.$val['id'].'/assets.json', 
array('asset' => array('key' => 'snippets/trackify.liquid', 'value' => '
{% if template contains "product" %}
{% for tag in product.tags %}
{% if tag contains "rr_track" %}        
            
<script src="https://app.redretarget.com/sapp/query.php?tags[]={{ tag }}&shop={{ shop.permanent_domain | remove: "http://" }}&code=product"></script>

{% endif %} 
{% endfor %}

{% endif  %}
{% if template contains "cart"  %}
{% for item in cart.items %}
{% for tag in item.product.tags %}    
            
{% if tag contains "rr_track" %}        
            
<script src="https://app.redretarget.com/sapp/query.php?tags[]={{ tag }}&shop={{ shop.permanent_domain | remove: "http://" }}&code=cart"></script>

{% endif %}   
    
{% endfor %}
{% endfor %}
{% endif %}
')));


}
}
}
}
	
	
	
$theme = $shopify('GET /admin/themes.json', array());
	
if(isset($_POST['submit']))
{
// reinstall begin
$theme = $shopify('GET /admin/themes.json', array());
	
foreach($theme as $val) {
if($val['role'] == 'main'){
if($val['previewable'] == 'true'){

$cartify = $shopify('GET /admin/themes/'.$val['id'].'/assets.json?asset[key]=templates/cart.liquid&theme_id='.$val['id'], array());

if (strpos($cartify['value'],'"trackify"') == true) {
$myfile = fopen("cart.liquid", "w") or die("unable");
$txt =str_replace('{% include "trackify" %}','',$cartify['value']);
fwrite($myfile, $txt);
fclose($myfile);
	

$cart = $shopify('PUT /admin/themes/'.$val['id'].'/assets.json', 
array('asset' => array('key' => 'templates/cart.liquid', 'src'=> 'http://app.redretarget.com/sapp/cart.liquid')));

}

$proify = $shopify('GET /admin/themes/'.$val['id'].'/assets.json?asset[key]=templates/product.liquid&theme_id='.$val['id'], array());

if (strpos($proify['value'],'"trackify"') == true || strpos($proify['value'],'"ajax-trackify"') == true) {
$myfile2 = fopen("product.liquid", "w") or die("unable");
$txt2 =str_replace('{% include "ajax-trackify" %}','',$proify['value']);
$txt3 =str_replace('{% include "trackify" %}','',$txt2);
fwrite($myfile2, $txt3);
fclose($myfile2);
	

$product = $shopify('PUT /admin/themes/'.$val['id'].'/assets.json', 
array('asset' => array('key' => 'templates/product.liquid', 'src'=> 'http://app.redretarget.com/sapp/product.liquid')));

}

$themify = $shopify('GET /admin/themes/'.$val['id'].'/assets.json?asset[key]=layout/theme.liquid&theme_id='.$val['id'], array());

if (strpos($themify['value'],'"trackify"') == false) {
if (strpos($themify['value'],'</body>') !== false) {

$myfile = fopen("theme.liquid", "w") or die("unable");

$theme = str_replace('</body>','{% include "trackify" %}</body>',$themify['value']);
fwrite($myfile, $theme);
fclose($myfile);

}
else
{
$myfile = fopen("theme.liquid", "w") or die("unable");

$theme = str_replace('{{ content_for_layout }}','{% include "trackify" %}{{ content_for_layout }}',$themify['value']);
fwrite($myfile, $theme);
fclose($myfile);
}

	

$cart = $shopify('PUT /admin/themes/'.$val['id'].'/assets.json', 
array('asset' => array('key' => 'layout/theme.liquid', 'src'=> 'http://app.redretarget.com/sapp/theme.liquid')));

}
else{

$myfile = fopen("theme.liquid", "w") or die("unable");

$theme = str_replace('{% include "trackify" %}','',$themify['value']);
$theme1 = str_replace('</body>','{% include "trackify" %}</body>',$theme);
fwrite($myfile, $theme1);
fclose($myfile);
$cart = $shopify('PUT /admin/themes/'.$val['id'].'/assets.json', 
array('asset' => array('key' => 'layout/theme.liquid', 'src'=> 'http://app.redretarget.com/sapp/theme.liquid')));

}

}

}
}
}




	include 'db_trackify.php';



	// Create connection
	$conn = mysqli_connect($servername, $username, $password, $dbname);
	// Check connection
	if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
				}

	$sql = "SELECT id,billing FROM tbl_usersettings WHERE store_name = '$store_name'";
	$result = $conn->query($sql);
	if ($result->num_rows > 0) {
	 while($row = $result->fetch_assoc()) {		
	 trackify();
            $sql = "UPDATE tbl_usersettings SET billing='1' WHERE store_name = '$store_name'";
            if ($conn->query($sql) === TRUE) {
	         header("Location: http://app.redretarget.com/sapp/admin/admin.php?install=success");
	       }	 
		     
	      }
	    }
		else{ echo "hi";}





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