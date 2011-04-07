<table id="table01">
<tr><th>Willkommen</th></tr>
<tr><td>
<?php
$welcome_query = mysql_query("SELECT welcome FROM game");
$welcome_array = mysql_fetch_array($welcome_query);

echo bbcode($welcome_array['welcome']);
?>
</td></tr></table>
