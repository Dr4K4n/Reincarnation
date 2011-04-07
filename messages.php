<h3>Nachrichten</h3>



<?php
$ordner	= $_GET["ordner"];
$to	= $_GET["to"];
$send	= $_POST["send"];
$submit	= $_POST["submit"];
$msg_id = $_GET["id"];


if($submit)
{
	## Nachrichten loeschen/verschieben
	$messages	= $_POST["messages"];
	$action		= $_POST["action"];
	if(isset($messages))
	{
		if($action == 'delete')
		{
			for($x=0;$x<count($messages);$x++)
			{
				$message_id = $messages[$x];
				$delete = mysql_query("DELETE FROM messages WHERE `id`='$message_id'");
			}
			echo 'Nachricht(en) erfolgreicht gel&#246;scht!';
		}
		elseif($action == 'archiv')
		{
			for($x=0;$x<count($messages);$x++)
			{
				$message_id = $messages[$x];
				$archiv = mysql_query("UPDATE messages SET `folder`='99' WHERE `id`='$message_id'");
			}
			echo 'Nachricht(en) erfolgreicht archiviert!';
		}
	}
}
elseif($to)
{
	$resubject = $_POST["resubject"];
?>
	<form method="post" action="?seite=nachrichten"><table id="table01">
		<tr><th colspan="2">Nachricht schreiben an: <?php echo getusername($to); ?></th></tr>
		<tr><td>Betreff:</td><td><input type="text" name="subject" value="<?php echo $resubject; ?>" size="50"></td></tr>
		<tr><td>Nachricht:</td><td><textarea name="msg" cols="60" rows="10"></textarea></td></tr>
		<tr><th colspan="2"><input type="submit" value=" abschicken "></th></tr>
	<input type="hidden" name="send" value="<?php echo $to; ?>">
	</table></form>
<?php
}
elseif($send)
{
	$subject	= $_POST["subject"];
	$msg		= $_POST["msg"];
	if(!$subject) { echo 'Du *musst* einen Betreff angeben!'; }
	elseif(!$msg) { echo 'Du *musst* einen Nachrichtentext schreiben!'; }
	else
	{
		$time = time();
		$insert = mysql_query("INSERT INTO messages (owner,folder,sender_id,sender_city,msg,more,time,`read`) VALUES ('$send','1','$userid','$city','$subject','$msg','$time','0')");
	}
}
elseif($msg_id)
{
	$msg_query = mysql_query("SELECT * FROM messages WHERE id='$msg_id'");
	$msg_array = mysql_fetch_array($msg_query);
	$sender_city_coords_array = getcoords($msg_array["sender_city"]);
	$sender_city_x = $sender_city_coords_array[0];
	$sender_city_y = $sender_city_coords_array[1];

	echo '<form method="post" action="?seite=nachrichten&to='.$msg_array["sender_id"].'"><table id="table01">';
	echo '<tr><th colspan="2">Nachricht von: '.getusername($msg_array["sender_id"]).' aus '.$sender_city_x.':'.$sender_city_y.'</th></tr>';
	echo '<tr><td>Betreff:</td><td>'.$msg_array["msg"].'</td></tr>';
	echo '<tr><td>Nachricht:</td><td>'.$msg_array["more"].'</td></tr>';
	echo '<tr><th colspan="2"><input type="submit" value=" antworten "></th></tr>';
	echo '<input type="hidden" name="send" value="'.$to.'">';
	echo '<input type="hidden" name="resubject" value="Re: '.$msg_array["msg"].'">';
	echo '</table></form>';
	
}
elseif($ordner)
{
	## Nachrichten in einem bestimmten Ordner auflisten und als gelesen markieren
	$name_query = mysql_query("SELECT name FROM messages_folder WHERE `id`='$ordner'");
	$name_array = mysql_fetch_array($name_query);
	$name = $name_array["name"];

	echo '<form method="post" action="?seite=nachrichten&ordner='.$ordner.'"><table id="table01"><tr><th colspan="5">Nachrichten in '.$name.'</th></tr>';
	echo '<tr><td></td><td>Datum</td><td>Absender</td><td>Stadt</td><td>Betreff</td></tr>';

	$messages_query = mysql_query("SELECT * FROM messages WHERE `owner`='$userid' AND `folder`='$ordner' ORDER BY time DESC");
	while($messages_row = mysql_fetch_object($messages_query))
	{
		$message_id = $messages_row->id;
		$sender_city_coords_array = getcoords($messages_row->sender_city);
		$sender_city_x = $sender_city_coords_array[0];
		$sender_city_y = $sender_city_coords_array[1];

		echo '<tr><td><input type="checkbox" name="messages[]" value="'.$message_id.'" /></td>';
		echo '<td>'.strftime('%d.%m.%Y %X',$messages_row->time).'</td><td>'.getusername($messages_row->sender_id).'</td>';
		echo '<td>'.$sender_city_x.':'.$sender_city_y.'</td><td>'.$messages_row->msg;
		if($messages_row->more) { echo ' <a href="?seite=nachrichten&id='.$message_id.'">more</a>'; }
		echo '</td></tr>';
		$read = mysql_query("UPDATE messages SET `read`='1' WHERE `id`='$message_id'");
	}

	echo '<tr><td colspan="5"><select name="action" size="1"><option value="delete">L&#246;schen</option>';
	if($ordner != 99) { echo '<option value="archiv">Archivieren</option>'; }
	echo '</select><input type="submit" name="submit" value=" OK " /></td></tr>';
	echo '</table></form>';
}
else
{
	## Ordner auflisten
	echo '<table id="table01"><tr><th colspan="3">Ordner</th></tr><tr><td>Name</td><td>ungelesene</td><td>gesamt</td></tr>';

	$folders_query = mysql_query("SELECT * FROM messages_folder ORDER BY id");
	while($folders_row = mysql_fetch_object($folders_query))
	{
		$folder_id = $folders_row->id;
		$messages_query = mysql_query("SELECT id FROM messages WHERE `owner`='$userid' AND `folder`='$folder_id'");
		$messages_count = mysql_num_rows($messages_query);
		$messages_unread_query = mysql_query("SELECT id FROM messages WHERE `owner`='$userid' AND `folder`='$folder_id' AND `read`=0");
		$messages_unread_count = mysql_num_rows($messages_unread_query);

		echo '<tr><td><a href="?seite=nachrichten&ordner='.$folders_row->id.'">'.$folders_row->name.'</a></td>';
		echo '<td>'.$messages_unread_count.'</td><td>'.$messages_count.'</td></tr>';
	}

	echo '</table>';
}
?>