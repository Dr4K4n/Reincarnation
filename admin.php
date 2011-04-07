<?php

if($_SESSION['superuser'] == 1)
{
	$action = $_GET['action'];

	#### Game-Admin

	## Ingame Ankuendigung / Spiel sperren
	if($action == 'ingamenews')
	{
		if($_SESSION['s_game'] == 1)
		{
			echo '<h3>Ingame Ankündigung / Spiel sperren</h3>';
			$submit = $_POST['submit'];
			if($submit == ' ändern ')
			{
				$locked = $_POST['locked'];
				if($locked == 'on') { $locked = 1; } else { $locked = 0; }
				$news = $_POST['news'];
				$update = mysql_query("UPDATE game SET locked='$locked', news='$news'");
				if($update)
				{
					echo '<p>Änderungen erfolgreich</p>';
				}
			}
			else
			{
				$ingamenews_query = mysql_query("SELECT * FROM game");
				$ingamenews_array = mysql_fetch_array($ingamenews_query);
				if($ingamenews_array['locked'] == 1) { $checked = 'checked="checked" '; } else { $checked = ''; }
				echo '<form action="?seite=admin&action=ingamenews" method="post"><table id="table01">';
				echo '<tr><td>Spiel gesperrt?</td><td><input type="checkbox" name="locked" '.$checked.'/></td></tr>';
				echo '<tr><td>Ankündigung</td><td><textarea cols="50" rows="5" name="news">'.$ingamenews_array['news'].'</textarea></td></tr>';
				echo '<tr><th colspan="2"><input type="submit" name="submit" value=" ändern " /></th></tr>';
				echo '</table></form>';
			}
		}
	}
	elseif($action == 'welcome')
	{
		if($_SESSION['s_game'] == 1)
		{
			echo '<h3>Willkommen-Text ändern</h3>';
			$submit = $_POST['submit'];
			if($submit == ' ändern ')
			{
				$welcome = $_POST['welcome'];
				$update = mysql_query("UPDATE game SET welcome='$welcome'");
				if($update)
				{
					echo '<p>Änderungen erfolgreich</p>';
				}
			}
			else
			{
				$ingamenews_query = mysql_query("SELECT * FROM game");
				$ingamenews_array = mysql_fetch_array($ingamenews_query);
				echo '<form action="?seite=admin&action=welcome" method="post"><table id="table01">';
				echo '<tr><td>Ankündigung</td><td><textarea cols="50" rows="5" name="welcome">'.$ingamenews_array['welcome'].'</textarea></td></tr>';
				echo '<tr><th colspan="2"><input type="submit" name="submit" value=" ändern " /></th></tr>';
				echo '</table></form>';
			}
		}
	}
	elseif($action == 'player')
	{
		if($_SESSION['s_game'] == 1)
		{
			$do = $_GET['do'];
			$id = $_GET['id'];
			if($do == 'del')
			{
				$delete_gamer = mysql_query("DELETE FROM gamer WHERE id='$id'");
				$delete_cities = mysql_query("DELETE FROM cities WHERE owner='$id'");
				$delete_events_troops = mysql_query("DELETE FROM events_troops WHERE source_owner='$id'");
				$delete_events_buildings = mysql_query("DELETE FROM events_buildings WHERE owner='$id'");
				$delete_events_research = mysql_query("DELETE FROM events_research WHERE owner='$id'");
				$delete_messages = mysql_query("DELETE FROM messages WHERE owner='$id'");
				$delete_superuser = mysql_query("DELETE FROM superuser WHERE id='$id'");
			}
			else
			{
				## Suche nach Benutzern -> Liste mit Löschen, Sperren, Verwarnen
			}
		}
	}

	#### News-Admin

	## Neue News posten !
	elseif($action == 'postnews')
	{
		if($_SESSION['s_news'] == 1)
		{
			echo '<h3>News posten</h3>';
			$submit = $_POST['submit'];
			if($submit == ' posten ')
			{
				$title = $_POST['title'];
				$cat = $_POST['cat'];
				$short = $_POST['short'];
				$text = $_POST['text'];
				if($title == '') { echo 'Du hast keinen Titel angegeben!'; }
				elseif($cat == '') { echo 'Du hast keine Kategorie ausgewählt!'; }
				elseif($short == '') { echo 'Du hast keine Einleitung geschrieben!'; }
				elseif($text == '') { echo 'Du hast keinen Text geschrieben!'; }
				else
				{
					$time = time();
					$post = mysql_query("INSERT INTO news (title,cat,short,text,time,author) VALUES ('$title','$cat','$short','$text','$time','$user')");
					if($post) { echo 'News gepostet!'; }
				}
			}
			else
			{
				?>
				<form method="post" action="?seite=admin&action=postnews"><table id="table01">
				<tr><td>Titel:</td><td><input type="text" name="title" value="" size="50"></td></tr>
				<tr><td>Kategorie:</td><td><select name="cat">
				<?php
				$cat_query = mysql_query("SELECT * FROM news_cat");
				while($cat_row = mysql_fetch_object($cat_query))
				{
					echo '<option value="'.$cat_row->id.'">'.$cat_row->name.'</option>';
				}
				?>
				</select></td></tr>
				<tr><td>Einleitung:</td><td><textarea name="short" cols="60" rows="10"></textarea></td></tr>
				<tr><td>Text:</td><td><textarea name="text" cols="60" rows="20"></textarea></td></tr>
				<tr><th colspan="2"><input type="submit" name="submit" value=" posten "></th></tr>
				</table></form>
				<?php
			}
		}
	}

	#### Foren-Admin

	## Neues Forum erstellen!
	elseif($action == 'createforum')
	{
		if($_SESSION['s_forum'] == 1)
		{
			echo '<h3>Forum erstellen</h3>';
			$submit = $_POST['submit'];
			if($submit == ' erstellen ')
			{
				$title = $_POST['title'];
				$description = $_POST['description'];
				if($title == '') { echo 'Du hast keinen Titel eingegeben!'; }
				elseif($description == '') { echo 'Du hast keine Beschreibung eingegeben!'; }
				else
				{
					$create = mysql_query("INSERT INTO forum_forums (title,description) VALUES ('$title','$description')");
					if($create) { echo 'Forum erstellt!'; }
				}
			}
			else
			{
				?>
				<form method="post" action="?seite=admin&action=createforum"><table id="table01">
				<tr><td>Titel:</td><td><input type="text" name="title" value="" size="40" /></td></tr>
				<tr><td>Beschreibung:</td><td><input type="text" name="description" size="65" /></td></tr>
				<tr><th colspan="2"><input type="submit" name="submit" value=" erstellen "></th></tr>
				</table></form>
				<?php
			}
		}
	}
	elseif($action == 'sortforum')
	{
		if($_SESSION['s_forum'] == 1)
		{
			echo '<h3>Foren sortieren</h3>';
			$submit = $_POST['submit'];

			if($submit == ' übernehmen ')
			{
				$newpos = $_POST['newpos'];
				while(list($id, $pos) = each($newpos))
				{
					$update = mysql_query("UPDATE forum_forums SET pos='$pos' WHERE id='$id'");
				}
				echo '<p>Sortierung geändert</p>';
			}
			echo '<form action="?seite=admin&action=sortforum" method="post"><table id="table01">';
			echo '<tr><th>Forum</th><th>alte Position</th><th>neue Position</th></tr>';
			$forum_query = mysql_query("SELECT id,title,pos FROM forum_forums ORDER BY pos");
			$forum_count = mysql_num_rows($forum_query);
			while($forum_row = mysql_fetch_object($forum_query))
			{
				echo '<tr><td>'.$forum_row->title.'</td><td>'.$forum_row->pos.'</td><td><select name="newpos['.$forum_row->id.']">';
				for($i=1;$i<=$forum_count;$i++)
				{
					if($forum_row->pos == $i) { echo '<option value="'.$i.'" selected="selected">'.$i.'</option>'; }
					else { echo '<option value="'.$i.'">'.$i.'</option>'; }
				}
				echo '</select></td></tr>';
			}
			echo '<tr><th colspan="3"><input type="submit" name="submit" value=" übernehmen " /></th></tr>';
			echo '</table></form>';	
		}
	}


	## Admin Menü anzeigen !
	else
	{
		echo '<h3>Admin</h3>';
		if($_SESSION['s_game'] == 1)
		{
			$datei = fopen("lastupdate.txt","r");
			$inhalt = fread($datei, filesize("lastupdate.txt"));
			fclose($datei);
			echo 'Letztes Punkteupdate: '.strftime("%d.%m.%Y %X",intval($inhalt));
			?>
			<h4>Game-Admin</h4>
			<ul>
			  <li><a href="?seite=admin&action=welcome">Willkommen-Text &auml;ndern</a></li>
			  <li><a href="?seite=admin&action=ingamenews">Ingame-Ank&uuml;ndigung / Spiel sperren</a></li>
			  <li><a href="?seite=admin&action=player">Spieler-Verwaltung</a></li>
			</ul>
			<?php
		}
		if($_SESSION['s_news'] == 1)
		{
			?>
			<h4>News-Admin</h4>
			<ul>
			  <li><a href="?seite=admin&action=postnews">News schreiben</a></li>
			</ul>
			<?php
		}
		if($_SESSION['s_forum'] == 1)
		{
			?>
			<h4>Foren-Admin</h4>
			<ul>
			  <li><a href="?seite=admin&action=createforum">Neues Forum erstellen</a></li>
			  <li><a href="?seite=admin&action=sortforum">Foren sortieren</a></li>
			</ul>
			<?php
		}
	}
}
else
{
	include("error.php");
}

?>