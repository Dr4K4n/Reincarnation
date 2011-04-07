<?php

function new_messages($user_id)
{
	$new_messages_query = mysql_query("SELECT id,folder FROM messages WHERE `owner`='$user_id' AND `read`='0'");
	if(mysql_num_rows($new_messages_query) != 0)
	{
		while($new_messages_row = mysql_fetch_object($new_messages_query))
		{ $folder = $new_messages_row->folder; }
		$string = '<tr><td colspan="3"><a href="?seite=nachrichten&ordner='.$folder.'">Sie haben ';
		if(mysql_num_rows($new_messages_query) == 1) { $string .= 'eine neue Nachricht</a></td></tr>'; }
		else { $string .= mysql_num_rows($new_messages_query).' neue Nachrichten</a></td></tr>'; }
		return $string;
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

<h3>&#220;bersicht</h3>

<table id="table01">

<?php
$action = $_GET["action"];
$newname = $_POST["newname"];

$now_city_query = mysql_query("SELECT * FROM cities WHERE id='$city'");
$now_city = mysql_fetch_array($now_city_query);

if($action == 'rename')
{
	if($newname)
	{
		$update = mysql_query("UPDATE cities SET name='$newname' WHERE id='$city'");
		if($update) { echo 'Stadt umbenannt!'; }
	}
	else
	{
		echo '<form method="post" action="?seite=uebersicht&action=rename"';
		echo '<tr><th colspan="2">Stadt umbennen</th></tr>';
		echo '<tr><td>neuer Name:</td><td><input type="text" name="newname" size="35" maxlength="40" value="'.$now_city['name'].'"></td></tr>';
		echo '<tr><th colspan="2"><input type="submit" value=" umbennen "></th></tr>';
	}
}
else
{
	if($game_array['news'] != '')
	{
		echo '<tr><th colspan="3">NEWS</th></tr>';
		echo '<tr><td class="center" colspan="3">'.nl2br(htmlentities($game_array["news"],ENT_NOQUOTES,'utf-8')).'</td></tr>';
	}

	echo new_messages($userid);
	
	$countdown_id = 0;

	echo '<tr><th colspan="3"><a href="?seite=uebersicht&action=rename" title="Stadt umbennen">'.$now_city["name"].'</a> ('.$now_city["x"].':'.$now_city["y"].')</th></tr>';
	
	## Gebaeude die im Bau sind auflisten
	
	$event_buildings_query = mysql_query("SELECT * FROM events_buildings WHERE owner='$userid' ORDER BY time ASC");
	if(mysql_num_rows($event_buildings_query) != 0)
	{
		echo '<tr><td colspan="3" class="top">Geb&#228;ude im Bau</td></tr>';
		while($event_buildings_row = mysql_fetch_object($event_buildings_query))
		{
			$building = $event_buildings_row->building;
			$building_city = $event_buildings_row->city;
			$building_db_field = 'b'.$building;
	
			$city_query = mysql_query("SELECT id,name,x,y,".$building_db_field." FROM cities WHERE id='$building_city'");
			$city_query_array = mysql_fetch_array($city_query);
			$city_building_stufe = $city_query_array[$building_db_field];
	
			$nameandtime_query = mysql_query("SELECT name FROM buildings WHERE id='$building'");
			$nameandtime_array = mysql_fetch_array($nameandtime_query);
			$time = time();
			$countdown_time = ($event_buildings_row->time) - $time;
			if($countdown_time <= 0)
			{
				echo 'Kampfscript FEHLER!';
			}
			else
			{
				$countdown_id += 1;
				echo '<tr><td class="text"><div id="build'.$countdown_id.'" title="'.$countdown_time.'"></div></td>';
				echo '<td class="text" colspan="2">'.$nameandtime_array["name"].' (Stufe '.($city_building_stufe+1).') in "'.$city_query_array["name"].'" wird fertiggestellt.</td></tr>';
			}
		}
	}
	
	## aktuelle Forschungen sind auflisten
	
	$user_research_query = mysql_query("SELECT research FROM gamer WHERE id='$userid';");
	$user_research_array = mysql_fetch_array($user_research_query);
	
	## time ist Zeit wo der Auftrag zu Ende ist
	$event_research_query = mysql_query("SELECT * FROM events_research WHERE owner='$userid'");
	if(mysql_num_rows($event_research_query) != 0)
	{
		echo '<tr><td colspan="3" class="top">aktuelle Forschung</td></tr>';
		while($event_research_row = mysql_fetch_object($event_research_query))
		{
			$research = $event_research_row->research;
	
			$name_query = mysql_query("SELECT name FROM researches WHERE id='$research'");
			$name_array = mysql_fetch_array($name_query);
			$time = time();
			$countdown_time = $event_research_row->time - $time;
			if($countdown_time <= 0)
			{
				#update_research($event_research_row->id,$research,$userid);
				echo 'Engine Research Update missing';
			}
			else
			{
				# tut dieses Workaround Not ?!
				if($user_research_array["research"] == '')
				{
					$research_string = '';
					for($x=1;$x<=4;$x++)
					{
						$research_string .= $x.';0'."\n";
					}
					$update = mysql_query("UPDATE gamer SET research = '$research_string' WHERE id='$userid'");
					$user_research_array["research"] = $research_string;
				}
	
				$user_research = make_array($user_research_array["research"],4);
				$countdown_id += 1;
				echo '<tr><td class="text"><div id="build'.$countdown_id.'" title="'.$countdown_time.'"></div></td>';
				echo '<td class="text" colspan="2">'.$name_array["name"].' (Stufe '.($user_research[$research]+1).') wird erforscht.</td></tr>';
			}
		}
	}
	
	## Truppenbewegungen
	
	## ausgehende Truppen
	$troops_events_query = mysql_query("SELECT * FROM events_troops WHERE source_owner='$userid' AND type !='2' ORDER BY arrive ASC");
	if(mysql_num_rows($troops_events_query) != 0)
	{
		echo '<tr><td colspan="3" class="top">ausgehende Truppenbewegungen</td></tr>';
		while($troops_events_row = mysql_fetch_object($troops_events_query))
		{
			$time = time();
			$target_array = getcoords($troops_events_row->target);
			$target_x = $target_array[0];
			$target_y = $target_array[1];
			## Laufende Angriffe
			if($troops_events_row->type == '1')
			{
				$countdown_time = $troops_events_row->arrive - $time;
				if($countdown_time <= 0)
				{
					echo 'Kampfscript Fehler !';
				}
				else
				{
					$countdown_id += 1;
					echo '<tr><td class="text"><div id="build'.$countdown_id.'" title="'.$countdown_time.'"></div></td>';
					echo '<td class="text" colspan="2">Eine ihrer <a href="?seite=marsch&status='.$troops_events_row->id.'">Truppen</a> greift <a href="?seite=karte&stadt='.$troops_events_row->target.'">'.$target_x.':'.$target_y.'</a> an</td></tr>';
				}
			}
		}
	}
	
	## eingehende Truppen
	$troops_events_query2 = mysql_query("SELECT * FROM events_troops WHERE target_owner='$userid' ORDER BY arrive ASC");
	if(mysql_num_rows($troops_events_query2) != 0)
	{
		echo '<tr><td colspan="3" class="top">eingehende Truppenbewegungen</td></tr>';
		while($troops_events_row = mysql_fetch_object($troops_events_query2))
		{
			$time = time();
			$source_array = getcoords($troops_events_row->source);
			$source_x = $source_array[0];
			$source_y = $source_array[1];
			$target_id = $troops_events_row->target;
			$target_array = getcoords($target_id);
			$target_x = $target_array[0];
			$target_y = $target_array[1];
			## Laufende Angriffe
			if($troops_events_row->type == '1')
			{
				$countdown_time = $troops_events_row->arrive - $time;
				if($countdown_time <= 0)
				{
					echo 'Kampfscript Fehler !';
				}
				else
				{
					$countdown_id += 1;
					echo '<tr><td class="text"><div id="build'.$countdown_id.'" title="'.$countdown_time.'"></div></td>';
					echo '<td class="text" colspan="2">Ihre Stadt <a href="?newcity='.$target_id.'&oldsite=uebersicht">'.$target_x.':'.$target_y.'</a> wird von <a href="?seite=karte&stadt='.$troops_events_row->source.'">'.$source_x.':'.$source_y.'</a> angegriffen</td></tr>';
				}
			}
			## Zurueckkehrende Angriffe
			elseif($troops_events_row->type == '2')
			{
				$countdown_time = $troops_events_row->arrive - $time;
				if($countdown_time <= 0)
				{
					echo 'Kampfscript Fehler !';
				}
				else
				{
					$countdown_id += 1;
					echo '<tr><td class="text"><div id="build'.$countdown_id.'" title="'.$countdown_time.'"></div></td>';
					echo '<td class="text" colspan="2">Eine ihrer <a href="?seite=marsch&status='.$troops_events_row->id.'">Truppen</a> kehrt nach '.$target_x.':'.$target_y.' zur&#252;ck</td></tr>';
				}
			}
		}
	}
	
	## Truppen
	
	$city_troops_query = mysql_query("SELECT troops FROM cities WHERE id='$city'");
	$city_troops_query_array = mysql_fetch_array($city_troops_query);
	$city_troops	= $city_troops_query_array["troops"];
	$city_troops_array	= make_array($city_troops,5);
	$city_troops_array = produced($city_troops_array,$city);
	
	$units_query = mysql_query("SELECT id,name FROM units");
	$nounits = 1;
	while($units_row = mysql_fetch_object($units_query))
	{
		if($city_troops_array[$units_row->id] != 0)
		{
			if($nounits == 1) { echo '<tr><th colspan="3">Truppen</th></tr>'; }
			echo '<tr><td colspan="2">'.$units_row->name.'</td><td>'.$city_troops_array[$units_row->id].'</td></tr>';
			$nounits = 0;
		}
	}
	
	## Punkte
	
	echo '<tr><th colspan="3">Punkte</th></tr>';
	
	$city_points		= city_points($city);
	$cities_points		= cities_points($userid);
	$research_points	= research_points($userid);
	$all_points		= $cities_points + $research_points;
	
	echo '<tr><td colspan="2">Punkte dieser Stadt</td><td>'.$city_points.'</td></tr>';
	echo '<tr><td colspan="2">Punkte aller St&#228;dte</td><td>'.$cities_points.'</td></tr>';
	echo '<tr><td colspan="2">Forschungspunkte</td><td>'.$research_points.'</td></tr>';
	echo '<tr><td colspan="2">Gesamtpunkte</td><td>'.$all_points.'</td></tr>';
	
	echo '<script language="javascript">countdowns='.$countdown_id.';countdown();</script>';
}
?>

</table><br />