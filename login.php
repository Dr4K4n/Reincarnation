<?
$register	= $_GET['register'];
$delete		= $_GET['delete'];
$forgot		= $_GET['forgot'];
$new_password	= $_GET['new_password'];
$done		= $_GET['done'];
$loguser	= htmlspecialchars($_POST['user']);
$password	= $_POST['password'];
$password1	= $_POST['password1'];
$password2	= $_POST['password2'];
$email		= htmlspecialchars($_POST['email']);
$code		= $_POST['code'];

// choose what to do !
if ($register)
{
	register($done, $loguser, md5($password1), md5($password2), $email, $code);
}
elseif ($delete)
{
	delete($done, $loguser, md5($password));
}
elseif ($forgot)
{
	forgot($done, $loguser, $email);
}
elseif ($new_password)
{
	new_password($loguser, $email, $key);
}
else
{
	login($done, $loguser, md5($password));
}

// define how to do it !
function new_password($user, $email, $key)
{
	
	$user = $_GET["user"];
	$email = $_GET["email"];
	$key = $_GET["key"];
	$check = md5($email);
	if ($key == $check)
	{
		$content = "qwertzupasdfghkyxcvbnm";
		$content .= "123456789";
		srand((double)microtime()*1000000);
		for($i = 0; $i < 6; $i++)
		{
			$rand_password .= substr($content,(rand()%(strlen ($content))), 1);
		}
		echo "Dein neues Passwort lautet: $rand_password";
		$message = "Hallo $row->user!\n\nDein neues Passwort lautet:$rand_password\nJetzt einloggen unter http://articmodding.de/ogame/beta/?seite=login\n\nMfg Admin";
		mail($email, "Passwort", $message, "From: nichtantworten");
		$rand_password = md5($rand_password);
		$add = mysql_query("UPDATE gamer SET password = '$rand_password' WHERE user = '$user'");
		@login();
	}
	else
	{
		echo "Fehler! PWND!";
	}
}

function login($done, $user, $password)
{
	if ($done)
	{
		$query = mysql_query ("SELECT * FROM gamer WHERE user = '$user'");
		$rows = mysql_num_rows($query);
		if ($rows <= 0)
		{
			echo "Unbekannter Benutzername!";
		}
		else
		{
			while ($row = mysql_fetch_object ($query))
			{
				if ($row->password == $password)
				{
					$user_id = $row->id;
					$superuser = 0;
					$superuser_query = mysql_query("SELECT * FROM superuser WHERE id='$user_id'");
					if(mysql_num_rows($superuser_query) != 0)
					{
						$superuser_array = mysql_fetch_array($superuser_query);
						$superuser = 1;
						$_SESSION['s_game'] = $superuser_array['game'];
						$_SESSION['s_news'] = $superuser_array['news'];
						$_SESSION['s_forum'] = $superuser_array['forum'];
					}
					$city = $row->maincity;
					$_SESSION["user_id"] = $user_id;
					$_SESSION["user"] = $user;
					$_SESSION["city"] = $city;
					$_SESSION["superuser"] = $superuser;
					?>
					
					<script language="javascript">
					<!--
					window.location.href="index.php?seite=uebersicht";
					// -->
					</script>
					
					<?
					if (!session_is_registered('user'))
					{
						echo "Bitte einloggen!";
					}
				}
				else
				{
					echo "Falsches Passwort!";
				}
			}
		}
	}
	else
	{
		
?>
<form method="POST" action="?seite=login&done=yes">
<table id="login">
  <tr>
    <th colspan="2">Login</th>
  </tr>
  <tr>
    <td>Benutzername:</td>
    <td><input type="text" name="user" value="" /></td>
  </tr>
  <tr>
    <td>Passwort:</td>
    <td><input type="password" name="password" value="" /></td>
  </tr>
  <tr>
    <td colspan="2" class="center"><input type="submit" value="Login" /></td>
  </tr>
</table>
</form>

<p><a href="?seite=login&forgot=yes">Passwort vergessen?</a></p>
<p><a href="?seite=login&delete=yes">Account l&ouml;schen?</a></p>
<p><a href="?seite=login&register=yes">Anmelden?</a></p>

<?

	}
}

