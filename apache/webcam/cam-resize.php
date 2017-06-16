<?php
// ---------------------------------------
// Webcam image scaling and compression
// This script handles real time resize and compression
// Parameters :
//   cam  - URL of webcam image on the LAN
//   width - maximum width of resized image
//   height - maximum height of resized image
//   quality - quality of recompressed image
//   format - format of new image (jgp, png, gif, ...)
// Revision history :
//   17/01/2016 - V1.0 - Creation by N. Bernaerts
// ---------------------------------------

// parse URL parameters and create variables
if (isset($_SERVER['QUERY_STRING'])) parse_str ($_SERVER['QUERY_STRING']);
 
// calculate webcam URL
$url_cam = escapeshellarg("http://{$cam}");
 
// send jpeg file header
header("Content-type: image/{$format}");
 
// send file conversion result
passthru("convert -quality {$quality} -geometry '{$width}x{$height}' {$url_cam} {$format}:-");
?>
