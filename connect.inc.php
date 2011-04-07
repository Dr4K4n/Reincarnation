<?php
$verbindung = mysql_connect("localhost","ogame","youngcoder") or die ("Keine Verbindung !"); 
// Adresse zur MySQL Datenbank, Benutzername und Passwort
mysql_select_db("ogame") or die ("Keine oder falsche Datenbank !"); 
// Datenbank-Name
?>