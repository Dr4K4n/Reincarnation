<?php
$city_coords_array 	= getcoords($city);
$city_coords_x		= $city_coords_array[0];
$city_coords_y		= $city_coords_array[1];

$start_x		= $_GET["startx"];
$start_y		= $_GET["starty"];
$stadt_id		= $_GET["stadt"];
$spieler_id		= $_GET["spieler"];

if($stadt_id)
{
	## Profil einer Stadt ausgeben
	$city_query = mysql_query("SELECT * FROM cities WHERE `id`='$stadt_id'");
	$city_array = mysql_fetch_array($city_query);
	$spieler_id = $city_array['owner'];

	$user_query = mysql_query("SELECT * FROM gamer WHERE `id`='$spieler_id'");
	$user_array = mysql_fetch_array($user_query);

	$member_query = mysql_query("SELECT * FROM alliances_members WHERE gamer='$spieler_id'");
	$member_array = mysql_fetch_array($member_query);
	$alliance = $member_array['alliance'];
	$alliance_query = mysql_query("SELECT tag FROM alliances WHERE id='$alliance'");
	$alliance_array = mysql_fetch_array($alliance_query);
	if($alliance_array['tag'] != '') { $alliance_tag = '[<a href="?seite=allianz&id='.$alliance.'">'.$alliance_array['tag'].'</a>] '; }

	echo '<h3>Stadt: '.$city_array["name"].'</h3>';

	echo '<table id="table01">';
	echo '<tr><td>Koordinaten</td><td class="number">'.$city_array["x"].':'.$city_array["y"].'</td></tr>';
	echo '<tr><td>Besitzer</td><td class="text">'.$alliance_tag;
	echo ' <a href="?seite=karte&spieler='.$city_array["owner"].'">'.getusername($city_array["owner"]).'</a></td></tr>';
	echo '<tr><td>Punkte</td><td class="number">'.city_points($stadt_id).' Punkte</td></tr>';
	echo '<tr><td colspan="2"><a href="?seite=marsch&x='.$city_array["x"].'&y='.$city_array["y"].'">Truppen in diese Stadt schicken</a></td></tr>';
	echo '</table>';
}
elseif($spieler_id)
{
	## Profil eines Spielers ausgeben
	$user_query = mysql_query("SELECT * FROM gamer WHERE `id`='$spieler_id'");
	$user_array = mysql_fetch_array($user_query);
	
	$member_query = mysql_query("SELECT * FROM alliances_members WHERE gamer='$spieler_id'");
	$member_array = mysql_fetch_array($member_query);
	$alliance = $member_array['alliance'];
	$alliance_query = mysql_query("SELECT * FROM alliances WHERE id='$alliance'");
	$alliance_array = mysql_fetch_array($alliance_query);
	if($alliance_array['tag'] != '') { $alliance_tag = '[<a href="?seite=allianz&id='.$alliance.'">'.$alliance_array['tag'].'</a>] '; }

	$cities_query = mysql_query("SELECT id,name,buildings FROM cities WHERE `owner`='$spieler_id'");

	echo '<h3>Spieler: '.$alliance_tag.$user_array["user"].'</h3>';

	echo '<table id="table01">';
	echo '<tr><th colspan="2">St&#228;dte des Spielers</th></tr>';

	while($cities_row = mysql_fetch_object($cities_query))
	{
		echo '<tr><td><a href="?seite=karte&stadt='.$cities_row->id.'">'.$cities_row->name.'</a></td>';
		echo '<td class="number">'.city_points($cities_row->id).' Punkte</td></tr>';
	}

	$cities_points		= cities_points($spieler_id);
	$research_points	= research_points($spieler_id);
	$all_points		= $cities_points + $research_points;

	echo '<tr><th colspan="2">Profil</th></tr>';
	echo '<tr><td>Alter</td><td class="number">';
	if($user_array["age"] == 0) { echo 'keine Angabe'; } else { echo $user_array["age"].' Jahre'; }
	echo '</td></tr>';
	echo '<tr><td>Geschlecht</td><td class="number">'.gender($user_array["gender"]).'</td></tr>';
	echo '<tr><td>ICQ</td><td class="number">';
	if($user_array["icq"] == 0) { echo 'keine Angabe'; } else { echo $user_array["icq"]; }
	echo '</td></tr>';
	echo '<tr><td colspan="2"><a href="?seite=nachrichten&to='.$spieler_id.'">Nachricht schreiben</a></td></tr>';
	if(file_exists('pics/gamer/'.$user_array['id'].'.jpg')) { echo '<tr><td colspan="2" class="center"><img src="pics/gamer/'.$user_array['id'].'.jpg" /></td></tr>'; }
	echo '<tr><th colspan="2">Profiltext</th></tr>';
	echo '<tr><td colspan="2" class="text">'.nl2br($user_array["comment"]).'</td></tr>';
	echo '<tr><th colspan="2">Punkte</th></tr>';
	echo '<tr><td>Punkte aller St&#228;dte</td><td class="number">'.$cities_points.' Punkte</td></tr>';
	echo '<tr><td>Forschungspunkte</td><td class="number">'.$research_points.' Punkte</td></tr>';
	echo '<tr><td>Gesamtpunkte</td><td class="number">'.$all_points.' Punkte</td></tr>';
	echo '</table>';
}
else
{
	echo '<h3>Karte</h3>';

	// Berechnung der Koordinaten des oberen linken Feldes der Karte
	//  damit auch nur Felder angezeigt werden, die auch wirklich existieren und nicht z.B. -1:0
	if(!$start_x && !$start_y)
	{
		if($city_coords_x < 3)
		{
			$start_x = 1;
		}
		elseif($city_coords_x > 98)
		{
			$start_x = 96;
		}
		else
		{
			$start_x = $city_coords_x - 2;
		}
		
		if($city_coords_y < 3)
		{
			$start_y = 1;
		}
		elseif($city_coords_y > 98)
		{
			$start_y = 96;
		}
		else
		{
			$start_y = $city_coords_y - 2;
		}
	}
	
	// Anzeigen des Navigationskreuzes
	
	echo '<table border="0" class="mapnavi"><tr><td colspan="2" rowspan="2"></td>';
	if($start_x > 5)
	{
		echo '<td><a href="?seite=karte&startx='.($start_x-5).'&starty='.$start_y.'">';
		echo '<img src="pics/map/navi_top2.gif" /></a></td>';
	}
	else
	{
		echo '<td><img src="pics/map/navi_top2.gif" /></td>';
	}
	echo '<td colspan="2" rowspan="2"></td></tr><tr>';
	if($start_x > 1)
	{
		echo '<td><a href="?seite=karte&startx='.($start_x-1).'&starty='.$start_y.'"><img src="pics/map/navi_top.gif" /></a></td>';
	}
	else
	{
		echo '<td><img src="pics/map/navi_top.gif" /></td>';
	}
	echo '</tr><tr>';
	if($start_y > 5)
	{
		echo '<td><a href="?seite=karte&startx='.$start_x.'&starty='.($start_y-5).'"><img src="pics/map/navi_left2.gif" /></a></td>';
	}
	else
	{
		echo '<td><img src="pics/map/navi_left2.gif" /></td>';
	}
	if($start_y > 1)
	{
		echo '<td><a href="?seite=karte&startx='.$start_x.'&starty='.($start_y-1).'"><img src="pics/map/navi_left.gif" /></a></td>';
	}
	else
	{
		echo '<td><img src="pics/map/navi_left.gif" /></td>';
	}
	echo '<td><a href="?seite=karte"><img src="pics/map/navi_center.gif" /></a></td>';
	if($start_y < 96)
	{
		echo '<td><a href="?seite=karte&startx='.$start_x.'&starty='.($start_y+1).'"><img src="pics/map/navi_right.gif" /></a></td>';
	}
	else
	{
		echo '<td><img src="pics/map/navi_right.gif" /></td>';
	}
	if($start_y < 92)
	{
		echo '<td><a href="?seite=karte&startx='.$start_x.'&starty='.($start_y+5).'"><img src="pics/map/navi_right2.gif" /></a></td>';
	}
	else
	{
		echo '<td><img src="pics/map/navi_right2.gif" /></td>';
	}
	echo '</tr><tr><td colspan="2" rowspan="2"></td>';
	if($start_x < 96)
	{
		echo '<td><a href="?seite=karte&startx='.($start_x+1).'&starty='.$start_y.'"><img src="pics/map/navi_down.gif" /></a></td>';
	}
	else
	{
		echo '<td><img src="pics/map/navi_down.gif" /></td>';
	}
	echo '<td colspan="2" rowspan="2"></td></tr><tr>';
	if($start_x < 92)
	{
		echo '<td><a href="?seite=karte&startx='.($start_x+5).'&starty='.$start_y.'">';
		echo '<img src="pics/map/navi_down2.gif" /></a></td>';
	}
	else
	{
		echo '<td><img src="pics/map/navi_down2.gif" /></td>';
	}
	echo '</tr></table><br /><br />';
	
	// Anzeigen der Karte als Tabelle
	
	echo '<table id="map">';
	echo '<tr><th> </th><th class="top">'.$start_y.'</th><th class="top">'.($start_y+1).'</th>';
	echo '<th class="top">'.($start_y+2).'</th><th class="top">'.($start_y+3).'</th><th class="top">'.($start_y+4).'</th></tr>';
	for($x=$start_x;$x<$start_x+5;$x++)
	{
		echo '<tr>';
		echo '<th class="left">'.$x.'</th>';
		for($y=$start_y;$y<$start_y+5;$y++)
		{
			$city_query = mysql_query("SELECT * FROM cities WHERE x=$x AND y=$y");
			if(mysql_num_rows($city_query) > 0)
			{
				$city_array = mysql_fetch_array($city_query);
				if($city_array['id'] == $city) { $class = 'home'; }
				elseif($city_array['owner'] == getuserid($user)) { $class = 'mine'; }
				else { $class = 'used'; }
				$gamer_id = $city_array["owner"];
				$member_query = mysql_query("SELECT alliance FROM alliances_members WHERE gamer='$gamer_id'");
				$member_array = mysql_fetch_array($member_query);
				$alliance = $member_array['alliance'];
				$alliance_query = mysql_query("SELECT tag FROM alliances WHERE id='$alliance'");
				$alliance_array = mysql_fetch_array($alliance_query);
				if($alliance_array['tag']) { $alliance_string = '['.$alliance_array['tag'].'] '; }
				else { $alliance_string = ''; }
				$text = 'bla';
				$text = $x.':'.$y.'<br />'.str_replace("'","\'",$city_array['name']).'<br />';
				$text .= 'Besitzer: '.$alliance_string.getusername($city_array["owner"]);
				$text .= '<br />'.city_points($city_array["id"]).' Punkte';
				echo '<td><a href="?seite=karte&stadt='.$city_array["id"].'" onmouseover="return escape(\''.$text.'\')">';
				echo '<img src="pics/map/'.$class.'.jpg" /></a></td>';
			}
			else
			{
				echo '<td><img src="pics/map/free.jpg" /></td>';
			}
		}
		echo '</tr>';
	}
	echo '</table>';
	echo '<script language="JavaScript" type="text/javascript" src="map_tooltip.js"></script>';
}
?>