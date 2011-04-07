<h3>Einstellungen</h3>

<form method="POST" action="?seite=einstellungen&bearbeiten=ja" enctype="multipart/form-data">
<table id="table01">

<?php

$bearbeiten = $_GET["bearbeiten"];

$profil_query = mysql_query("SELECT * FROM gamer WHERE id='$userid'");
$profil_array = mysql_fetch_array($profil_query);

if($bearbeiten == 'ja')
{
	$set_email	= $_POST["email"];
	$set_pw1	= $_POST["pw1"];
	$set_pw2	= $_POST["pw2"];
	$set_age	= $_POST["age"];
	$set_gender	= $_POST["gender"];
	$set_icq	= $_POST["icq"];
	$set_comment	= $_POST["comment"];

	if($set_pw1 != '' && $set_pw1 == $set_pw2) { $update = mysql_query("UPDATE gamer SET password='".md5($set_pw1)."' WHERE id='$userid'"); echo '<p>Passwort ge&#228;ndert !</p>'; }
	elseif($set_pw1 != $set_pw2) { echo '<p>Die eingegebenen Passw&#246;rter stimmen nicht &#252;berein!</p>'; }

	$max_byte_size = 25000;
	$allowed_types = "(jpg|jpeg)";

	if(is_uploaded_file($_FILES["pic"]["tmp_name"]))
	{
		if(preg_match("/\." . $allowed_types . "$/i", $_FILES["pic"]["name"]))
		{
			if($_FILES["pic"]["size"] <= $max_byte_size)
			{
				if(copy($_FILES["pic"]["tmp_name"], 'pics/gamer/'.$userid.'.jpg'))
				{
					 echo "Avatar erfolgreich hochgeladen!";
				}
				else { echo 'hochladen fehlgeschlagen!'; }
			}
			else { echo 'Avatar zu groß'; }
		}
		else { echo 'Avatar hat falschen Dateityp'; }
	}


	$update = mysql_query("UPDATE gamer SET email='$set_email', age='$set_age', gender='$set_gender', icq='$set_icq', comment='$set_comment' WHERE id='$userid'");
	echo '<p>Profildaten ge&#228;ndert!</p>';
	echo '</table></form>';
}
else
{
	echo '<tr><td>E-Mail</td><td class="text"><input type="text" size="30" name="email" value="'.$profil_array["email"].'" /></td></tr>';
	echo '<tr><td>Neues Passwort</td><td class="text"><input type="password" size="15" name="pw1" /></td></tr>';
	echo '<tr><td>best&#228;tigen</td><td class="text"><input type="password" size="15" name="pw2" /></td></tr>';
	echo '<tr><td>Alter</td><td class="text"><input type="text" size="5" name="age" value="'.$profil_array["age"].'" /></td></tr>';
	echo '<tr><td>Geschlecht</td><td class="text">';
	if($profil_array["gender"] == 'm')
	{ echo '<input type="radio" checked="checked" name="gender" value="m">m&#228;nnlich<br /><input type="radio" name="gender" value="w">weiblich'; }
	elseif($profil_array["gender"] == 'w')
	{ echo '<input type="radio" name="gender" value="m">m&#228;nnlich<br /><input type="radio" checked="checked" name="gender" value="w">weiblich'; }
	else
	{ echo '<input type="radio" name="gender" value="m">m&#228;nnlich<br /><input type="radio" name="gender" value="w">weiblich'; }
	echo '</td></tr>';
	echo '<tr><td>ICQ</td><td class="text"><input type="text" size="15" name="icq" value="'.$profil_array["icq"].'" /></td></tr>';
	echo '<tr><td>Avatar*</td><td>';
	if(file_exists('pics/gamer/'.$userid.'.jpg'))
	{ echo '<img src="pics/gamer/'.$userid.'.jpg" /><br />'; }
	echo 'Bild hochladen: <input type="file" name="pic" /></td></tr>';
	echo '<tr><td>Profiltext</td><td class="text"><textarea cols="41" rows="6" name="comment">'.$profil_array["comment"].'</textarea></td></tr>';
	echo '<tr><th colspan="2"><input type="submit" value="&#220;bernehmen" /></th></tr>';
	echo '</table></form>';
	echo '<p class="small">*Das Bild muss im JPEG-Format vorliegen und darf nicht größer als 300x300 Pixel (25KB) sein.</p>';
}

?>