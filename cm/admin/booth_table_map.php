<?php

require_once dirname(__FILE__).'/admin.php';

if (!echo_booth_map()) {
	header('Content-Type: image/png');
	$image = imagecreate(640, 480);
	$bg = imagecolorallocate($image, 255, 255, 255);
	imagefilledrectangle($image, 0, 0, 640, 480, $bg);
	$fg = imagecolorallocate($image, 0, 0, 255);
	imagestring($image, 5, (640-9*21)/2, 480/2-8-12, 'Could not load image.', $fg);
	imagestring($image, 5, (640-9*24)/2, 480/2-8+12, 'Please upload a new one.', $fg);
	imagepng($image);
	imagedestroy($image);
}