<script src="ticker.js" type="text/javascript"></script>

<h3>Forschung</h3>

<table id="table01">
<?php
$search = $_GET["search"];
$action = $_GET["action"];

if($search)
{
	echo search($userid,$city,$search,$ressis_array);
}
elseif($action == 'abort')
{
	## irgendwo nen ganz hässlicher bug drinne, das $update query funzt nur wenn man das $delete query auskommentiert O_o
	$research_id = $_GET["id"];
	$research_cost_array = research_cost_array($research_id,$userid);
	$new_fe = round($ressis_array['fe'] + $research_cost_array[0],4);
	$new_h2o = round($ressis_array['h2o'] + $research_cost_array[1],4);
	$new_uran = round($ressis_array['uran'] + $research_cost_array[2],4);
	$update = mysql_query("UPDATE cities SET fe='$new_fe',h2o='$new_h2o',uran='$new_uran' WHERE id='$city';");
	$delete = mysql_query("DELETE FROM events_research WHERE owner='$userid';");
	if($delete) { echo 'Forschung abgebrochen!'; }
}


$city_buildings_query = mysql_query("SELECT b1,b2,b3,b4,b5,b6,b7,b8,b9,b10,b11,b12,b13 FROM cities WHERE id='$city'");
$city_buildings_array = mysql_fetch_array($city_buildings_query);

if($city_buildings_array['b8'] != 0)
{
	$gamer_research_query = mysql_query("SELECT research FROM gamer WHERE id='$userid'");
	$gamer_research_array = mysql_fetch_array($gamer_research_query);
	$gamer_researches = $gamer_research_array["research"];
	$gamer_researches_array = make_array($gamer_researches,4);

	$researches_query = mysql_query("SELECT * FROM researches ORDER BY id");
	while($researches_row = mysql_fetch_object($researches_query))
	{
		$required_buildings = $researches_row->required_buildings;
		$required_research = $researches_row->required_research;
		if(required_research4research($gamer_researches_array,$required_buildings,$required_research,$city_buildings_array) == 'erfuellt')
		{
			$research_level = $gamer_researches_array[$researches_row->id];
			echo '<tr><td class="text"><h4><a href="?seite=technik&type=researches&id='.$researches_row->id.'">';
			echo $researches_row->name.'</a> (Stufe '.trim($research_level).')</h4>';
			echo '<p>'.nl2br($researches_row->description).'</p>';

			echo '<p><b>Kosten: ';
			if($researches_row->cost_fe != 0)
			{ echo 'Eisen: </b>'.number_format(research_costs_fe($researches_row->id,$researches_row->cost_fe,$research_level),0,',','.').'<b> '; }
			if($researches_row->cost_h2o != 0)
			{ echo 'Wasser: </b>'.number_format(research_costs_h2o($researches_row->id,$researches_row->cost_h2o,$research_level),0,',','.').'<b> '; }
			if($researches_row->cost_uran != 0)
			{ echo 'Uran: </b>'.number_format(research_costs_uran($researches_row->id,$researches_row->cost_uran,$research_level),0,',','.').'<b>'; }

			echo '</p><p><b>Dauer: ';
			$duration = research_costs_time($researches_row->id,$researches_row->duration,$research_level,$city_buildings_array[8]);
			$duration_days = intval(gmstrftime("%j",$duration))-1;
			if($duration_days == 1) { echo $duration_days.' Tag '; }
			elseif($duration_days > 1) { echo $duration_days.' Tage '; }
			echo gmstrftime("%X",$duration);
			echo '</b></p></td><td class="link">';

			$event_query = mysql_query("SELECT * FROM events_research WHERE owner='$userid'");
			$event_array = mysql_fetch_array($event_query);

			if($event_array["research"] == '')
			{
				if(research_canafford($researches_row->id,$ressis_array,$energy_supplied,$city,$userid) == TRUE)
				{
					echo '<a href="?seite=forschung&search='.$researches_row->id.'">Forschung auf Stufe '.($research_level+1).'</a>';
				}
				else
				{
					echo 'Forschung auf Stufe '.($research_level+1);
				}
			}
			elseif($event_array["research"] == $researches_row->id)
			{
				$time = time();
				$countdown_time = $event_array["time"] - $time;
				if($countdown_time > 0)
				{
					echo '<div id="build1" title="'.$countdown_time.'"></div><a href="?seite=forschung&action=abort&id='.$researches_row->id.'">Abbrechen</a>';
				}
				else
				{
					#update_research($event_array["id"],$event_array["research"],$userid);
					echo 'Engine Error';
				}
			}
			else
			{
				echo 'Hier wird schon geforscht.';
			}


			echo '</td></tr>';
		}
	}
}
else
{
	echo 'Du musst erst ein Forschungszentrum bauen!';
}


echo '<script language="javascript">countdowns=1;countdown();</script>';
?>
</table>