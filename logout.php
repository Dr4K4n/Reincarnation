<?
if (!session_is_registered('user'))
{
die ("Du bist ausgeloggt!");
}
session_destroy();
?>
Bitte warten, sie werden jetzt ausgeloggt!
<META HTTP-EQUIV="Refresh" content="1; URL=index.php">