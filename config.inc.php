<?php
error_reporting(E_ERROR);

$_SQL['host'] = 'localhost';
$_SQL['user'] = 'reincarnation';
$_SQL['pass'] = 'HCBjBW5XRUEEfA8w';
$_SQL['db'] = 'reincarnation';

$sites_pub = array(
	"home" => "welcome.php",
	"news" => "news.php",
	#"anleitung" => "tutorial.php",
	"login" => "login.php",
	"impressum" => "impressum.php"
);
$sites_int = array(
	"uebersicht" => "overview.php",
	"gebaeude" => "buildings.php",
	"militaerbasis" => "barracks.php",
	"karte" => "map.php",
	"forschung" => "research.php",
	"marsch" => "send_troops.php",
	"allianz" => "alliance.php",
	"nachrichten" => "messages.php",
	"einstellungen" => "settings.php",
	"technik" => "technik.php",
	"highscore" => "highscore.php",
	"forum" => "forum.php",
	"admin" => "admin.php",
	"logout" => "logout.php"
);
?>
