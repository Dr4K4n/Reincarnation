<?php
$ressis_query = mysql_query("SELECT fe,h2o,uran,time,buildings FROM cities WHERE id='$city'");
$ressis_array = mysql_fetch_array($ressis_query);
$ressis_array = ressis_now($ressis_array,$city);

if($ressis_array["fe"] == $ressis_array["fe_max"])
{ echo '<p>Eisen: <b class="full">'.number_format($ressis_array["fe"],0,',','.').'</b></p>'; }
elseif($ressis_array["fe"] >= $ressis_array["fe_max"]*0.9)
{ echo '<p>Eisen: <b class="critic">'.number_format($ressis_array["fe"],0,',','.').'</b></p>'; }
else { echo '<p>Eisen: <b>'.number_format($ressis_array["fe"],0,',','.').'</b></p>'; }

if($ressis_array["h2o"] == $ressis_array["h2o_max"])
{ echo '<p>Wasser: <b class="full">'.number_format($ressis_array["h2o"],0,',','.').'</b></p>'; }
elseif($ressis_array["h2o"] >= $ressis_array["h2o_max"]*0.9)
{ echo '<p>Wasser: <b class="critic">'.number_format($ressis_array["h2o"],0,',','.').'</b></p>'; }
else { echo '<p>Wasser: <b>'.number_format($ressis_array["h2o"],0,',','.').'</b></p>'; }

if($ressis_array["uran"] == $ressis_array["uran_max"])
{ echo '<p>Uran: <b class="full">'.number_format($ressis_array["uran"],0,',','.').'</b></p>'; }
elseif($ressis_array["uran"] >= $ressis_array["uran_max"]*0.9)
{ echo '<p>Uran: <b class="critic">'.number_format($ressis_array["uran"],0,',','.').'</b></p>'; }
else { echo '<p>Uran: <b>'.number_format($ressis_array["uran"],0,',','.').'</b></p>'; }

$buildings_array	= make_array($ressis_array["buildings"],14);
$energy_supplied	= energy_kraftwerk($buildings_array,6) + energy_kraftwerk($buildings_array,13);
$energy_needed		= energy_needed($buildings_array);

if($energy_needed < $energy_supplied)
{ echo '<p>Energie: <b class="good">'.$energy_needed.'</b>/'.$energy_supplied.'</p>'; }
if($energy_needed == $energy_supplied)
{ echo '<p>Energie: <b class="critic">'.$energy_needed.'</b>/'.$energy_supplied.'</p>'; }
if($energy_needed > $energy_supplied)
{ echo '<p>Energie: <b class="full">'.$energy_needed.'</b>/'.$energy_supplied.'</p>'; }

if($_SESSION['superuser'] == 1) { echo '<p><a href="?seite=admin">Admin</a></p>'; }
?>
