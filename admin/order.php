<?php
   $servername = "mysql51-056.wc2.dfw1.stabletransit.com";
   $username = "493328_tfadm";
   $password = "FWOj2RvQ8EXewX8";
   $dbname = "493328_trackify";
   $store = $_GET['shop'];
   
   require __DIR__.'/vendor/autoload.php';
   use phpish\shopify;

   require __DIR__.'/conf.php';
   
   
   
      // Create connection
   $conn = mysqli_connect($servername, $username, $password, $dbname);
   // Check connection
   if (!$conn) {
                die("Connection failed: " . mysqli_connect_error());
               }
$sql = "SELECT access_token FROM tbl_usersettings WHERE store_name = '$store'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
       $access_token=$row["access_token"];
    }
}

$shopify = shopify\client($store, SHOPIFY_APP_API_KEY, $access_token);
try
{

$shop = $shopify('GET /admin/shop.json', array());
$currency=$shop['currency'];

$sql = "UPDATE tbl_usersettings SET currency='$currency' WHERE store_name = '$store'";
if ($conn->query($sql) === TRUE) {
                                 }

}

   catch (shopify\ApiException $e)
   {
      # HTTP status code was >= 400 or response contained the key 'errors'
      echo $e;
      print_r($e->getRequest());
      print_r($e->getResponse());
   }
   catch (shopify\CurlException $e)
   {
      # cURL error
      echo $e;
      print_r($e->getRequest());
      print_r($e->getResponse());
   }



?>
