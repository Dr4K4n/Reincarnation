<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
  <title>OGame 2100</title>
  <meta name="GENERATOR" content="Quanta Plus" />
  <meta name="AUTHOR" content="Stefan Erichsen" />
  <meta http-equiv="Content-Type" content="text/html; charset=utf8" />
  <link rel="stylesheet" type="text/css" href="style.css" />
</head>
<div>

<?php

function make_array($start_array,$end_count)
{
    $array = split("\n",$start_array);
    $end_array = array();
    for($x=0;$x<count($array);$x++)
    {
		$parsed = split(";",$array[$x]);
		$id = $parsed[0];
		$level = $parsed[1];
		$mid_array[$id] = trim($level);
    }

    for($x=1;$x<=$end_count;$x++)
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

function getcoords($city)
{
    $coords_query = mysql_query("SELECT id,x,y FROM cities WHERE id='$city'");
    $coords_array = mysql_fetch_array($coords_query);
    $x = $coords_array["x"];
    $y = $coords_array["y"];
    return array($x,$y);
}

function unit_name($id)
{
    $unit_query = mysql_query("SELECT name FROM units WHERE id='$id'");
    $unit_array = mysql_fetch_array($unit_query);
    return $unit_array["name"];
}

$id = $_GET["id"];

include_once("connect.inc.php");

$bericht_query = mysql_query("SELECT * FROM reports WHERE id='$id'");
if(mysql_num_rows($bericht_query) != 0)
{
	$bericht_array = mysql_fetch_array($bericht_query);
	$atter_coords_array = getcoords($bericht_array["source"]);
	$deffer_coords_array = getcoords($bericht_array["target"]);

	$a_troops_array = make_array($bericht_array["a_troops"],5);
	$a_troops_final_array = make_array($bericht_array["a_troops_final"],5);
	$d_troops_array = make_array($bericht_array["d_troops"],5);
	$d_troops_final_array = make_array($bericht_array["d_troops_final"],5);

	echo '<div id="report"><table id="table01">';
	echo '<tr><th colspan="3">Kampfbericht</th></tr>';
	echo '<tr><td colspan="3">Uhrzeit: '.strftime("%X",$bericht_array["time"]).'<br />Datum: '.strftime("%d.%m.%Y",$bericht_array["time"]).'</td></tr>';

	echo '<tr><th colspan="3">Angreifer ('.$atter_coords_array[0].':'.$atter_coords_array[1].')</th></tr>';
	echo '<tr><td class="top">Einheit</td><td class="top" width="120px">Anzahl</td><td class="top" width="120px">Verluste</td></tr>';
	while(list($unit_id, $unit_count) = each($a_troops_array))
	{
		if($unit_count != 0)
		{
			echo '<tr><td>'.unit_name($unit_id).'</td><td class="center">'.$unit_count.'</td><td class="center">'.($unit_count-$a_troops_final_array[$unit_id]).'</td></tr>';
		}
	}

	echo '<tr><th colspan="3">Verteidiger ('.$deffer_coords_array[0].':'.$deffer_coords_array[1].')</td></tr>';
	$notroops = 1;
	while(list($unit_id, $unit_count) = each($d_troops_array))
	{
		if($unit_count != 0)
		{
			if($notroops == 1) { echo '<tr><td class="top">Einheit</td><td class="top" width="120px">Anzahl</td><td class="top" width="120px">Verluste</td></tr>'; }
			echo '<tr><td>'.unit_name($unit_id).'</td><td class="center">'.$unit_count.'</td><td class="center">'.($unit_count-$d_troops_final_array[$unit_id]).'</td></tr>';
			$notroops = 0;
		}
	}
	if($notroops == 1)
	{
		echo '<tr><td colspan="3" class="center">Keine Truppen</td></tr>';
	}
	echo '<tr><th colspan="3">erbeutete Rohstoffe</th></tr>';
	if($bericht_array['fe'] == 0 && $bericht_array['h2o'] == 0 && $bericht_array['uran'] == 0)
	{
		echo '<tr><td colspan="3">Keine Rohstoffe erbeutet</td></tr>';
	}
	else
	{
		if($bericht_array['fe'] != 0) { echo '<tr><td>Eisen</td><td colspan="2">'.$bericht_array['fe'].'</td></tr>'; }
		if($bericht_array['h2o'] != 0) { echo '<tr><td>Wasser</td><td colspan="2">'.$bericht_array['h2o'].'</td></tr>'; }
		if($bericht_array['uran'] != 0) { echo '<tr><td>Uran</td><td colspan="2">'.$bericht_array['uran'].'</td></tr>'; }
	}

	echo '</table></div>';
}
else
{
	echo 'Bericht nicht vorhanden!';
}

include_once("disconnect.inc.php");
?>

</body>
</html>