<?php
session_start();
    require __DIR__.'/vendor/autoload.php';
	use phpish\shopify;

	require __DIR__.'/conf.php';

if($_SERVER["HTTPS"] != "on")
{
    header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
    exit();
}

include 'db_trackify.php';

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);
// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if(!mysql_connect("$servername","$username","$password"))
{
	die('oops connection problem ! --> '.mysql_error());
}
if(!mysql_select_db("$dbname"))
{
	die('oops database selection problem ! --> '.mysql_error());
}

if(!isset($_SESSION['user']))
{
	header("Location: index.php");
}

$res=mysql_query("SELECT * FROM users WHERE user_id=".$_SESSION['user']);
$userRow=mysql_fetch_array($res);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Welcome - <?php echo $userRow['email']; ?></title>
<link rel="stylesheet" href="style.css" type="text/css" />
<script src="http://app.redretarget.com/sapp/js/jquery-1.9.1.min.js"></script>
</head>
<body>
<div id="header">
	<div id="left">
    <label><a href="admin.php">Trackify admin</a></label>
    </div>
    <div id="right">
    	<div id="content">
        	Hi' <?php echo $userRow['username']; ?>&nbsp;<a href="logout.php?logout">Sign Out</a>
        </div>
    </div>
</div>

<div id="body">

<table class="table table-bordered table-striped table-condensed">
<tbody><tr>
<th>Action</th>
<th>Shop</th>
<th>Trackify</th>
<th>User Settings</th>
<th>Billing Status</th>
<th>Trial Days Left</th>
</tr>
<?php





$sql = "SELECT * FROM `tbl_usersettings` ";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    // output data of each row
    while($row = mysqli_fetch_assoc($result)) {
	
	try
	{
	
	$shopify = shopify\client($row["store_name"], SHOPIFY_APP_API_KEY, $row["access_token"]);
	
	if(is_numeric($row["billing"]) && !empty($row["billing"]) && $row["billing"]!=1)
	{
	
	$charge = $shopify('GET /admin/recurring_application_charges/'.$row["billing"].'.json');
	
	if($charge['status']=== 'active'){ 
	
	
	$a= strtotime($charge['trial_ends_on']) - strtotime("now");
	
	if($a < 0)
	  {
	  $trial_days = 'ENDED';
	  
	  }
	  else{
	
	  $trial_days = date("d", $a );
	  }
	  
	  
	  
	  
	}
	
	}
	elseif($row["billing"]==1)
	{   
	$trial_days = '<span style="color:blue;">Permanent</span>';
	
	}
	
	else
	{
	$trial_days = '';
	
	}
	
	
	
	}
	catch (shopify\ApiException $e)
	{
		# HTTP status code was >= 400 or response contained the key 'errors'
		//echo $e;
		//print_R($e->getRequest());
		//print_R($e->getResponse());
	}
	catch (shopify\CurlException $e)
	{
		# cURL error
		echo $e;
		print_R($e->getRequest());
		print_R($e->getResponse());
	}
	
?>

<tr>

<td>
<a target="_blank" href="manage.php?store_name=<?php echo $row["store_name"];?>&access_token=<?php echo $row["access_token"];?>">Manage</a>
</td>

<td>
<a target="_blank" href="http://<?php echo $row["store_name"];?>"><?php echo $row["store_name"];?></a>
</td>

<td>
<a target="_blank" href="master-check.php?store_name=<?php echo $row["store_name"];?>&access_token=<?php echo $row["access_token"];?>">Trackify.liquid</a>
</td>

<td>
<a target="_blank" href="settings.php?store_name=<?php echo $row["store_name"];?>&access_token=<?php echo $row["access_token"];?>">Settings</a>
</td>

<td>
<?php 

if(empty($row["billing"]))
{ echo "<span style='color:yellow;'>Inactive</span>";}

elseif($row["billing"]== 'rejected')
{
echo "<span style='color:red;'>Rejected</span>";
}
else{ echo "<span style='color:green;'>Active</span>" ;  $count= $count+1;}



?>
</td>

<td>
<?php echo $trial_days ; ?>
</td>


</tr>
<tr>

</tr>
  <?php
    }
} else {
    echo "0 results";
}

mysqli_close($conn);
?> 
<tr>
<td>
</td>
<td>
</td>
<td>
</td>
<td>
Total Active:

</td>
<td>
<?php echo $count; ?>

</td>
</tr>

</tbody></table>

</div>


<div class="modal-overlay" id="AddModal2" style="display:none;">
  <section class="quick-modal">
    <div class="table-cell">
      <div class="product">
        <div class="center" id="moq">
          
<p>Congratulation! You have successfully installed Trackify in all themes</p>
        
        </div>
          
      </div>
      
     
    </div>
    
     <span class="close" onclick="$('#AddModal2').fadeOut();"></span>
    
  </section>
</div>
 
 <?php 
 
 if(($_GET['install'])== 'success')
 {
 echo"<script> 
 $('#AddModal2').fadeIn();
 
 $('body').click(function(e) {
        if($(e.target).is('#AddModal2')){
            e.preventDefault();
            return;
        }
        $('#AddModal2').fadeOut();
    }); </script>";
 
 
 }
 
  ?>
  

</body>
</html>