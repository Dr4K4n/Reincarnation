<?php

$action = $_GET["action"];

if($action == 'delpost')
{
	$delid = $_GET['id'];
	if($_SESSION['s_forum'] == 1)
	{
		mysql_query("DELETE FROM forum_posts WHERE id='$delid'");
		echo '<p>Posting gelöscht!</p>';
	}
}
elseif($action == 'delthread')
{
	$delid = $_GET['id'];
	if($_SESSION['s_forum'] == 1)
	{
		mysql_query("DELETE FROM forum_threads WHERE id='$delid'");
		mysql_query("DELETE FROM forum_posts WHERE thread_id='$delid'");
		echo '<p>Thread und Postings gelöscht!</p>';
	}
}
elseif($action == 'delforum')
{
	$delid = $_GET['id'];
	if($_SESSION['s_forum'] == 1)
	{
		mysql_query("DELETE FROM forum_forums WHERE id='$delid'");
		mysql_query("DELETE FROM forum_threads WHERE forum_id='$delid'");
		mysql_query("DELETE FROM forum_posts WHERE forum_id='$delid'");
		echo '<p>Forum, Threads und Postings gelöscht!</p>';
	}
}
elseif($action == 'thread')
{
	$thread_id = $_GET['id'];
	$thread_query = mysql_query("SELECT * FROM forum_threads WHERE id=$thread_id");
	$thread_array = mysql_fetch_array($thread_query);
	echo '<h3>'.$thread_array['title'].'</h3>';

	echo '<table id="table01">';
	$posts_query = mysql_query("SELECT * FROM forum_posts WHERE thread_id=$thread_id");
	while($posts_row = mysql_fetch_object($posts_query))
	{
		$timestring = strftime('%H:%M:%S am %d.%m.%Y',$posts_row->time);
		$author_id = getuserid($posts_row->author);
		echo '<tr><th>Post von <a href="?seite=karte&spieler='.$author_id.'">'.$posts_row->author.'</a> um '.$timestring.'</th></tr>';
		echo '<tr><td>'.bbcode($posts_row->text).'</td></tr>';
		if($_SESSION['s_forum'] == 1 || $author_id == $userid)
		{
			echo '<tr><td>Diesen Beitrag: <a href="?seite=forum&action=delpost&id='.$posts_row->id.'">l&ouml;schen</a> ';
			echo '<a href="?seite=forum&action=edit&id='.$posts_row->id.'">bearbeiten</a></td></tr>';
		}
	}
	echo '</table>';
	echo '<p><a href="?seite=forum&action=post&forum='.$thread_array['forum_id'].'&id='.$thread_id.'">Antworten</a></p>';
	echo '<p><a href="?seite=forum">Zur&uuml;ck zum Forum</a></p>';
}
elseif($action == 'newthread')
{
	$submit = $_POST['submit'];
	if($submit == ' Thema erstellen ')
	{
		$forum = $_GET['forum'];
		$title = $_POST['title'];
		$text = $_POST['text'];
		$time = time();
		mysql_query("INSERT INTO forum_threads (forum_id,title,author) VALUES ('$forum','$title','$user')");
		$id_query = mysql_query("SELECT id FROM forum_threads WHERE title='$title' AND author='$user'");
		$id_array = mysql_fetch_array($id_query);
		$thread_id = $id_array['id'];
		mysql_query("INSERT INTO forum_posts (forum_id,thread_id,text,author,time) VALUES ('$forum','$thread_id','$text','$user','$time')");
		echo 'Thread erstellt! <a href="?seite=forum">Zur&uuml;ck zum Forum</a>';
	}
	else
	{
		echo '<h3>Neues Thema erstellen</h3>';
		echo '<form action="?seite=forum&action=newthread&forum='.$_GET['forum'].'" method="post"><table id="table01">';
		echo '<tr><td>Thema</td><td><input type="text" name="title" maxlength="100"></td></tr>';
		echo '<tr><td>Text</td><td><textarea cols="50" rows="5" name="text"></textarea></td></tr>';
		echo '<tr><td colspan="2"><input type="submit" name="submit" value=" Thema erstellen "></td></tr>';
		echo '</form></table>';
	}
}
elseif($action == 'post')
{
	$thread_id = $_GET['id'];
	$forum_id = $_GET['forum'];
	$submit = $_POST['submit'];
	if($submit == ' Antworten ')
	{
		$text = $_POST['text'];
		$time = time();
		mysql_query("INSERT INTO forum_posts (forum_id,thread_id,text,author,time) VALUES ('$forum_id','$thread_id','$text','$user','$time')");
		echo 'Antwort erstellt! <a href="?seite=forum">Zur&uuml;ck zum Forum</a>';
	}
	else
	{
		echo '<h3>Antworten</h3>';
		echo '<form action="?seite=forum&action=post&forum='.$_GET['forum'].'&id='.$thread_id.'" method="post"><table id="table01">';
		echo '<tr><td>Text</td><td><textarea cols="60" rows="5" name="text"></textarea></td></tr>';
		echo '<tr><td>BB-Codes</td><td>';
		echo '<p>[u]Text[/u] - unterstrichener Text</p>';
		echo '<p>[i]Text[/i] - kursiver Text</p>';
		echo '<p>[b]Text[/b] - fettgedruckter Text</p>';
		echo '</td></tr>';
		echo '<tr><td colspan="2"><input type="submit" name="submit" value=" Antworten "></td></tr>';
		echo '</form></table>';
	}

}
elseif($action == 'edit')
{
	$post_id = $_GET['id'];
	$submit = $_POST['submit'];
	if($submit == ' Übernehmen ')
	{
		$text = $_POST['text'];
		$time = time();
		$text .= "\n\n[i]Beitrag editiert von ".$user." am ".strftime('%d.%m.%Y um %H:%M:%S',$time).".[/i]";
		mysql_query("UPDATE forum_posts SET text='$text' WHERE id='$post_id'");
		echo 'Posting bearbeitet! <a href="?seite=forum">Zur&uuml;ck zum Forum</a>';
	}
	else
	{
		$post_query = mysql_query("SELECT text FROM forum_posts WHERE id='$post_id'");
		$post_array = mysql_fetch_array($post_query);
		echo '<h3>Posting bearbeiten</h3>';
		echo '<form action="?seite=forum&action=edit&id='.$post_id.'" method="post"><table id="table01">';
		echo '<tr><td>Text</td><td><textarea cols="60" rows="5" name="text">'.$post_array["text"].'</textarea></td></tr>';
		echo '<tr><td>BB-Codes</td><td>';
		echo '<p>[u]Text[/u] - unterstrichener Text</p>';
		echo '<p>[i]Text[/i] - kursiver Text</p>';
		echo '<p>[b]Text[/b] - fettgedruckter Text</p>';
		echo '</td></tr>';
		echo '<tr><td colspan="2"><input type="submit" name="submit" value=" Übernehmen "></td></tr>';
		echo '</form></table>';
	}

}
elseif($action == 'forum')
{
	$forum_id = $_GET['id'];
	echo '<h3>Forum</h3>';
	echo '<p><a href="?seite=forum&action=newthread&forum='.$forum_id.'">Neues Thema starten</a></p>';
	echo '<table id="table01">';
	echo '<tr><th width="55%">Titel</th><th width="25%">Autor</th><th width="20%">Beiträge</th></tr>';
	$threads_query = mysql_query("SELECT * FROM forum_threads WHERE forum_id='$forum_id' ORDER BY id DESC");
	while($threads_row = mysql_fetch_object($threads_query))
	{
		$thread_id = $threads_row->id;
		$posts_query = mysql_query("SELECT id FROM forum_posts WHERE thread_id = '$thread_id'");
		$posts = mysql_num_rows($posts_query);
		echo '<tr><td><a href="?seite=forum&action=thread&id='.$threads_row->id.'">'.$threads_row->title.'</a></td>';
		echo '<td><a href="?seite=karte&spieler='.getuserid($threads_row->author).'">'.$threads_row->author.'</a></td><td>'.$posts.'</td></tr>';
		if($_SESSION['s_forum'] == 1)
		{
			echo '<tr><td colspan="3">Dieses Thema: <a href="?seite=forum&action=delthread&id='.$threads_row->id.'">l&ouml;schen</a></td></tr>';
		}
	}
	echo '</table>';
	echo '<p><a href="?seite=forum&action=newthread&forum='.$forum_id.'">Neues Thema starten</a></p>';
}
else
{
	echo '<h3>Forum</h3>';
	echo '<table id="table01">';
	echo '<tr><th width="55%">Titel</th><th width="25%">Themen</th><th width="20%">Beiträge</th></tr>';
	$forums_query = mysql_query("SELECT * FROM forum_forums ORDER BY pos");
	while($forums_row = mysql_fetch_object($forums_query))
	{
		$forum_id = $forums_row->id;
		$threads_query = mysql_query("SELECT id FROM forum_threads WHERE forum_id='$forum_id'");
		$threads = mysql_num_rows($threads_query);
		$posts_query = mysql_query("SELECT id FROM forum_posts WHERE forum_id='$forum_id'");
		$posts = mysql_num_rows($posts_query);
		echo '<tr><td><a href="?seite=forum&action=forum&id='.$forums_row->id.'">'.$forums_row->title.'</a>';
		
		echo '<br />'.$forums_row->description.'</td><td>'.$threads.'</a></td><td>'.$posts.'</td></tr>';
		if($_SESSION['s_forum'] == 1)
		{
			echo '<tr><td colspan="3">Dieses Forum: <a href="?seite=forum&action=delforum&id='.$forums_row->id.'">l&ouml;schen</a></td></tr>';
		}
	}
	echo '</table>';
}
?>
