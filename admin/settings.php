<?php
session_start();

if($_SERVER["HTTPS"] != "on")
{
    header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
    exit();
}

include 'db_trackify.php';

$store_name = $_GET['store_name'];
$access_token = $_GET['access_token'];
if(!isset($_SESSION['user']))
{
	header("Location: index.php");
}

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);
// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}



$sql = "SELECT smode, global , ca , kpv ,atc ,pvtc, pes, ajax FROM tbl_usersettings WHERE store_name = '$store_name'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
         $smode=$row["smode"];
		 $global=$row["global"];
		 $ca=$row["ca"];
		 $kpv=$row["kpv"];
		 $atc=$row["atc"];
		 $pvtc=$row["pvtc"];
		 $pes=$row["pes"];
		 $ajax=$row["ajax"];
    }
}


if(!mysql_connect("$servername","$username","$password"))
{
	die('oops connection problem ! --> '.mysql_error());
}
if(!mysql_select_db("$dbname"))
{
	die('oops database selection problem ! --> '.mysql_error());
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
</head>
<body>
<div id="header">
	<div id="left">
    <label>Trackify admin</label>
    </div>
    <div id="right">
    	<div id="content">
        	Hi' <?php echo $userRow['username']; ?>&nbsp;<a href="logout.php?logout">Sign Out</a>
        </div>
    </div>
</div>

<div id="body">
<center>
<h1 style="text-align:center;padding:30px;"><?php echo $store_name; ?></h1>

<table><tr>
<td>
<p>FB Custom Audience Pixel ID : </p></td><td><?php echo "********".substr($ca, -4); ?>
</td>
</tr>
<tr>
<td>
<p>Catalog ID (optional): </p></td><td><?php echo "********".substr($pvtc, -4); ?>
</td>
</tr>

<tr>
<td>
<p>Add-To-Cart and Purchase Events send: </p></td><td><select name="pes"><option value="pdid">Product ID</option><option <?php if($pes == 'vrid'){echo 'selected';} ?> value="vrid" >Variant ID</option></select></td>
</tr>
<tr>
<td>
<p>Global Key Page View Pixel ID (optional) : </p></td><td> <?php echo "********".substr($kpv, -4); ?>

</td>
</tr>
<tr>
<td>
<p>Global Add to cart Pixel ID (optional) : </p></td><td><?php echo "********".substr($atc, -4); ?>
</tr>
<td>
<p>Global Checkout Pixel ID : </p></td><td><?php echo "********".substr($global, -4); ?>
</td>
</tr>
<tr>
<td>
<p>Select Cart Mode : </p></td><td><select name="ajax"><option value="">Regular Cart</option><option <?php if(!empty($ajax)){echo 'selected';}; ?>  value="1">Ajax Cart</option></select>
</td>
</tr>
</table>
</center>
<br/>

<br/>


</div>

</body>
</html>