function register ($done, $user, $password1, $password2, $email, $code)
{
	if($done)
	{
		$ip = strval($_SERVER["REMOTE_ADDR"]);
		$captcha_query = mysql_query("SELECT * FROM captchas WHERE ip = '$ip' ORDER BY time ASC");
		while($captcha_row = mysql_fetch_object($captcha_query))
		{
			$captcha_code = $captcha_row->code;
		}

		$query = mysql_query("SELECT user FROM gamer");
		while ($row = mysql_fetch_object ($query))
		{
			if ($row->user == $user)
			{
				echo "Dieser Benutzer existiert schon!";
				exit;
			}
		}

		if ($user == "" OR $password1 == "" OR $password2 == "" OR $email == "" OR $code == "")
		{
			echo "Du hast mindestens ein Feld nicht ausgefllt!";
		}
		elseif ($password1 != $password2)
		{
			echo "Dein Passwort ist ungleich Deiner Wiederholung!";
		}
		elseif (strtoupper($code) != strtoupper($captcha_code))
		{
			echo "Dein Code stimmt nicht mit dem in der Grafik &uuml;berein!";
			echo $code.'<br />'.$captcha_code;
		}
		else
		{
			$add = mysql_query("INSERT INTO gamer (user, password, email) VALUES ('$user','$password1', '$email')");
			$user_id = getuserid($user);
			$add_research = mysql_query("INSERT INTO gamer_research (gamer_id) VALUES ('$user_id')");
			$city1_id = newcity(0,0,$user_id);
			$update_coords = mysql_query("UPDATE gamer SET maincity='$city1_id' WHERE id='$user_id'");
			echo "Erfolgreich angemeldet! Du kannst Dich nun einloggen:";
			@login();
		}
		$captcha_delquery = mysql_query("DELETE FROM captchas WHERE ip = '$ip'");
	}
	else
	{
?>

<form method="POST" action="?seite=login&register=yes&done=yes">
<table id="login">
  <tr>
    <th colspan="2">Anmeldung</th>
  </tr>
  <tr>
    <td>Benutzername:</td>
    <td><input type="text" name="user" value=""></td>
  </tr>
  <tr>
    <td>Passwort:</td>
    <td><input type="password" name="password1" value="" /></td>
  </tr>
  <tr>
    <td>Wiederholen:</td>
    <td><input type="password" name="password2" value="" /></td>
  </tr>
  <tr>
    <td>E-Mail Adresse:</td>
    <td><input type="text" name="email" value="" /></td>
  </tr>
  <tr>
    <td colspan="2"><img src="captcha.jpeg.php" /></td>
  </tr>
  <tr>
    <td>Code:</td>
    <td><input type="text" name="code" value="" /></td>
  </tr>
  <tr>
    <td colspan="2" class="center"><input type="submit" value="Anmelden" /></td>
  </tr>
</table>
</form>


<?
	}
}

function forgot ($done, $user, $email)
{
	if ($done)
	{
		$query = mysql_query ("SELECT * FROM gamer WHERE user = '$user'");
		$rows = mysql_num_rows($query);
		if ($rows <= 0)
		{
			echo "Unbekannter Benutzername!";
		}
		else
		{
			while ($row = mysql_fetch_object ($query))
			{
				if ($row->email == $email)
				{
					$key = md5($email);
					$message = "Hallo $row->user!\n\nUm ein neues Passwort anzulegen, hier klicken http://localhost/ogame/index.php?seite=login&new_password=yes&user=$row->user&email=$email&key=$key\nWenn du kein neues Passwort angefordert hast, vergiss diese Mail einfach.\n\nMfG Admin";
					mail($email, "Passwort", $message, "From: nichtantworten");
					echo "<p>Dir wurde an die e-Mail Adresse $email ein Aktivierungslink geschickt!</p>";
					@login();
				}
				else
				{
					echo "Falsche E-Mail Adresse!";
				}
			}
		}
	}
	else
	{
?>

<form method="POST" action="?seite=login&forgot=yes&done=yes">
<table id="login">
  <tr>
    <th colspan="2">Passwort vergessen</th>
  </tr>
  <tr>
    <td>Benutzername:</td>
    <td><input type="text" name="user" value="" /></td>
  </tr>
  <tr>
    <td>E-Mail Adresse:</td>
    <td><input type="text" name="email" value="" /></td>
  </tr>
  <tr>
    <td colspan="2" class="center"><input type="submit" value="Los" /></td>
  </tr>
</table>
</form>

<?
	}
}

