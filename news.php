<?php

$action = $_GET['action'];

if($action == 'del')
{
	$news_id = $_GET['id'];
	if($_SESSION['s_news'] == 1)
	{
		$del_query = mysql_query("DELETE FROM news WHERE id='$news_id'");
		if($del_query) { echo 'News gelöscht'; }
	}
}
elseif($action == 'show')
{
	$newsid = $_GET["id"];
	$news_query = mysql_query("SELECT * FROM news WHERE id='$newsid'");
	$news_array = mysql_fetch_array($news_query);

	echo '<table id="news">';
	echo '<tr><th colspan="2">'.$news_array["title"].'</th></tr>';
	echo '<tr><td>'.nl2br($news_array["short"]).'<br /><br />'.nl2br($news_array["text"]);
	
	$category = $news_array["cat"];
	$cat_news_query = mysql_query("SELECT * FROM news_cat WHERE id='$category'");
	$cat_news_daten = mysql_fetch_array($cat_news_query);
	echo '<td width="100">';
	echo '<img src="pics/news_cat/'.$cat_news_daten["pic"].'" alt="'.$cat_news_daten["name"].'" /></td></tr>';

	$author = $news_array["author"];
	$author_news_query = mysql_query("SELECT user FROM gamer WHERE id='$author'");
	$author_news_array = mysql_fetch_array($author_news_query);
	echo '<tr><td colspan="2" class="small">News geschrieben am '.strftime("%d.%m.%Y um %X",$news_row->time).' von '.$author_news_array["user"].'</td></tr>';
	if($_SESSION['s_news'] == 1) { echo '<tr><td colspan="2">Diese News: <a href="?seite=news&action=del&id='.$cat_news_daten['id'].'">löschen</a></td></tr>'; }
	echo '</table>';
}
else
{
	$news_query = mysql_query("SELECT * FROM news ORDER BY id DESC");
	while($news_row = mysql_fetch_object($news_query))
	{
		echo '<table id="news">';
		echo '<tr><th colspan="2">'.$news_row->title.'</th></tr>';
		echo '<tr><td>'.nl2br($news_row->short);
		echo '<p><a href="?seite=news&action=show&id='.$news_row->id.'">more</a></p></td>';

		$category = $news_row->cat;
		$cat_news_query = mysql_query("SELECT * FROM news_cat WHERE id='$category'");
		$cat_news_daten = mysql_fetch_array($cat_news_query);
		echo '<td width="100">';
		echo '<img src="pics/news_cat/'.$cat_news_daten["pic"].'" alt="'.$cat_news_daten["name"].'" /></td></tr>';

		$author = $news_row->author;
		$author_news_query = mysql_query("SELECT user FROM gamer WHERE id='$author'");
		$author_news_array = mysql_fetch_array($author_news_query);
		echo '<tr><td colspan="2" class="small">News geschrieben am '.strftime("%d.%m.%Y um %X",$news_row->time).' von '.$author_news_array["user"].'</td></tr>';
		if($_SESSION['s_news'] == 1) { echo '<tr><td colspan="2">Diese News: <a href="?seite=news&action=del&id='.$news_row->id.'">löschen</a></td></tr>'; }
		echo '</table>';
	}
}
?>