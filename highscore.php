<h3>Highscore</h3>

<p><a href="?seite=highscore&highscore=spieler">Spieler-Highscore</a></p>
<p><a href="?seite=highscore&highscore=stadt">Städte-Highscore</a></p>

<br />
<table id="table01">


<?php
$highscore = $_GET["highscore"];
if(!$highscore) { $highscore = 'spieler'; }

if($highscore == 'stadt')
{
	echo '<tr><th colspan="4">St&#228;dte Highscore</th></tr>';
	echo '<tr><td>#</td><td>Stadt</td><td>Besitzer</td><td>Punkte</td></tr>';

	$cities_query = mysql_query("SELECT id,name,owner,points FROM cities ORDER BY points DESC LIMIT 100");
	$x = 0;
	while($cities_row = mysql_fetch_object($cities_query))
	{
		$x++;
		$owner_id = $cities_row->owner;
		$owner_name = getusername($owner_id);
		echo '<tr><td class="number">'.$x.'</td>';
		echo '<td class="text"><a href="?seite=karte&stadt='.$cities_row->id.'">'.$cities_row->name.'</a></td>';
		echo '<td class="text"><a href="?seite=karte&spieler='.$owner_id.'">'.$owner_name.'</a></td>';
		echo '<td class="number">'.$cities_row->points.'</td></tr>';
	}
}
elseif($highscore == 'spieler' OR $highscore == 'forscher' OR $highscore == 'staedte')
{
	echo '<tr><th colspan="5">Spieler Highscore</th></tr>';
	echo '<tr><td>#</td>';
	echo '<td>Spieler</td>';
	echo '<td><a href="?seite=highscore&highscore=staedte">Städtepunkte</a></td>';
	echo '<td><a href="?seite=highscore&highscore=forscher">Forschungspunkte</a></td>';
	echo '<td><a href="?seite=highscore&highscore=spieler">Gesamtpunkte</a></td></tr>';

	$orderby = "points DESC, research_points DESC";
	if($highscore == 'forscher')
	{
		$orderby = "research_points DESC, points DESC";
	}
	elseif($highscore == 'staedte')
	{
		$orderby = "buildings_points DESC, points DESC";
	}

	$gamer_query = mysql_query("SELECT id,user,buildings_points,research_points,points FROM gamer ORDER BY $orderby LIMIT 100");
	$x = 0;
	while($gamer_row = mysql_fetch_object($gamer_query))
	{
		$x++;
		echo '<tr><td class="number">'.$x.'</td>';
		echo '<td class="text"><a href="?seite=karte&spieler='.$gamer_row->id.'">'.$gamer_row->user.'</a></td>';
		echo '<td class="number">'.$gamer_row->buildings_points.'</td>';
		echo '<td class="number">'.$gamer_row->research_points.'</td>';
		echo '<td class="number">'.$gamer_row->points.'</td></tr>';
	}
}

?>
</table>