<?php
session_start();
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
<div>
<form method="post" action="master-install.php">
<table align="center" width="30%" border="0">
<tr>
<td><input type="text" name="store_name" value="<?php echo $store_name;  ?>" required /></td>
</tr>
<tr>
<td><input type="text" name="access_token" value="<?php echo $access_token;  ?>" required /></td>
</tr>
<tr>
<td><button type="submit" name="update">UPDATE</button></td>
</tr>

</table>
</form>
</div>
</center>
<br/>
<br/>
<center>
</center>


</div>

</body>
</html>