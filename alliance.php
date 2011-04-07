<h3>Allianz</h3>

<table id="table01">
<?php

function alliancerank($rank)
{
	if($rank == 0) { return 'Mitglied'; }
	if($rank == 1) { return 'Administrator'; }
}

$id = $_GET["id"];
$action = $_GET["action"];

if(!$id)
{
	$gamer_query = mysql_query("SELECT alliance FROM alliances_members WHERE gamer='$userid'");
	$gamer_array = mysql_fetch_array($gamer_query);
	$id = $gamer_array['alliance'];
}

if($action == 'austreten')
{
	$gamer_query = mysql_query("DELETE FROM alliances_members WHERE gamer='$userid'");
	echo '<p>Du hast die Allianz verlassen!</p>';
}
elseif($action == 'found')
{
	$submit = $_POST['submit'];
	if($submit == ' gründen ')
	{
		$name = $_POST['name'];
		$tag = $_POST['tag'];
		$alliance_name_exists = mysql_query("SELECT id FROM alliances WHERE name='$name'");
		$alliance_tag_exists = mysql_query("SELECT id FROM alliances WHERE tag='$tag'");
		if(mysql_num_rows($alliance_name_exists) == 0 && mysql_num_rows($alliance_tag_exists) == 0)
		{
			$i_alliance_query = mysql_query("INSERT INTO alliances (name,tag,founder) VALUES ('$name','$tag','$userid')");
			$alliance_query = mysql_query("SELECT id FROM alliances WHERE tag='$tag' AND founder='$userid'");
			$alliance_array = mysql_fetch_array($alliance_query);
			$alliance_id = $alliance_array['id'];
			$member_gamer_query = mysql_query("INSERT INTO alliances_members VALUES ('$alliance_id','$userid','1')");
		}
		else
		{
			echo 'Eine Allianz mit diesem Namen oder Tag existiert bereits.';
		}
	}
	else
	{
		echo '<form method="post" action="?seite=allianz&action=found"><table id="table01">';
		echo '<tr><th colspan="2">Angaben zur Allianz</th></tr>';
		echo '<tr><td>Allianzname</td><td><input type="text" name="name" maxlength="100" size="50" /></td></tr>';
		echo '<tr><td>Allianztag</td><td><input type="text" name="tag" maxlength="10" size="10" /></td></tr>';
		echo '<tr><th colspan="2"><input type="submit" name="submit" value=" gründen " /></th></tr>';
		echo '</table></form>';
	}
}
elseif($action == 'bewerben')
{
	$submit = $_POST['submit'];
	if($submit == ' bewerben ')
	{
		$alliance_id = $_POST['alliance'];
		$text = $_POST["text"];
		$application_query = mysql_query("INSERT INTO alliances_applications (alliance,userid,text) VALUES ('$alliance_id','$userid','$text')");
		echo 'Bewerbung abgeschickt!';
	}
	else
	{
		echo '<form method="post" action="?seite=allianz&action=bewerben"><table id="table01">';
		echo '<tr><th colspan="2">Bewerbung</th></tr>';
		echo '<tr><td>Text</td><td><textarea name="text" rows="15" cols="70"></textarea></td></tr>';
		echo '<input type="hidden" name="alliance" value="'.$_GET['id'].'" />';
		echo '<tr><th colspan="2"><input type="submit" name="submit" value=" bewerben " /></th></tr>';
		echo '</table></form>';
	}
}
elseif($action == 'search')
{
	$submit = $_POST['submit'];
	if($submit == ' suchen ')
	{
		$name = "%".$_POST['name']."%";
		$tag = "%".$_POST['tag']."%";
		echo '<table id="table01"><tr><th colspan="2">Suchergebnis</th></tr>';
		$shown = array();
		if($name != '%%')
		{
			$name_query = mysql_query("SELECT id,name,tag FROM alliances WHERE name LIKE '$name'");
			while($name_row = mysql_fetch_object($name_query))
			{
				echo '<tr><td>[<a href="?seite=allianz&id='.$name_row->id.'">'.$name_row->tag.'</a>]</td>';
				echo '<td><a href="?seite=allianz&id='.$name_row->id.'">'.$name_row->name.'</a></td></tr>';
				array_push($shown,$name_row->id);
			}
		}
		if($tag != '%%')
		{
			$tag_query = mysql_query("SELECT id,name,tag FROM alliances WHERE tag LIKE '$tag'");
			while($tag_row = mysql_fetch_object($tag_query))
			{
				if(!in_array($tag_row->id,$shown))
				{
					echo '<tr><td>[<a href="?seite=allianz&id='.$tag_row->id.'">'.$tag_row->tag.'</a>]</td>';
					echo '<td><a href="?seite=allianz&id='.$tag_row->id.'">'.$tag_row->name.'</a></td></tr>';
				}
			}
		}
	}
	else
	{
		echo '<form method="post" action="?seite=allianz&action=search"><table id="table01">';
		echo '<tr><th colspan="2">Suchanfrage</th></tr>';
		echo '<tr><td>Allianzname</td><td><input type="text" name="name" maxlength="100" size="50" /></td></tr>';
		echo '<tr><td>Allianztag</td><td><input type="text" name="tag" maxlength="10" size="10" /></td></tr>';
		echo '<tr><th colspan="2"><input type="submit" name="submit" value=" suchen " /></th></tr>';
		echo '</table></form>';
	}
}
elseif($action == 'text')
{
	$submit = $_POST['submit'];
	if($submit == ' übernehmen ')
	{
		$text = $_POST['text'];
		$update_query = mysql_query("UPDATE alliances SET text='$text' WHERE id='$id'");
		echo '<p>Allianz-Text geändert!</p>';
	}
	else
	{
		$alliance_query = mysql_query("SELECT * FROM alliances WHERE id='$id'");
		$alliance_array = mysql_fetch_array($alliance_query);
		echo '<form method="post" action="?seite=allianz&action=text"><table id="table01">';
		echo '<tr><th colspan="2">Allianz-Text ändern</th></tr>';
		echo '<tr><td>Allianzname</td><td><textarea cols="60" rows="5" name="text">'.$alliance_array['text'].'</textarea></td></tr>';
		echo '<tr><th colspan="2"><input type="submit" name="submit" value=" übernehmen " /></th></tr>';
		echo '</table></form>';
	}
}
elseif($action == 'rename')
{
	$submit = $_POST['submit'];
	if($submit == ' umbenennen ')
	{
		$newname = $_POST['name'];
		$update_query = mysql_query("UPDATE alliances SET name='$newname' WHERE id='$id'");
		echo '<p>Allianz umbenannt!</p>';
	}
	else
	{
		$alliance_query = mysql_query("SELECT * FROM alliances WHERE id='$id'");
		$alliance_array = mysql_fetch_array($alliance_query);
		echo '<form method="post" action="?seite=allianz&action=rename"><table id="table01">';
		echo '<tr><th colspan="2">Allianz umbenennen</th></tr>';
		echo '<tr><td>Allianzname</td><td><input type="text" name="name" maxlength="100" size="50" value="'.$alliance_array['name'].'" /></td></tr>';
		echo '<tr><th colspan="2"><input type="submit" name="submit" value=" umbenennen " /></th></tr>';
		echo '</table></form>';
	}
}
elseif($action == 'retag')
{
	$submit = $_POST['submit'];
	if($submit == ' umbenennen ')
	{
		$newtag = $_POST['tag'];
		$update_query = mysql_query("UPDATE alliances SET tag='$newtag' WHERE id='$id'");
		echo '<p>Allianz-Tag geändert!</p>';
	}
	else
	{
		$alliance_query = mysql_query("SELECT * FROM alliances WHERE id='$id'");
		$alliance_array = mysql_fetch_array($alliance_query);
		echo '<form method="post" action="?seite=allianz&action=retag"><table id="table01">';
		echo '<tr><th colspan="2">Allianz-Tag ändern</th></tr>';
		echo '<tr><td>Allianzname</td><td><input type="text" name="tag" maxlength="100" size="50" value="'.$alliance_array['tag'].'" /></td></tr>';
		echo '<tr><th colspan="2"><input type="submit" name="submit" value=" umbenennen " /></th></tr>';
		echo '</table></form>';
	}
}
elseif($action == 'logo')
{
	$submit = $_POST['submit'];
	if($submit == ' hochladen ')
	{
		$max_byte_size = 50000;
		$allowed_types = "(jpg|jpeg)";
	
		if(is_uploaded_file($_FILES["logo"]["tmp_name"]))
		{
			if(preg_match("/\." . $allowed_types . "$/i", $_FILES["logo"]["name"]))
			{
				if($_FILES["logo"]["size"] <= $max_byte_size)
				{
					if(copy($_FILES["logo"]["tmp_name"], 'pics/alliances/'.$id.'.jpg'))
					{
						echo '<p>Allianz-Logo hochgeladen!</p>';
					}
					else { echo 'hochladen fehlgeschlagen!'; }
				}
				else { echo 'Logo zu groß, max. '.($max_byte_size/1024).' KB.'; }
			}
			else { echo 'Logo hat falschen Dateityp, nur jpeg ist erlaubt!'; }
		} else { print_R($_FILES['logo']);}
	}
	else
	{
		$alliance_query = mysql_query("SELECT * FROM alliances WHERE id='$id'");
		$alliance_array = mysql_fetch_array($alliance_query);
		echo '<form method="post" action="?seite=allianz&action=logo" enctype="multipart/form-data"><table id="table01">';
		echo '<tr><th colspan="2">Allianz-Logo</th></tr>';
		if(file_exists('pics/alliances/'.$id.'.jpg'))
		{ echo '<tr><td colspan="2"><img src="pics/alliances/'.$id.'.jpg" /></td></tr>'; }
		echo '<tr><td>Allianz-Logo</td><td><input type="file" name="logo" /></td></tr>';
		echo '<tr><th colspan="2"><input type="submit" name="submit" value=" hochladen " /></th></tr>';
		echo '</table></form>';
	}
}
elseif($action == 'bewerbungen')
{
	$submit = $_POST['submit'];
	$application_id = $_GET['id'];
	if($submit == ' annehmen ')
	{
		$application_query = mysql_query("SELECT * FROM alliances_applications WHERE id='$application_id'");
		$application_array = mysql_fetch_array($application_query);
		$alliance = $application_array['alliance'];
		$member_id = $application_array['userid'];
		$member_query = mysql_query("INSERT INTO alliances_members (alliance,gamer,rank) VALUES ('$alliance','$member_id','0')");

		$alliance_del = mysql_query("DELETE FROM alliances_applications WHERE id='$application_id'");
		echo getusername($member_id).' wurde in die Allianz aufgenommen!';
	}
	elseif($submit == ' ablehnen ')
	{
		$alliance_del = mysql_query("DELETE FROM alliances_applications WHERE id='$application_id'");
		echo 'Die Bewebung von '.getusername($member_id).' wurde abgelehnt!';
	}
	elseif($application_id)
	{
		$application_query = mysql_query("SELECT * FROM alliances_applications WHERE id='$application_id'");
		$application_array = mysql_fetch_array($application_query);
		echo '<form method="post" action="?seite=allianz&action=bewerbungen&id='.$application_id.'"><table id="table01">';
		echo '<tr><th colspan="2">Bewerbung bearbeiten</th></tr>';
		echo '<tr><td>Name</td><td><a href="?seite=karte&spieler='.$application_array['userid'].'">';
		echo getusername($application_array['userid']).'</a></td></tr>';
		echo '<tr><td>Bewerbungstext</td><td>'.$application_array['text'].'</td></tr>';
		echo '<tr><th colspan="2"><input type="submit" name="submit" value=" annehmen " />';
		echo ' <input type="submit" name="submit" value=" ablehnen " /></th></tr>';
		echo '</table></form>';
	}
	else
	{
		$application_query = mysql_query("SELECT * FROM alliances_applications WHERE alliance='$id'");
		while($application_row = mysql_fetch_object($application_query))
		{
		echo '<p><form method="post" action="?seite=allianz&action=bewerbungen&id='.$application_row->id.'"><table id="table01">';
		echo '<tr><th colspan="2">Bewerbung bearbeiten</th></tr>';
		echo '<tr><td>Name</td><td><a href="?seite=karte&spieler='.$application_row->userid.'">';
		echo getusername($application_row->userid).'</a></td></tr>';
		echo '<tr><td>Bewerbungstext</td><td>'.$application_row->text.'</td></tr>';
		echo '<tr><th colspan="2"><input type="submit" name="submit" value=" annehmen " />';
		echo ' <input type="submit" name="submit" value=" ablehnen " /></th></tr>';
		echo '</table></form></p><br />';
		}
	}
}
elseif($id)
{
	$alliance_query = mysql_query("SELECT * FROM alliances WHERE id='$id'");
	$alliance_array = mysql_fetch_array($alliance_query);

	echo '<tr><th colspan="2">['.$alliance_array['tag'].'] - '.$alliance_array['name'].'</th></tr>';
	if(file_exists('pics/alliances/'.$alliance_array['id'].'.jpg'))
	{
		echo '<tr><td colspan="2" class="center"><img src="pics/alliances/'.$alliance_array['id'].'.jpg" /></td></tr>';
	}
	echo '<tr><td>Gründer</td><td><a href="?seite=karte&spieler='.$alliance_array['founder'].'">'.getusername($alliance_array['founder']).'</a></td></tr>';

	$member_query = mysql_query("SELECT * FROM alliances_members WHERE alliance='$id'");
	$member_count = mysql_num_rows($member_query);
	$member_array = array();
	while($member_row = mysql_fetch_object($member_query))
	{
		$member_array[$member_row->gamer] = $member_row->rank;
	}

	$application_query = mysql_query("SELECT * FROM alliances_applications WHERE alliance='$id'");
	$application_count = mysql_num_rows($application_query);
	echo '<tr><td>Mitglieder</td><td>'.$member_count;
	if($member_array[$userid] == 1)
	{
		if($application_count == 1)
		{
			$application_array = mysql_fetch_array($application_query);
			echo ' (eine <a href="?seite=allianz&action=bewerbungen&id='.$application_array['id'].'">Bewerbung</a>)';
		}
		elseif($application_count != 0) { echo ' ('.$application_count.' <a href="?seite=allianz&action=bewerbungen">Bewerbungen</a>)'; }
	}
	echo '</td></tr>';

	if($gamer_array['alliance'] == $id)
	{
		echo '<tr><td colspan="2" class="center">Du bist Mitglied in dieser Allianz</td></tr>';
		echo '<tr><td>Dein Rang</td><td>'.alliancerank($member_array[$userid]).'</td></tr>';
		if($member_array[$userid] == 1)
		{
			echo '<tr><td colspan="2" class="center"><a href="?seite=allianz&action=rename">Name ändern</a> - ';
			echo '<a href="?seite=allianz&action=retag">Tag ändern</a> - ';
			echo '<a href="?seite=allianz&action=logo">Allianz-Logo</a> - ';
			echo '<a href="?seite=allianz&action=text">Allianz-Text</td></tr>';
		}
	}

	echo '<tr><th colspan="2">Allianztext</th></tr>';
	echo '<tr><td class="center" colspan="2">'.bbcode($alliance_array['text']).'</td></tr>';

	echo '<tr><th colspan="2">Mitglieder</th></tr>';
	echo '<tr><td class="top">Name</td><td class="top">Rang</td></tr>';
	$member_id_array = array_keys($member_array);
	for($i=0;$i<count($member_array);$i++)
	{
		$member_id = $member_id_array[$i];
		echo '<tr><td><a href="?seite=karte&spieler='.$member_id.'">'.getusername($member_id).'</a></td>';
		echo '<td>'.alliancerank($member_array[$member_id]).'</td></tr>';
	}

	if($gamer_array['alliance'] == $id)
	{
		echo '<tr><td class="center" colspan="2"><a href="?seite=allianz&action=austreten">Allianz verlassen</a></td></tr>';
	}
	elseif($gamer_array['alliance'] == 0)
	{
		$application_gamer_query = mysql_query("SELECT * FROM alliances_applications WHERE alliance='$id' AND userid='$userid'");
		if(mysql_num_rows($application_gamer_query) != 0)
		{
			echo '<tr><td class="center" colspan="2">Du hast dich bei dieser Allianz beworben!</td></tr>';
		}
		else
		{
			echo '<tr><td class="center" colspan="2"><a href="?seite=allianz&action=bewerben&id='.$alliance_array['id'].'">Bewerbung schreiben</a></td></tr>';
		}
	}
}
else
{
	$application_query = mysql_query("SELECT * FROM alliances_applications WHERE userid='$userid'");
	if(mysql_num_rows($application_query) != 0)
	{
		echo '<table id="table01"><tr><th colspan="2">Bewerbungen</th></tr>';
		while($application_row = mysql_fetch_object($application_query))
		{
			$id = $application_row->alliance;
			$alliance_query = mysql_query("SELECT name FROM alliances WHERE id='$id'");
			$alliance_array = mysql_fetch_array($alliance_query);
			echo '<tr><td><a href="?seite=allianz&id='.$id.'">'.$alliance_array['name'].'</a></td>';
			echo '<td><a href="?seite=allianz&action=delapp">löschen</a></td></tr>';
		}
		echo '</table>';
	}
	echo '<p><a href="?seite=allianz&action=search">Suchen</a> / <a href="?seite=allianz&action=found">Gründen</a></p>';
}

?>
</table>