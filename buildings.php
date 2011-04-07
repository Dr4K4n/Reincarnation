<?php

function required_research4building($city_buildings_array,$required_buildings_array,$required_research_array,$userid)
{
	$result = 'erfuellt';

	$gamer_research_query = mysql_query("SELECT r1,r2,r3,r4,r5 FROM gamer_research WHERE gamer_id='$userid'");
	$gamer_research_array = mysql_fetch_array($gamer_research_query);

	while(list($req_build_id, $req_build_level) = each($required_buildings_array))
	{
		if($req_build_level > $city_buildings_array[$req_build_id]) { $result = 'istnichtdrin'; }
	}
	while(list($req_res_id, $req_res_level) = each($required_research_array))
	{
		if($req_res_level > $gamer_research_array[$req_res_id]) { $result = 'istnichtdrin'; }
	}

	return $result;
}

function building_cost_array($building_id,$city_id)
{
	$basecost_query = mysql_query("SELECT cost_fe,cost_h2o,cost_uran,energy,duration FROM buildings WHERE `id`='$building_id'");
	$basecost_array = mysql_fetch_array($basecost_query);

	$base_fe	= $basecost_array["cost_fe"];
	$base_h2o	= $basecost_array["cost_h2o"];
	$base_uran	= $basecost_array["cost_uran"];
	$base_energy	= $basecost_array["energy"];
	$base_time	= $basecost_array["duration"];

	$building_id_db_field = 'b'.$building_id;
	$buildings_query = mysql_query("SELECT b1,".$building_id_db_field." FROM cities WHERE `id`='$city_id'");
	$buildings_array = mysql_fetch_array($buildings_query);
	$level = $buildings_array[$building_id_db_field];

	$cost_array = array(
		"fe" => building_costs_fe($building_id,$base_fe,$level),
		"h2o" => building_costs_h2o($building_id,$base_h2o,$level),
		"uran" => building_costs_uran($building_id,$base_uran,$level),
		"energy" => building_costs_energy($building_id,$base_energy,$level),
		"time" => building_costs_time($building_id,$base_time,$buildings_array)
	);

	return $cost_array;
}

function building_canafford($building_id,$ressis_array,$energy_supplied,$city_id)
{
	$fe	= $ressis_array["fe"];
	$h2o	= $ressis_array["h2o"];
	$uran	= $ressis_array["uran"];

	$cost_array	= building_cost_array($building_id,$city_id);
	$cost_fe	= $cost_array["fe"];
	$cost_h2o	= $cost_array["h2o"];
	$cost_uran	= $cost_array["uran"];
	$cost_energy	= $cost_array["energy"];

	if($cost_energy != 0)
	{
		$city_query = mysql_query("SELECT b1,b2,b3,b4,b5,b6,b7,b8,b9,b10,b11,b12,b13 FROM cities WHERE id='$city_id'");
		$city_array = mysql_fetch_array($city_query);
		$energy_needed = energy_needed($city_array);
	}

	if($fe >= $cost_fe && $h2o >= $cost_h2o && $uran >= $cost_uran && $energy_supplied >= $energy_needed+$cost_energy)
	{
		return TRUE;
	}
	else
	{
		return FALSE;
	}
}

function build($user_id,$city_id,$building_id,$ressis_array,$energy_supplied)
{
	$event_query = mysql_query("SELECT id FROM events_buildings WHERE city='$city_id'");
	if(mysql_num_rows($event_query) != 0)
	{
		return 'In dieser Stadt wird bereits gebaut!';
	}
	elseif(building_canafford($building_id,$ressis_array,$energy_supplied,$city_id) == FALSE)
	{
		return 'Du hast zu wenig Rohstoffe oder zu wenig Energie!';
	}
	else
	{
		$time = time();
		$cost_array	= building_cost_array($building_id,$city_id);
		$ressis_array	= ressis_now($ressis_array,$city_id);

		$new_fe		= $ressis_array["fe"] - $cost_array["fe"];
		$new_h2o	= $ressis_array["h2o"] - $cost_array["h2o"];
		$new_uran	= $ressis_array["uran"] - $cost_array["uran"];
		$time_finish	= $time + $cost_array["time"];

		$update = mysql_query("UPDATE cities SET `fe`='$new_fe', `h2o`='$new_h2o', `uran`='$new_uran', `time`='$time' WHERE `id`='$city_id'");
		$insert = mysql_query("INSERT INTO events_buildings (owner,city,building,time) VALUES ('$user_id','$city_id','$building_id','$time_finish')");
		if($insert != '')
		{
			return 'Geb&#228;ude in Auftrag gegeben!';
		}
		else { return 'DB Fehler'."INSERT INTO events_buildings (owner,city,building,time) VALUES ('$user_id','$city_id','$building_id','$time_finish')"; }
	}
}

?>
<script src="ticker.js" type="text/javascript"></script>

<h3>Geb&auml;ude</h3>

<table id="table01">
<?php
$build = $_GET["build"];
$action = $_GET["action"];


