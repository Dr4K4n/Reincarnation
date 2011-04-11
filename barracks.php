<script src="ticker.js" type="text/javascript"></script>

<h3>Militärbasis</h3>

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
else { echo "<p>Du musst erst eine Militärbasis bauen!</p>"; }

?>