<?php
session_start();
include_once("config.inc.php");

$site 		= $_GET["seite"];
$oldsite 	= $_GET["oldsite"];
$newcity 	= $_GET["newcity"];
$city 		= $_SESSION["city"];
$user 		= $_SESSION["user"];

### FUNKTIONEN

function bbcode($text)
{
	$text = nl2br(htmlentities($text,ENT_NOQUOTES,'utf-8'));
	$text = preg_replace("/\[B\](.*)\[\/B\]/isU","<b>$1</b>",$text);
	$text = preg_replace("/\[I\](.*)\[\/I\]/isU","<i>$1</i>",$text);
	$text = preg_replace("/\[U\](.*)\[\/U\]/isU","<u>$1</u>",$text);
	$text = preg_replace("/\[img\](.*)\[\/img\]/isU", "<img src=\"$1\" />",$text);
	return $text;
}

function make_array($start_array,$end_count)
{
	$array = split("\n",$start_array);
	$end_array = array();
	$highest_id = 1;
	for($x=0;$x<count($array);$x++)
	{
		$parsed = split(";",$array[$x]);
		$id = $parsed[0];
		$level = $parsed[1];
		$mid_array[$id] = trim($level);
		if($id > $highest_id) { $highest_id = $id; }
	}
	for($x=1;$x<=$highest_id;$x++)
	{
		if(!$mid_array[$x])
		{
			$end_array[$x] = 0;
		}
		else
		{
			$end_array[$x] = $mid_array[$x];
		}
	}

	return $end_array;
}

function building_costs_fe($building_id,$basecost,$level)
{
	return $basecost+$basecost*$level*$level;
}

function building_costs_h2o($building_id,$basecost,$level)
{
	return $basecost+$basecost*$level*$level;
}

function building_costs_uran($building_id,$basecost,$level)
{
	return $basecost+$basecost*$level*$level;
}

function building_costs_energy($building_id,$basecost,$level)
{
	return $basecost;
}

function building_costs_time($building_id,$basecost,$buildings_array)
{
	$level = $buildings_array['b'.$building_id];
	$bauhof_level = $buildings_array['b1'];
	return $basecost+($basecost*($level/$bauhof_level)*$level);
}

function research_costs_fe($research_id,$basecost,$level)
{
	return $basecost+$basecost*$level*$level;
}

function research_costs_h2o($research_id,$basecost,$level)
{
	return $basecost+$basecost*$level*$level;
}

function research_costs_uran($research_id,$basecost,$level)
{
	return $basecost+$basecost*$level*$level;
}
function research_costs_time($research_id,$basecost,$level,$fz_level)
{
	if(intval($fz_level) == 0) { $fz_level = 1; }
	return $basecost+($basecost*($level/$fz_level)*$level);
}

function unit_costs_time($base_time,$barracks_level)
{
	$duration = $base_time-sqrt($base_time*$barracks_level*25);
	if($duration < $base_time/5) { $duration = $base_time/5; }
	return $duration;
}

function change_city($old,$new,$userid)
{
	$owner_query = mysql_query("SELECT owner FROM cities WHERE id='$new'");
	$owner_array = mysql_fetch_array($owner_query);
	$owner = $owner_array["owner"];
	if($owner == $userid)
	{
		session_unregister(city);
		$_SESSION["city"] = $new;
	}
}

function gender($char)
{
	if($char == 'm') { return 'm&auml;nnlich'; }
	elseif($char == 'w') { return 'weiblich'; }
	elseif($char == '') { return 'keine Angabe'; }
}

function getuserid($user)
{
	$userid_query = mysql_query("SELECT id FROM gamer WHERE user='$user'");
	$userid_array = mysql_fetch_array($userid_query);
	$userid = $userid_array["id"];
	return $userid;
}

function getusername($user_id)
{
	$username_query = mysql_query("SELECT * FROM gamer WHERE id='$user_id'");
	$username_array = mysql_fetch_array($username_query);
	$username = $username_array["user"];
	return $username;
}

function getcoords($city_id)
{
	$coords_query = mysql_query("SELECT id,x,y FROM cities WHERE id='$city_id'");
	$coords_array = mysql_fetch_array($coords_query);
	$x = $coords_array["x"];
	$y = $coords_array["y"];
	return array($x,$y);
}

function getcityid($x,$y)
{
	$id_query = mysql_query("SELECT id FROM cities WHERE x='$x' AND y='$y'");
	$id_array = mysql_fetch_array($id_query);
	$id = $id_array["id"];
	return $id;
}

function city_points($city_id)
{
	$points_query = mysql_query("SELECT points FROM cities WHERE `id`='$city_id'");
	$points_array = mysql_fetch_array($points_query);
	$city_points = $points_array["points"];

	return $city_points;
}

function cities_points($user_id)
{
	$points_query = mysql_query("SELECT buildings_points FROM gamer WHERE `id`='$user_id'");
	$points_array = mysql_fetch_array($points_query);
	$cities_points = $points_array["buildings_points"];

	return $cities_points;
}

function research_points($user_id)
{
	$points_query = mysql_query("SELECT research_points FROM gamer WHERE `id`='$user_id'");
	$points_array = mysql_fetch_array($points_query);
	$research_points = $points_array["research_points"];

	return $research_points;
}

function unit_name($id)
{
	$unit_query = mysql_query("SELECT name FROM units WHERE id='$id'");
	$unit_array = mysql_fetch_array($unit_query);
	return $unit_array["name"];
}

