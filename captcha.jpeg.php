<?php

function isfile($filename)
{
        if ($filename != '.' && $filename != '..')
	{
	if (substr_count($filename,'.') >= 1)
	{
		return $filename;
	}
	}
}

// The damn image

$letters = 5;
$font_size = 14;
$img_width = 180;
$img_height = 45;
$fontdir = scandir('./fonts/');
$fonts = array_values(array_filter($fontdir,'isfile'));
$chars = array('A','C','E','F','H','K','L','M','N','P','R','U','V','W','X','Y','Z','2','3','4','9');

header('Content-Type: image/jpeg', true);

$img = imagecreatetruecolor($img_width,$img_height);

$color = imagecolorallocate($img,rand(200,255),rand(200,255),rand(200,255));

imagefill($img,0,0,$color);

$code = '';

$x = 10;
for($i=1;$i<=5;$i++)
{
	$char = $chars[rand(0,count($chars)-1)];
	$code .= $char;
	$font = './fonts/'.$fonts[rand(1,count($fonts)-1)];
	$color = imagecolorallocate($img,rand(0,199),rand(0,199),rand(0,199));

	$y = 20 + rand(0,10);
	$angle = rand(-30,30);

	imagettftext($img, $font_size, $angle, $x, $y, $color, $font, $char);

	$dim = imagettfbbox($font_size, $angle, $font, $char);
	$x += $dim[4] + abs($dim[6]) + 10;
}

imagejpeg($img,'',100);
imagedestroy($img);

// the code to save the "code" in a mysql-table

$time = time();
$ip = $_SERVER['REMOTE_ADDR'];

include("connect.inc.php");

mysql_query("INSERT INTO captchas VALUES ('$ip','$time','$code')");

include("disconnect.inc.php");

?>