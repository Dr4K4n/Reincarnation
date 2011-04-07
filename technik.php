<?php
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

echo '<h3>Technik</h3>';

$type = $_GET['type'];
$id = $_GET['id'];

if($type && $id)
{
	## Hier werden die Details zu einer bestimmten Technik aufgelistet!
	$data_query = mysql_query("SELECT * FROM $type WHERE id='$id'");
	$data_array = mysql_fetch_array($data_query);

	$city_query = mysql_query("SELECT buildings FROM cities WHERE id='$city'");
	$city_array = mysql_fetch_array($city_query);
	$buildings = make_array($city_array['buildings'],14);

	if($type == 'buildings')
	{
		$level = $buildings[$id];
		$cost_fe = number_format(building_costs_fe($id,$data_array['cost_fe'],$level),0,',','.');
		$cost_h2o = number_format(building_costs_h2o($id,$data_array['cost_h2o'],$level),0,',','.');
		$cost_uran = number_format(building_costs_uran($id,$data_array['cost_h2o'],$level),0,',','.');
		$cost_time = gmstrftime("%X",building_costs_time($id,$data_array['duration'],$buildings));
		$prod = prod($data_array,$buildings);
	}
	elseif($type == 'researches')
	{
		$gamer_query = mysql_query("SELECT research FROM gamer WHERE id='$userid'");
		$gamer_array = mysql_fetch_array($gamer_query);
		$researches = make_array($gamer_array['research'],4);
		$level = $researches[$id];
		$fz_level = $buildings[8];
		$cost_fe = number_format(research_costs_fe($id,$data_array['cost_fe'],$level),0,',','.');
		$cost_h2o = number_format(research_costs_h2o($id,$data_array['cost_h2o'],$level),0,',','.');
		$cost_uran = number_format(research_costs_uran($id,$data_array['cost_h2o'],$level),0,',','.');
		$cost_time = gmstrftime("%X",research_costs_time($id,$data_array['duration'],$level,$fz_level));
		$prod = prod($data_array,$researches);
	}
	if($type == 'units')
	{
		$cost_fe = number_format($data_array['cost_fe'],0,',','.');
		$cost_h2o = number_format($data_array['cost_h2o'],0,',','.');
		$cost_uran = number_format($data_array['cost_h2o'],0,',','.');
		$cost_time = '(Militärbasis Stufe '.$buildings[7].') '.gmstrftime("%X",unit_costs_time($data_array['duration'],$buildings[7]));
		$prod = $data_array['prod'];
		$level = -1;
	}

	echo '<table id="table01"><tr><th>'.$data_array['name'].'</th></tr>';
	echo '<tr><td>'.bbcode($data_array['description']).'</td></tr>';
	echo '<tr><td><b>Kosten:</b> ';
	if($level >= 0) { echo '(Stufe '.($level+1).') '; }
	if($data_array['cost_fe'] != 0)
	{ echo '<b>Eisen:</b> '.$cost_fe.' '; }
	if($data_array['cost_h2o'] != 0)
	{ echo '<b>Wasser:</b> '.$cost_h2o.' '; }
	if($data_array['cost_uran'] != 0)
	{ echo '<b>Uran:</b> '.$cost_uran.' '; }
	if($data_array['energy'] != 0)
	{ echo '<p><b>Energiebedarf:</b> '.$data_array['energy'].'</p>'; }
	if($data_array['duration'] != 0)
	{ echo '<p><b>Dauer:</b> '.$cost_time.'</p>'; }
	if($data_array['prod'] != 0)
	{ echo $prod; }
	
	if($data_array['points'] != 0)
	{ echo '</td></tr><tr><td>Punkte pro Stufe: '.$data_array['points']; }
	echo '</td></tr>';
	echo '</table>';
}
else
{
	echo '<table id="table01"><tr><th>Geb&#228;ude</th><th>Ben&#246;tigte Geb&#228;ude</th><th>Ben&#246;tigte Forschung</th></tr>';
	
	$buildings_query = mysql_query("SELECT * FROM buildings ORDER BY id");
	while($buildings_row = mysql_fetch_object($buildings_query))
	{
		echo '<tr>';
		echo '<td class="text"><a href="?seite=technik&type=buildings&id='.$buildings_row->id.'">'.$buildings_row->name.'</a></td>';
		echo '<td class="text">'.required_buildings($buildings_row->required_buildings).'</td>';
		echo '<td class="text">'.required_research($buildings_row->required_research).'</td>';
		echo '</tr>';
	}

	echo '</table><br /><table id="table01"><tr><th>Forschung</th><th>Ben&#246;tigte Geb&#228;ude</th><th>Ben&#246;tigte Forschung</th></tr>';

	$researches_query = mysql_query("SELECT * FROM researches ORDER BY id");
	while($researches_row = mysql_fetch_object($researches_query))
	{
		echo '<tr>';
		echo '<td class="text"><a href="?seite=technik&type=researches&id='.$researches_row->id.'">'.$researches_row->name.'</td>';
		echo '<td class="text">'.required_buildings($researches_row->required_buildings).'</td>';
		echo '<td class="text">'.required_research($researches_row->required_research).'</td>';
		echo '</tr>';
	}

	echo '</table><br /><table id="table01"><tr><th>Einheit</th><th>Ben&#246;tigte Forschung</th></tr>';

	$units_query = mysql_query("SELECT * FROM units ORDER BY id");
	while($units_row = mysql_fetch_object($units_query))
	{
		echo '<tr>';
		echo '<td class="text"><a href="?seite=technik&type=units&id='.$units_row->id.'">'.$units_row->name.'</td>';
		echo '<td class="text">'.required_research($units_row->required_research).'</td>';
		echo '</tr>';
	}
	echo '</table>';
}