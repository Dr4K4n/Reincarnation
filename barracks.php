<?php

function unit_cost_array($unit_id,$city_id)
{
	$basecost_query = mysql_query("SELECT cost_fe,cost_h2o,cost_uran,duration FROM units WHERE `id`='$unit_id'");
	$basecost_array = mysql_fetch_array($basecost_query);

	$fe	= $basecost_array["cost_fe"];
	$h2o	= $basecost_array["cost_h2o"];
	$uran	= $basecost_array["cost_uran"];
	$time	= $basecost_array["duration"];

	$buildings_query = mysql_query("SELECT b7 FROM cities WHERE `id`='$city_id'");
	$buildings_array = mysql_fetch_array($buildings_query);

	$barracks_level = $buildings_array['b7'];

	$cost_array = array($fe,$h2o,$uran,unit_costs_time($time,$barracks_level));

	return $cost_array;
}

function max_unit_count($unit_id,$ressis_array,$city_id)
{
	$fe	= $ressis_array["fe"];
	$h2o	= $ressis_array["h2o"];
	$uran	= $ressis_array["uran"];

	$cost_array = unit_cost_array($unit_id,$city_id);

	$cost_fe	= $cost_array["fe"];
	$cost_h2o	= $cost_array["h2o"];
	$cost_uran	= $cost_array["uran"];

	if($cost_fe) { $count_fe = $fe/$cost_fe; } else { $count_fe = 9999999999; }
	if($cost_h2o) { $count_h2o = $h2o/$cost_h2o; } else { $count_h2o = 9999999999; }
	if($cost_uran) { $count_uran = $uran/$cost_uran; } else { $count_uran = 9999999999; }

	$max_array = array($count_fe,$count_h2o,$count_uran);
	sort($max_array);

	return intval($max_array[0]);
}

function produce($city_id,$troops_array,$ressis_array)
{
	while(list($unit_id, $unit_count) = each($troops_array))
	{
		if($unit_count != 0)
		{
			$ressis_array = ressis_now($ressis_array,$city_id);
			$time	= time();
	
			$cost_array = unit_cost_array($unit_id,$city_id);
			if($unit_count <= max_unit_count($unit_id,$ressis_array,$city_id))
			{
				## wir haben ihren Auftrag aufgenommen !
				$insert = mysql_query("INSERT INTO events_production (id,city_id,unit_id,count,time) VALUES ('','$city_id','$unit_id','$unit_count','$time');");

				## zahlen bitte !
				$new_fe = $ressis_array['fe'] - $cost_array[0]*$unit_count;
				$new_h2o = $ressis_array['h2o'] - $cost_array[1]*$unit_count;
				$new_uran = $ressis_array['uran'] - $cost_array[2]*$unit_count;
				$ress_update = mysql_query("UPDATE cities SET fe='$new_fe',h2o='$new_h2o',uran='$new_uran' WHERE id='$city_id'");

				return '<p>'.$unit_count.' mal Einheit vom Typ '.unit_name($unit_id).' in Auftrag gegeben</p>';
			}
			else
			{
				return 'Du hast nicht genug Rohstoffe!';
			}
		}
	}
}

function produced($city_troops_array,$city_id)
{
	$event_query = mysql_query("SELECT * FROM events_production WHERE city_id='$city_id' ORDER BY id");
	$time = time();
	$active = 1;
	while($event_row = mysql_fetch_object($event_query))
	{
	if($active != 0)
	{
		$event_id = $event_row->id;
		$unit_id = $event_row->unit_id;
		$unit_count = $event_row->count;
		$starttime = $event_row->time;

		if($active != 1)
		{
			$update = mysql_query("UPDATE events_production SET time='$active' WHERE id='$event_id'");
			$starttime = $active;
		}

		$unit_duration_query = mysql_query("SELECT duration FROM units WHERE id='$unit_id'");
		$unit_duration_array = mysql_fetch_array($unit_duration_query);
		$prodded_time = $unit_duration_array["duration"];
		$prodded_units = 0;
		$active = 0;
		$time_gone = $time - $starttime;

		while($prodded_time < $time_gone)
		{
			$prodded_units += 1;
			$prodded_time += $unit_duration_array["duration"];
		}

		if($prodded_units > 0 && $prodded_units < $unit_count)
		{
			## Event updaten
			$update_time = $starttime + $prodded_units*$unit_duration_array["duration"];
			$update_count = $unit_count - $prodded_units;
			$update = mysql_query("UPDATE events_production SET time = '$update_time', count = '$update_count' WHERE id='$event_id'");
			## Truppen updaten
			$city_troops_array[$unit_id] += $prodded_units;
		}
		elseif($prodded_units > 0 && $prodded_units >= $unit_count)
		{
			## Event loeschen
			$delete = mysql_query("DELETE FROM events_production WHERE id='$event_id'");
			## Truppen updaten
			$city_troops_array[$unit_id] += $unit_count;
			## Naechsten Auftrag in der Bauschleife ausfuehren
			$active = $starttime + $prodded_time;
		}

		## Komplettes Array mit allen Truppen in der Stadt
		$troops_new = '';
		for($x=1;$x<=5;$x++)
		{
			$troops_new .= $x.';'.$city_troops_array[$x]."\n";
		}
		$update = mysql_query("UPDATE cities SET troops = '$troops_new' WHERE id='$city_id'");
	}
	}
	return $city_troops_array;
}

?>

<script src="ticker.js" type="text/javascript"></script>

<h3>Milit&#228;rbasis</h3>

<?php

