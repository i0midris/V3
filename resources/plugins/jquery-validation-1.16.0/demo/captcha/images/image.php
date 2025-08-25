<?php

// Begin the session
session_start();

// If the session is not present, set the variable to an error message
$str = $_SESSION['captcha_id'] ?? 'ERROR!';

// Set the content type
header('Content-Type: image/png');
header('Cache-Control: no-cache');

// Create an image from button.png
$image = imagecreatefrompng('button.png');

// Set the font colour
$colour = imagecolorallocate($image, 183, 178, 152);

// Set the font
$font = '../fonts/Anorexia.ttf';

// Set a random integer for the rotation between -15 and 15 degrees
$rotate = random_int(-15, 15);

// Create an image using our original image and adding the detail
imagettftext($image, 14, $rotate, 18, 30, $colour, $font, (string) $str);

// Output the image as a png
imagepng($image);