$event_query = mysql_query("SELECT * FROM events_buildings WHERE city='$city'");
$event_array = mysql_fetch_array($event_query);

$city_buildings_query = mysql_query("SELECT b1,b2,b3,b4,b5,b6,b7,b8,b9,b10,b11,b12,b13 FROM cities WHERE id='$city'");
$city_buildings_array = mysql_fetch_array($city_buildings_query);

if($build)
{
	echo build($userid,$city,$build,$ressis_array,$energy_supplied);
}
elseif($action == 'abort')
{
	# ressis zurueck
	$buildung_cost_array = building_cost_array($event_array['building'],$city);
	$new_fe = $ressis_array['fe'] + $buildung_cost_array['fe'];
	$new_h2o = $ressis_array['h2o'] + $buildung_cost_array['h2o'];
	$new_uran = $ressis_array['uran'] + $buildung_cost_array['uran'];
	$update = mysql_query("UPDATE cities SET fe='$new_fe',h2o='$new_h2o',uran='$new_uran' WHERE id='$city'");
	$delete = mysql_query("DELETE FROM events_buildings WHERE city='$city'");
	if($delete) { echo 'Bauauftrag abgebrochen!'; }
}

$buildings_query = mysql_query("SELECT * FROM buildings ORDER BY id");
while($buildings_row = mysql_fetch_object($buildings_query))
{
	$required_buildings_array = array(
		$buildings_row->b1,
		$buildings_row->b2,
		$buildings_row->b3,
		$buildings_row->b4,
		$buildings_row->b5,
		$buildings_row->b6,
		$buildings_row->b7,
		$buildings_row->b8,
		$buildings_row->b9,
		$buildings_row->b10,
		$buildings_row->b11,
		$buildings_row->b12,
		$buildings_row->b13
	);
	$required_research_array = array(
		$buildings_row->r1,
		$buildings_row->r2,
		$buildings_row->r3,
		$buildings_row->r4,
		$buildings_row->r5
	);
	if(required_research4building($city_buildings_array,$required_buildings_array,$required_research_array,$userid) == 'erfuellt')
	{
		$building_level = $city_buildings_array['b'.$buildings_row->id];
		echo '<tr><td class="text"><h4><a href="?seite=technik&type=buildings&id='.$buildings_row->id.'">'.$buildings_row->name.'</a>';
		echo ' (Stufe '.trim($building_level).')</h4>';
		echo '<p>'.nl2br($buildings_row->description).'</p>';
		echo '<p><b>Kosten: </b>';
		if($buildings_row->cost_fe != 0)
		{ echo '<b>Eisen: </b>'.number_format(building_costs_fe($buildings_row->id,$buildings_row->cost_fe,$building_level),0,',','.').' '; }
		if($buildings_row->cost_h2o != 0)
		{ echo '<b>Wasser: </b>'.number_format(building_costs_h2o($buildings_row->id,$buildings_row->cost_h2o,$building_level),0,',','.').' '; }
		if($buildings_row->cost_uran != 0) 
		{ echo '<b>Uran: </b>'.number_format(building_costs_uran($buildings_row->id,$buildings_row->cost_uran,$building_level),0,',','.'); }
		echo '</p>';
		if($buildings_row->energy != 0) { echo '<p><b>Energiebedarf:</b> '.$buildings_row->energy.'</p>'; }
		
		# Bauzeit ausgeben
		echo '<p><b>Dauer:</b> ';
		$duration = building_costs_time($buildings_row->id,$buildings_row->duration,$city_buildings_array);
		$duration_days = intval(gmstrftime("%j",$duration))-1;
		if($duration_days == 1) { echo $duration_days.' Tag '; }
		elseif($duration_days > 1) { echo $duration_days.' Tage '; }
		echo gmstrftime('%X',$duration).'</p></td><td class="link">';

		# überprüfen ob dieses Gebäude grade ausgebaut wird
		$event_query = mysql_query("SELECT * FROM events_buildings WHERE city='$city'");
		$event_array = mysql_fetch_array($event_query);

		if($event_array["building"] == '')
		{
			if(building_canafford($buildings_row->id,$ressis_array,$energy_supplied,$city) == TRUE)
			{
				echo '<a href="?seite=gebaeude&build='.$buildings_row->id.'">';
				echo 'Ausbau auf Stufe '.($building_level+1).'</a>';
			}
			else
			{
				echo 'Ausbau auf Stufe '.($building_level+1);
			}
		}
		elseif($event_array["building"] == $buildings_row->id)
		{
			$time = time();
			$countdown_time = ($event_array["time"]) - $time;
			if($countdown_time > 0)
			{
				echo '<div id="build1" title="'.$countdown_time.'"></div><a href="?seite=gebaeude&action=abort">Abbrechen</a>';
			}
			else
			{
				echo 'Kampfscript FEHLER!';
			}
		}
		else
		{
			echo 'Hier wird schon gebaut.';
		}
		echo '</td></tr>';
	}
}

echo '<script language="javascript">countdowns=1;countdown();</script>';
?>
</table>