$produce = $_GET["produce"];
$action = $_GET["action"];

$city_buildings_query = mysql_query("SELECT b1,b2,b3,b4,b5,b6,b7,b8,b9,b10,b11,b12,b13,troops FROM cities WHERE id='$city'");
$city_buildings_array = mysql_fetch_array($city_buildings_query);
# TODO Troops
$city_troops	= $city_buildings_array["troops"];
$city_troops_array	= make_array($city_troops,5);
$city_troops_array = produced($city_troops_array,$city);

if($produce)
{
	$troops_array = $_POST["troops"];
	echo produce($city,$troops_array,$ressis_array);
}
elseif($action == 'abort')
{
	$del_id = $_GET['id'];
	$del_query = mysql_query("SELECT * FROM events_production WHERE id='$del_id'");
	$del_array = mysql_fetch_array($del_query);
	$count = $del_array['count'];
	$unit_id = $del_array['unit_id'];
	
	$unit_cost_array = unit_cost_array($unit_id,$city);
	$return_fe = $count*$unit_cost_array[0];
	$return_h2o = $count*$unit_cost_array[1];
	$return_uran = $count*$unit_cost_array[2];

	$new_fe = $ressis_array['fe'] + $return_fe;
	$new_h2o = $ressis_array['h2o'] + $return_h2o;
	$new_uran = $ressis_array['uran'] + $return_uran;

	$update_query = mysql_query("UPDATE cities SET fe='$new_fe',h2o='$new_h2o',uran='$new_uran' WHERE id='$city'");

	$del_query_del = mysql_query("DELETE FROM events_production WHERE id='$del_id'");
	echo '<p>Auftrag gelöscht! '.$count.' Einheiten</p>';
}



if($city_buildings_array[7] != 0)
{
	echo '<form action="?seite=militaerbasis&produce=yes" method="post" name="barracks"><table id="table01">';

	$gamer_research_query = mysql_query("SELECT research FROM gamer WHERE id='$userid'");
	$gamer_research_query_array = mysql_fetch_array($gamer_research_query);
	$gamer_research = $gamer_research_query_array["research"];
	$gamer_research_array = make_array($gamer_research,4);
	
	$units_query = mysql_query("SELECT * FROM units");
	while($units_row = mysql_fetch_object($units_query))
	{
		$required_research_array = make_array($units_row->required_research,14);
		$forschung = 1;
		while(list($req_res_id, $req_res_level) = each($required_research_array))
		{
			if($req_res_level > $gamer_research_array[$req_res_id]) { $forschung = 0; }
		}
		if($forschung == 1)
		{
			echo '<tr><td class="text"><h4><a href="?seite=technik&type=units&id='.$units_row->id.'">';
			echo $units_row->name.'</a> (Anzahl '.$city_troops_array[$units_row->id].')</h4>';
			echo '<p><b>Angriffswert: </b>'.$units_row->att.'</p>';
			echo '<p><b>Verteidigungswert: </b>'.$units_row->deff.'</p>';
			echo '<p><b>Ladekapazität: </b>'.$units_row->space.' Einheiten</p>';
			echo '<p><b>Geschwindigkeit: </b>'.$units_row->speed.' km/h</p>';
			echo '<p>'.nl2br($units_row->description).'</p>';
		
			echo '<p><b>Kosten: ';
			if($units_row->cost_fe != 0)   
			{ echo 'Eisen: </b>'.number_format($units_row->cost_fe,0,',','.').'<b> '; }
			if($units_row->cost_h2o != 0)  
			{ echo 'Wasser: </b>'.number_format($units_row->cost_h2o,0,',','.').'<b> '; }
			if($units_row->cost_uran != 0) 
			{ echo 'Uran: </b>'.number_format($units_row->cost_uran,0,',','.').'<b>'; }
			echo '</p><p><b>Dauer: '.gmstrftime('%X',unit_costs_time($units_row->duration,$city_buildings_array[7])).'</b></p></td><td class="link">';
			echo '<input type="text" maxlength="5" size="5" name="troops['.$units_row->id.']" />';
			echo ' (max. '.max_unit_count($units_row->id,$ressis_array,$city).')';
			echo '</td></tr>';
		}
	}
	echo '<tr><th colspan="2"><input type="submit" value=" Produzieren " /></th></tr>';
	echo '</table></form>';

	## Bauschleife
	echo '<table id="table01"><th colspan="3">Bauschleife</th>';
	echo '<tr><th>Einheiten</td><th>Dauer</td><th>Abbruch</td></tr>';
	$event_query = mysql_query("SELECT * FROM events_production WHERE city_id='$city' ORDER BY id");
	while($event_row = mysql_fetch_object($event_query))
	{
		$unit_cost_array = unit_cost_array($event_row->unit_id,$city);
		$units_cost_time = $event_row->count*$unit_cost_array[3];
		$tage = intval(gmstrftime('%j',$units_cost_time))-1;
		$restzeit = gmstrftime('%X',$units_cost_time).' Stunden';

		echo '<tr><td class="number">'.$event_row->count.' '.unit_name($event_row->unit_id).'</td><td class="number">';
		if($tage) { echo $tage.' Tage '; }
		echo $restzeit.'</td>';
		echo '<td><a href="?seite=militaerbasis&action=abort&id='.$event_row->id.'">abbrechen</a></td></tr>';
	}
	echo '</table>';
	echo '<script language="javascript">countdowns=1;countdown();</script>';
}
else { echo "<p>Du musst erst eine Milit&#228;rbasis bauen!</p>"; }

?>