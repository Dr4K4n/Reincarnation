<?php
$verbindung = mysql_connect($_SQL['host'],$_SQL['user'],$_SQL['pass']) or die ("Keine Verbindung !"); 
mysql_select_db($_SQL['db']) or die ("Keine oder falsche Datenbank !"); 
?>