function delete($done, $user, $password)
{
	if($done)
	{
		$query = mysql_query ("SELECT * FROM gamer WHERE user = '$user'");
		$rows = mysql_num_rows($query);
		if ($rows <= 0)
		{
			echo "Unbekannter Benutzername!";
		}
		else
		{
			while ($row = mysql_fetch_object ($query))
			{
				if ($row->password==$password)
				{
					$delete = mysql_query ("DELETE FROM gamer WHERE user = '$user'");
					echo "Deine Daten wurden erfolgreich aus der Datenbank entfernt!";
				}
				else
				{
					echo "Falsches Passwort!";
				}
			}
		}
	}
	else
	{
?>

<form method="POST" action="?seite=login&delete=yes&done=yes">

<table id="login">
  <tr>
    <th colspan="2">Account löschen</th>
  </tr>
  <tr>
    <td>Benutzername:</td>
    <td><input type="text" name="user" value="" /></td>
  </tr>
  <tr>
    <td>Passwort:</td>
    <td><input type="password" name="password" value="" /></td>
  </tr>
  <tr>
    <td colspan="2" class="center"><input type="submit" value=" löschen " /></td>
  </tr>
</table>
</form>

<?
	}
}

function gencoords($max)
{
	srand((double)microtime() * 1000000);
	$new_x = rand(1,$max);
	$new_y = rand(1,$max);
	return array($new_x,$new_y);
}

function checkcoords($coords_array)
{
	$x = $coords_array[0];
	$y = $coords_array[1];
	$city_exist_query = mysql_query("SELECT id FROM cities WHERE `x`='$x' AND `y`='$y'");
	return mysql_num_rows($city_exist_query);
}

function newcity($x,$y,$user_id)
{
	if($x == 0 && $y == 0)
	{
		## Koordinaten fuer die neue Stadt berechnen, aufpassen, das Koordinaten nich schon besetzt sind ;)
		$cities_query = mysql_query("SELECT id FROM cities");
		$cities_count = mysql_num_rows($cities_query);
		$felder_reserviert = $cities_count*2;
		$bereich_seitenlaenge = 2;
		while($bereich_seitenlaenge*$bereich_seitenlaenge <= $felder_reserviert)
		{
			$bereich_seitenlaenge += 1;
		}
		$new_coords = gencoords($bereich_seitenlaenge);
		while(checkcoords($new_coords) != 0)
		{
			$new_coords = gencoords($bereich_seitenlaenge);
		}
		$x = $new_coords[0];
		$y = $new_coords[1];
	}
	elseif(checkcoords(array($x,$y)) != 0)
	{
		echo "diese Koordinaten sind schon besetzt";
	}
	$time = time();
	$new_buildings = "1;1\n2;1\n3;1";
	$sql = "INSERT INTO cities (id,name,owner,x,y,fe,h2o,uran,time,buildings,points) VALUES ('','Neue Stadt',$user_id,$x,$y,100,50,0,$time,$new_buildings,1)";
	$found_city = mysql_query($sql);
	$id_query = mysql_query("SELECT id FROM cities WHERE x='$x' AND y='$y'");
	$id_array = mysql_fetch_array($id_query);
	return $id_array["id"];
}
?>