function update_research($event_id,$research_id,$user_id)
{
	## abgeschlossenes Event loeschen und hoehere Forschungsstufe in die DB eintragen.
	$delete = mysql_query("DELETE FROM events_research WHERE id='$event_id' ");

	$user_research_query = mysql_query("SELECT id,research FROM gamer WHERE id='$user_id'");
	$user_research_array = mysql_fetch_array($user_research_query);

	if($user_research_array["research"] == '')
	{
		$research_string = '';
		for($x=1;$x<=4;$x++)
		{
			$research_string .= $x.';0'."\n";
		}
		mysql_query("UPDATE gamer SET research = '$research_string' WHERE id='$user_id'");
		$user_research_array["research"] = $research_string;
	}

	$user_research = make_array($user_research_array["research"],4);
	$user_research[$research_id] += 1;
	$research_new = '';
	## Komplettes Array mit allen Forschungen des Spielers
	for($x=1;$x<=4;$x++)
	{
		$research_new .= $x.';'.$user_research[$x]."\n";
	}
	$update = mysql_query("UPDATE gamer SET research = '$research_new' WHERE id='$user_id'");

	$name_query = mysql_query("SELECT name FROM researches WHERE id='$research_id'");
	$name_array = mysql_fetch_array($name_query);

	$name = $name_array["name"];
	$stufe = $user_research[$research_id];
	$time = time();
	$msg = "Technologie $name (Stufe $stufe) wurde erforscht.";
	
	$message = mysql_query("INSERT INTO messages (`owner`,`sender_id`,`sender_city`,`folder`,`msg`,`time`,`read`) VALUES ('$user_id','$user_id','','4','$msg','$time','0')");

	?>
	<script language="javascript">
	<!--
	window.location.reload();
	//-->
	</script>
	<?php
}

# TODO von Array auf Stufe umbauen !?
function energy_kraftwerk($buildings_array,$building_id)
{
	# Gibt die Energie zurueck die ein Kraftwerk ($building_id) auf einer bestimmten Stufe produziert
	$prod_query = mysql_query("SELECT prod FROM buildings WHERE `id`='$building_id'");
	$prod_array = mysql_fetch_array($prod_query);
	return $buildings_array[$building_id]*$prod_array["prod"];
}

function energy_needed($buildings_array)
{
 ## TODO something wrong here !
	$energy_needed = 0;
	while(list($building_id, $building_level) = each($buildings_array))
	{
		$energy_query = mysql_query("SELECT energy FROM buildings WHERE `id`='$building_id'");
		$energy_array = mysql_fetch_array($energy_query);
		$energy_needed += $building_level*$energy_array["energy"];
	}
	return $energy_needed;
}

function ressis_now($ressis_array,$city_id)
{
	$fe_old			= $ressis_array["fe"];
	$h2o_old		= $ressis_array["h2o"];
	$uran_old		= $ressis_array["uran"];
	$time_old		= $ressis_array["time"];

	# prod = produktion/kapazität der Gebäudetypen
	$prod_query		= mysql_query("SELECT id,prod FROM buildings ORDER BY id");
	$prod_array		= make_array(array(),14);
	while($prod_row = mysql_fetch_object($prod_query))
	{
		$prod_array[$prod_row->id] = $prod_row->prod;
	}

	$fe_prod		= $ressis_array["b2"]*$prod_array[2];
	$h2o_prod		= $ressis_array["b3"]*$prod_array[3];
	$uran_prod		= $ressis_array["b11"]*$prod_array[11];

	# Wie viel kann ich maximal lagern ?
	$fe_max			= $prod_array[4]+($ressis_array["b4"]*$prod_array[4]);
	$h2o_max		= $prod_array[5]+($ressis_array["b5"]*$prod_array[5]);
	$uran_max		= $prod_array[12]+($ressis_array["b12"]*$prod_array[12]);

	$time_new		= time();
	$time_diff		= $time_new - $time_old;

	$fe_new			= $fe_old+(($time_diff/3600)*$fe_prod);
	$h2o_new		= $h2o_old+(($time_diff/3600)*$h2o_prod);
	$uran_new		= $uran_old+(($time_diff/3600)*$uran_prod);

	# Speicher voll ?
	if($fe_new > $fe_max) { $fe_new = $fe_max; }
	if($h2o_new > $h2o_max) { $h2o_new = $h2o_max; }
	if($uran_new > $uran_max) { $uran_new = $uran_max; }

	$update = mysql_query("UPDATE cities SET `fe`='$fe_new', `h2o`='$h2o_new', `uran`='$uran_new', `time`='$time_new' WHERE `id`='$city_id'");

	$ressis_array["fe"]	= $fe_new;
	$ressis_array["h2o"]	= $h2o_new;
	$ressis_array["uran"]	= $uran_new;
	$ressis_array["fe_max"] = $fe_max;
	$ressis_array["h2o_max"] = $h2o_max;
	$ressis_array["uran_max"] = $uran_max;
	$ressis_array["time"]	= $time_new;

	return $ressis_array;
}

### ENDE FUNKTIONEN

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

$sites_pub = array(
	"home" => "welcome.php",
	"news" => "news.php",
	#"anleitung" => "tutorial.php",
	"login" => "login.php",
	"impressum" => "impressum.php"
);
$sites_int = array(
	"uebersicht" => "overview.php",
	"gebaeude" => "buildings.php",
	"militaerbasis" => "barracks.php",
	"karte" => "map.php",
	"forschung" => "research.php",
	"marsch" => "send_troops.php",
	"allianz" => "alliance.php",
	"nachrichten" => "messages.php",
	"einstellungen" => "settings.php",
	"technik" => "technik.php",
	"highscore" => "highscore.php",
	"forum" => "forum.php",
	"admin" => "admin.php",
	"logout" => "logout.php"
);

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
		include("ressibar.php");
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
