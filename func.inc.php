<?php
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

## Allianz Funktionen

function alliancerank($rank)
{
	if($rank == 0) { return 'Mitglied'; }
	if($rank == 1) { return 'Administrator'; }
}

## Barracks Funktionen


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

## buildings Funktionen

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

## overview Funktionen

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

## research Funktionen

function required_research4research($gamer_researches_array,$required_buildings,$required_research,$city_buildings_array)
{
	$result = 'erfuellt';

	$required_buildings_array = make_array($required_buildings,14);
	$required_researches_array = make_array($required_research,4);

	while(list($req_build_id, $req_build_level) = each($required_buildings_array))
	{
		if($req_build_level > $city_buildings_array['b'+$req_build_id]) { $result = 'istnichtdrin'; }
	}
	while(list($req_res_id, $req_res_level) = each($required_researches_array))
	{
		if($req_res_level > $gamer_researches_array['r'+$req_res_id]) { $result = 'istnichtdrin'; }
	}

	return $result;
}

function research_cost_array($research_id,$user_id)
{
	$basecost_query = mysql_query("SELECT cost_fe,cost_h2o,cost_uran,duration FROM researches WHERE `id`='$research_id'");
	$basecost_array = mysql_fetch_array($basecost_query);

	$base_fe	= $basecost_array["cost_fe"];
	$base_h2o	= $basecost_array["cost_h2o"];
	$base_uran	= $basecost_array["cost_uran"];
	$base_time	= $basecost_array["duration"];

	$research_query = mysql_query("SELECT research FROM gamer WHERE `id`='$user_id'");
	$research_array = mysql_fetch_array($research_query);
	$research_array = make_array($research_array["research"],4);

	$level = $research_array[$research_id];


	$buildings_query = mysql_query("SELECT b8 FROM cities WHERE `id`='$city_id'");
	$buildings_array = mysql_fetch_array($buildings_query);

	$fz_level = $buildings_array['b8'];

	$cost_array = array(research_costs_fe($research_id,$base_fe,$level),research_costs_h2o($research_id,$base_h2o,$level),research_costs_uran($research_id,$base_uran,$level),research_costs_time($research_id,$base_time,$level,$fz_level));

	return $cost_array;
}

function research_canafford($research_id,$ressis_array,$energy_supplied,$city_id,$user_id)
{
	$ressis_array = ressis_now($ressis_array,$city_id);
	$fe	= $ressis_array["fe"];
	$h2o	= $ressis_array["h2o"];
	$uran	= $ressis_array["uran"];

	$cost_array	= research_cost_array($research_id,$user_id);
	$cost_fe	= $cost_array["fe"];
	$cost_h2o	= $cost_array["h2o"];
	$cost_uran	= $cost_array["uran"];

	if($fe >= $cost_fe && $h2o >= $cost_h2o && $uran >= $cost_uran)
	{
		return TRUE;
	}
	else
	{
		return FALSE;
	}
}

function search($user_id,$city_id,$research_id,$ressis_array)
{
	$event_query = mysql_query("SELECT id FROM events_research WHERE owner='$user_id'");
	if(mysql_num_rows($event_query) != 0)
	{
		return 'Du kannst nur eine Technologie zur Zeit forschen!';
	}
	elseif(research_canafford($research_id,$ressis_array,$energy_supplied,$city_id,$user_id) == FALSE)
	{
		return 'Du hast zu wenig Rohstoffe!';
	}
	else
	{
		$time = time();
		$cost_array	= research_cost_array($research_id,$user_id);
		$ressis_array	= ressis_now($ressis_array,$city_id);

		$new_fe		= $ressis_array["fe"] - $cost_array[0];
		$new_h2o	= $ressis_array["h2o"] - $cost_array[1];
		$new_uran	= $ressis_array["uran"] - $cost_array[2];
		$time_finish	= $time + $cost_array[3];

		$update = mysql_query("UPDATE cities SET `fe`='$new_fe', `h2o`='$new_h2o', `uran`='$new_uran', `time`='$time' WHERE `id`='$city_id'");
		$insert = mysql_query("INSERT INTO events_research (id,owner,research,time) VALUES ('','$user_id','$research_id','$time_finish')");
		if($insert != '')
		{
			return 'Technologie wird geforscht!';
		}
	}
}

## send_troops Funktionen

function space($troops_array,$space_array)
{
	$space = 0;
	for($x=1;$x<=count($space_array);$x++)
	{
		$space += $troops_array[$x]*$space_array[$x];
	}
	return $space;
}

## technik Funktionen

# Diese Funktion ist dazu da um die Strings aus der Datenbank wie "1;3" umzuwandeln in den
# dauzeghoerigen Text. Die 1 steht fuer die Gebaeudenummer und die 3 fuer die Ausbaustufe des
# Gebaeudes. Die Funktion guckt in der DB welcher Name zur id 1 gehoert und schmeisst das
# dann raus. Weiterhin zu beachten ist, dass die Funktion vorher noch ein array erstellt,
# in dem jeder eintrag eine Zeile des DB-Eintrages repraesentiert. *blubb*
function required_buildings($string)
{
	if($string != '')
	{
		$array = split("\n",$string);
		$returnstring = '';
		for($x=0;$x<count($array);$x++)
		{
			$parsed = split(";",$array[$x]);
			$building_id = $parsed[0];
			$building_query = mysql_query("SELECT name FROM buildings WHERE id='$building_id'");
			$building_array = mysql_fetch_array($building_query);
			$building = $building_array["name"];
			$level = $parsed[1];
			$returnstring .= $building.' Stufe '.$level.'<br />';
		}
		return $returnstring;
	}
}

function required_research($string)
{
	if($string != '')
	{
		$array = split("\n",$string);
		$returnstring = '';
		for($x=0;$x<count($array);$x++)
		{
			$parsed = split(";",$array[$x]);
			$research_id = $parsed[0];
			$research_query = mysql_query("SELECT name FROM researches WHERE id='$research_id'");
			$research_array = mysql_fetch_array($research_query);
			$research = $research_array["name"];
			$level = $parsed[1];
			$returnstring .= $research.' Stufe '.$level.'<br />';
		}
		return $returnstring;
	}
}

function prod($data_array,$buildings_array)
{
	return 'prod';
}
?>