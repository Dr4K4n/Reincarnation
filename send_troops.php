<h3>Marschbefehl</h3>

<?php

$senden = $_GET["senden"];
$weiter = $_GET["weiter"];
$status = $_GET["status"];
$x = $_GET["x"];
$y = $_GET["y"];

if($senden == 'ja')
{
	echo '<form method="POST" action="?seite=marsch&weiter=ja"><table id="table01">';

	$action		= $_POST["action"];
	$target_x	= $_POST["x"];
	$target_y	= $_POST["y"];
	$troops_array	= $_POST["troops"];
	$troops_values	= array_values($troops_array);
	$values		= array_count_values($troops_array);

	if(count($troops_array) == $values[0] && $troops_values[0] == 0)
	{
		echo 'Du hast keine Truppen angegeben!';
	}
	elseif($action == "attack" && $target_x != '' && $target_y != '')
	{
		$source_owner_query = mysql_query("SELECT owner FROM cities WHERE id='$city'");
		$source_owner_array = mysql_fetch_array($source_owner_query);
		$source_owner = $source_owner_array["owner"];

		$target = getcityid($target_x,$target_y);
		$target_owner_query = mysql_query("SELECT owner FROM cities WHERE id='$target'");
		$target_owner_array = mysql_fetch_array($target_owner_query);
		$target_owner = $target_owner_array["owner"];

		$source_coords = getcoords($city);
		$source_x = $source_coords[0];
		$source_y = $source_coords[1];
		$way_x = $source_x - $target_x;
		$way_y = $source_y - $target_y;
		## c = Wurzel(a+b)
		## *30  - 1 Feld = 30 km
		$distance = sqrt($way_x*$way_x+$way_y*$way_y)*15;

		## Geschwindigkeit nach der langsamsten Einheit ausrichten
		$speed = 1000000;
		$speed_query = mysql_query("SELECT id,speed,space FROM units;");
		while($speed_row = mysql_fetch_object($speed_query))
		{
			$space_array[$speed_row->id] = $speed_row->space;
			if($troops_array[$speed_row->id] != 0 && $speed_row->speed < $speed)
			{
				$speed = $speed_row->speed;
			}
		}
		## Berechnung der Dauer in Stunden * 3600 = sekunden
		$time = time();
		$duration = ($distance/$speed)*3600;
		$arrive = intval($time + $duration);
		$return = intval($arrive + $duration);

		if($duration >= 60*60*24)
		{
			$days = intval($duration / (60*60*24));
			$duration_string = $days.' Tage ';
			$duration_left = $duration-($days*60*60*24);
		}
		else
		{
			$duration_left = $duration;
		}


		$city_troops_query = mysql_query("SELECT troops FROM cities WHERE id='$city'");
		$city_troops_query_array = mysql_fetch_array($city_troops_query);
		$city_troops	= $city_troops_query_array["troops"];
		$city_troops_array_new	= make_array($city_troops,5);

		echo "<tr><td>Befehl</td><td>Angriff auf ".$target_x.":".$target_y."</td></tr>";
		echo "<tr><td>Marschl&#228;nge</td><td>".number_format($distance,2,',','.')." km</td></tr>";
		echo "<tr><td>Marschgeschwindigkeit</td><td>".$speed." km/h</td></tr>";
		echo "<tr><td>Marschdauer</td><td>".$duration_string.gmstrftime('%X',$duration_left)."</td></tr>";
		echo "<tr><td>Ladekapazität</td><td>".space($troops_array,$space_array)." Einheiten</td></tr>";
		echo "<tr><td>Ankunft</td><td>".strftime('%d.%m.%Y %X',$arrive)."</td></tr>";
		echo "<tr><td>R&#252;ckkehr</td><td>".strftime('%d.%m.%Y %X',$return)."</td></tr>";
		echo "<tr><td>Truppen</td><td>";
		while(list($unit_id, $unit_count) = each($troops_array))
		{
			if($unit_count != 0)
			{
				if($unit_count > $city_troops_array_new[$unit_id])
				{
					$unit_count = $city_troops_array_new[$unit_id];
				}
				echo $unit_count." ".unit_name($unit_id)."<br />";
				echo '<input type="hidden" name="troops['.$unit_id.']" value="'.$unit_count.'">';
			}
		}
		echo "</td></tr>";
		echo '<tr><td colspan="2"><input type="submit" value=" Angriffsbefehl best&#228;tigen "></td></tr>';
		echo '<input type="hidden" name="type" value="attack">';
		echo '<input type="hidden" name="duration" value="'.$duration.'">';
		echo '<input type="hidden" name="target_x" value="'.$target_x.'">';
		echo '<input type="hidden" name="target_y" value="'.$target_y.'">';
		echo '<input type="hidden" name="target_owner" value="'.$target_owner.'">';
		echo '<input type="hidden" name="source_owner" value="'.$source_owner.'">';
	}
	elseif($target_x == '' && $target_y == '')
	{
		echo 'Du hast kein Ziel angegeben!';
	}
	echo '</table></form>';
}
elseif($weiter == 'ja')
{
	$type		= $_POST["type"];
	$duration	= $_POST["duration"];
	$target_x	= $_POST["target_x"];
	$target_y	= $_POST["target_y"];
	$troops_array	= $_POST["troops"];
	$target_owner	= $_POST["target_owner"];
	$source_owner	= $_POST["source_owner"];

	$time		= time();
	$arrive		= $time + $duration;
	$back		= $arrive + $duration;

	$target		= getcityid($target_x,$target_y);
	$troops		= '';

	if($type == 'attack')
	{
		$type = 1;
		$typestring = 'Angriff auf ';
	}

	$city_troops_query = mysql_query("SELECT troops FROM cities WHERE id='$city'");
	$city_troops_query_array = mysql_fetch_array($city_troops_query);
	$city_troops	= $city_troops_query_array["troops"];
	$city_troops_array_new	= make_array($city_troops,5);

	while(list($unit_id, $unit_count) = each($troops_array))
	{
		if($unit_count != 0)
		{
			if($unit_count > $city_troops_array_new[$unit_id])
			{
				$unit_count = $city_troops_array_new[$unit_id];
			}
			$troops .= $unit_id.";".$unit_count."\n";
			$city_troops_array_new[$unit_id] -= $unit_count;
		}
	}

	$city_troops_new = '';
	while(list($unit_id, $unit_count) = each($city_troops_array_new))
	{
		$city_troops_new .= $unit_id.';'.$unit_count."\n";
	}

	$update = mysql_query("UPDATE cities SET troops='$city_troops_new' WHERE id='$city'");

	$insert = mysql_query("INSERT INTO events_troops (source,source_owner,target,target_owner,type,arrive,back,troops) VALUES ('$city','$source_owner','$target','$target_owner','$type','$arrive','$back','$troops')");

	echo '<table id="table01">';
	echo '<tr><th colspan="2">Status</th></tr>';
	echo '<tr><td>Befehl</td><td>'.$typestring.$target_x.':'.$target_y.'</td></tr>';
	echo '<tr><td>Ankunft</td><td>'.strftime('%d.%m.%Y %X',$arrive).'</td></tr>';
	echo '<tr><td>R&#252;ckkehr</td><td>'.strftime('%d.%m.%Y %X',$back).'</td></tr>';
	echo '<tr><td>Truppen</td><td>';
	while(list($unit_id, $unit_count) = each($troops_array))
	{
		if($unit_count != 0)
		{
			echo $unit_count.' '.unit_name($unit_id).'<br />';
		}
	}
	echo '</td></tr>';
	echo '</table>';
}
elseif($status)
{
	$status_query = mysql_query("SELECT * FROM events_troops WHERE id='$status'");
	$status_array = mysql_fetch_array($status_query);
	$troops = $status_array["troops"];
	$troops_array = make_array($troops,5);

	$source_array = getcoords($status_array["source"]);
	$source_x = $source_array[0];
	$source_y = $source_array[1];
	$target_array = getcoords($status_array["target"]);
	$target_x = $target_array[0];
	$target_y = $target_array[1];

	if($status_array["type"] == 1)
	{
		$typestring = 'Angriff auf ';
	}
	elseif($status_array["type"] == 2)
	{
		$typestring = 'Angriff auf <a href="?seite=karte&stadt='.$status_array["source"].'">'.$source_x.':'.$source_y.'</a> war erfolgreich<br />';
		$typestring .= 'R&#252;ckkehr nach ';
	}

	echo '<table id="table01">';
	echo '<tr><th colspan="2">Status</th></tr>';
	echo '<tr><td>Befehl</td><td>'.$typestring.'<a href="?seite=karte&stadt='.$status_array["target"].'">'.$target_x.':'.$target_y.'</a></td></tr>';
	echo '<tr><td>Ankunft</td><td>'.strftime('%d.%m.%Y %X',$status_array["arrive"]).'</td></tr>';
	echo '<tr><td>R&#252;ckkehr</td><td>'.strftime('%d.%m.%Y %X',$status_array["back"]).'</td></tr>';
	echo '<tr><td>Truppen</td><td>';
	while(list($unit_id, $unit_count) = each($troops_array))
	{
		if($unit_count != 0)
		{
			echo $unit_count.' '.unit_name($unit_id).'<br />';
		}
	}
	echo '</td></tr>';
	if($status_array["fe"] != 0 || $status_array["h2o"] != 0 || $status_array["uran"] != 0)
	{
		echo '<tr><td>Rohstoffe</td><td>Eisen: '.$status_array["fe"].'<br />Wasser: '.$status_array["h2o"].'<br />Uran: '.$status_array["uran"].'</td></tr>';
	}
	echo '</table>';
}
else
{
	echo '<form method="POST" action="?seite=marsch&senden=ja"><table id="table01">';

	$city_troops_query = mysql_query("SELECT troops FROM cities WHERE id='$city'");
	$city_troops_query_array = mysql_fetch_array($city_troops_query);
	$city_troops	= $city_troops_query_array["troops"];
	$city_troops_array	= make_array($city_troops,5);

	$city_troops_array = produced($city_troops_array,$city);

	echo '<tr><th colspan="2">Ziel</th></tr>';
	echo '<tr><td>Aktion</td><td><input type="radio" name="action" value="attack" checked="checked">Angriff</td></tr>';
	echo '<tr><td>Ziel-Koordinaten</td><td><input type="text" size="5" maxlenght="3" name="x" value="'.$x.'">:<input type="text" size="5" maxlenght="3" name="y" value="'.$y.'"></td></tr>';
	echo '<tr><th colspan="2">Truppen</th></tr>';

	$units_query = mysql_query("SELECT id,name FROM units");
	$nounits = 1;
	while($units_row = mysql_fetch_object($units_query))
	{
		if($city_troops_array[$units_row->id] != 0)
		{
			echo '<tr><td>'.$units_row->name.'</td>';
			echo '<td><input type="text" name="troops['.$units_row->id.']" size="6" value="0" /> von '.$city_troops_array[$units_row->id].'</td></tr>';
			$nounits = 0;
		}
	}

	if($nounits == 1) { $disabled = 'disabled'; }

	echo '<tr><th colspan="2"><input type="submit" value=" Weiter " '.$disabled.' /></th></tr>';
	echo '</table></form>';
}

?>

