<?php
session_start();

include_once("config.inc.php");
include_once("func.inc.php");

$site 		= $_GET["seite"];
$oldsite 	= $_GET["oldsite"];
$newcity 	= $_GET["newcity"];
$city 		= $_SESSION["city"];
$user 		= $_SESSION["user"];

if($newcity)
{
	if($city != $newcity)
	{
		change_city($city,$newcity,$userid);
?>

<script language="javascript">
<!--
<?php echo 'window.location.href = "?seite='.$oldsite.'";'; ?>
// -->
</script>

<?php
	}
}

include("connect.inc.php");

if($site == '') { $datei = 'welcome.php'; }
elseif(array_key_exists($site,$sites_pub)) { $datei = $sites_pub[$site]; }
elseif(array_key_exists($site,$sites_int)) { $datei = $sites_int[$site]; $intern = 1; }
else { $datei = 'error.php'; }

?>
<!-- JavaScript for Changing your City -->
<script language="JavaScript">
function changecity (cityindex)
{
	<?php echo "oldsite = '".$site."';"; ?>
	window.location.href = "?newcity="+cityindex.value+"&oldsite="+oldsite;
}
</script>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
  <title>Reincarnation 2100</title>
  <meta name="GENERATOR" content="Quanta Plus" />
  <meta name="AUTHOR" content="Stefan Erichsen" />
  <meta http-equiv="Content-Type" content="text/html; charset=utf8" />
  <link rel="stylesheet" type="text/css" href="style.css" />
</head>
<body>

<div id="outerframe">

<img id="back_left" src="pics/back_bottom_left.jpg" />

<img id="back_right" src="pics/back_bottom_right.jpg" />

<div id="frame">

<div id="header"></div>

<div id="navi_top">
<?php
if(session_is_registered("user"))
{
	echo '<p>Account: <b>'.$user.'</b></p><p>&nbsp;</p>';
	if($datei != 'logout.php')
	{
		include_once("ressibar.php");
	}
}
?>
</div>

<div id="navi_left">
<?php if(!session_is_registered("user")) { ?>
<img src="pics/nav_head_1.jpg" />
  <ul>
    <li><a href="?seite=home" onmouseover="image1.src='pics/buttons/over_home.jpg';" onmouseout="image1.src='pics/buttons/norm_home.jpg';">
	<img name="image1" src="pics/buttons/norm_home.jpg" /></a></li>
    <li><a href="?seite=login" onmouseover="image2.src='pics/buttons/over_login.jpg';" onmouseout="image2.src='pics/buttons/norm_login.jpg';">
	<img name="image2" src="pics/buttons/norm_login.jpg" /></a></li>
    <li><a href="?seite=impressum" onmouseover="image3.src='pics/buttons/over_impressum.jpg';" onmouseout="image3.src='pics/buttons/norm_impressum.jpg';">
	<img name="image3" src="pics/buttons/norm_impressum.jpg" /></a></li>
  </ul>
<?php } else { ?>
<img src="pics/nav_head_2.jpg" />
  <ul>
    <li><a href="?seite=uebersicht" onmouseover="image4.src='pics/buttons/over_overview.jpg';" onmouseout="image4.src='pics/buttons/norm_overview.jpg';">
	<img name="image4" src="pics/buttons/norm_overview.jpg" /></a></li>
    <li><a href="?seite=gebaeude" onmouseover="image5.src='pics/buttons/over_buildings.jpg';" onmouseout="image5.src='pics/buttons/norm_buildings.jpg';">
	<img name="image5" src="pics/buttons/norm_buildings.jpg" /></a></li>
    <li><a href="?seite=militaerbasis" onmouseover="image6.src='pics/buttons/over_barracks.jpg';" onmouseout="image6.src='pics/buttons/norm_barracks.jpg';">
	<img name="image6" src="pics/buttons/norm_barracks.jpg" /></a></li>
    <li><a href="?seite=forschung" onmouseover="image7.src='pics/buttons/over_research.jpg';" onmouseout="image7.src='pics/buttons/norm_research.jpg';">
	<img name="image7" src="pics/buttons/norm_research.jpg" /></a></li>
    <li><a href="?seite=karte" onmouseover="image8.src='pics/buttons/over_map.jpg';" onmouseout="image8.src='pics/buttons/norm_map.jpg';">
	<img name="image8" src="pics/buttons/norm_map.jpg" /></a></li>
    <li><a href="?seite=marsch" onmouseover="image9.src='pics/buttons/over_sendtroops.jpg';" onmouseout="image9.src='pics/buttons/norm_sendtroops.jpg';">
	<img name="image9" src="pics/buttons/norm_sendtroops.jpg" /></a></li>
    <li><a href="?seite=nachrichten" onmouseover="image10.src='pics/buttons/over_messages.jpg';" onmouseout="image10.src='pics/buttons/norm_messages.jpg';">
	<img name="image10" src="pics/buttons/norm_messages.jpg" /></a></li>
    <li><a href="?seite=allianz" onmouseover="image11.src='pics/buttons/over_alliance.jpg';" onmouseout="image11.src='pics/buttons/norm_alliance.jpg';">
	<img name="image11" src="pics/buttons/norm_alliance.jpg" /></a></li>
    <li><a href="?seite=technik" onmouseover="image13.src='pics/buttons/over_technik.jpg';" onmouseout="image13.src='pics/buttons/norm_technik.jpg';">
	<img name="image13" src="pics/buttons/norm_technik.jpg" /></a></li>
    <li><a href="?seite=highscore" onmouseover="image14.src='pics/buttons/over_highscore.jpg';" onmouseout="image14.src='pics/buttons/norm_highscore.jpg';">
	<img name="image14" src="pics/buttons/norm_highscore.jpg" /></a></li>
    <li><a href="?seite=forum" onmouseover="image15.src='pics/buttons/over_forum.jpg';" onmouseout="image15.src='pics/buttons/norm_forum.jpg';">
	<img name="image15" src="pics/buttons/norm_forum.jpg" /></a></li>
    <li><a href="?seite=einstellungen" onmouseover="image12.src='pics/buttons/over_settings.jpg';" onmouseout="image12.src='pics/buttons/norm_settings.jpg';">
	<img name="image12" src="pics/buttons/norm_settings.jpg" /></a></li>
    <li><a href="?seite=logout" onmouseover="image16.src='pics/buttons/over_logout.jpg';" onmouseout="image16.src='pics/buttons/norm_logout.jpg';">
	<img name="image16" src="pics/buttons/norm_logout.jpg" /></a></li>
  </ul>
<?php } ?>
<?php

if($_SESSION["user"])
{
	$userid = $_SESSION['user_id'];

	$cities_query = mysql_query("SELECT * FROM cities WHERE owner='$userid'");
	if(mysql_num_rows($cities_query) >= 1)
	{
		echo '<br /><img src="pics/nav_head_3.jpg" /><form>';
		echo '<select name="cities" style="width:130px;" onchange="changecity(this.form.cities.options[this.form.cities.selectedIndex])">';
		while($cities_row = mysql_fetch_object($cities_query))
		{
			if($cities_row->id == $_SESSION["city"])
			{
				echo '<option selected="selected" value="'.$cities_row->id.'">'.$cities_row->x.':'.$cities_row->y.' '.$cities_row->name.'</option>';
			}
			else
			{
				echo '<option value="'.$cities_row->id.'">'.$cities_row->x.':'.$cities_row->y.' '.$cities_row->name.'</option>';
			}
		}
		echo '</select></form>';
	}
}
?>
</div>

<div id="navi_right">
<img src="pics/right_head_1.jpg" />
</div>

<div id="content">
<img src="pics/content_top.jpg" />
<?php

$game_query = mysql_query("SELECT * FROM game");
$game_array = mysql_fetch_array($game_query);

if(!$intern)
{
	include($datei);
}
else
{
	if($game_array['locked'] == 1 && $datei != 'admin.php')
	{
		echo '<h4>Das Spiel ist momentan gesperrt!</h4><p>'.$game_array["news"].'</p>';
	}
	else
	{
		if (!session_is_registered('user'))
		{
			echo "<p>Bitte einloggen!</p>";
			echo '<p><a href="?seite=login">Zum Login</a></p>';
		} else {
			include($datei);
		}
	}

	echo '<p class="small">Serverzeit: '.strftime('%d.%m.%Y %X',time()).'</p>';
}

include("disconnect.inc.php");
?>
</div>



<div id="footer"></div>

</div>
</div>

<img width="0" height="0" src="pics/buttons/over_home.jpg" />
<img width="0" height="0" src="pics/buttons/over_login.jpg" />
<img width="0" height="0" src="pics/buttons/over_impressum.jpg" />
<img width="0" height="0" src="pics/buttons/over_overview.jpg" />
<img width="0" height="0" src="pics/buttons/over_buildings.jpg" />
<img width="0" height="0" src="pics/buttons/over_barracks.jpg" />
<img width="0" height="0" src="pics/buttons/over_research.jpg" />
<img width="0" height="0" src="pics/buttons/over_map.jpg" />
<img width="0" height="0" src="pics/buttons/over_sendtroops.jpg" />
<img width="0" height="0" src="pics/buttons/over_messages.jpg" />
<img width="0" height="0" src="pics/buttons/over_alliance.jpg" />
<img width="0" height="0" src="pics/buttons/over_technik.jpg" />
<img width="0" height="0" src="pics/buttons/over_highscore.jpg" />
<img width="0" height="0" src="pics/buttons/over_forum.jpg" />
<img width="0" height="0" src="pics/buttons/over_settings.jpg" />
<img width="0" height="0" src="pics/buttons/over_logout.jpg" />


</body>
</html>
