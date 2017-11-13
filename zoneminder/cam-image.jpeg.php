<?php
// -------------------------------------------------------
//      Webcam image display from ZoneMinder
//
// Revision history :
//   10/11/2017 - V1.0 - Creation from N. Bernaerts
// -------------------------------------------------------

// zoneminder configuration
require_once ("cam-config.php");

// parameters : image index and image scale (in % between 1 and 100)
$monitorId = -1;
if (isset($_GET["id"])) $monitorId = $_GET["id"];
$monitorScale = 0;
if (isset($_GET["scale"])) $monitorScale = $_GET["scale"];

// calculate hash login
$arrTime = localtime();
$authKey = $zmSecret . $zmUser . $zmPass . $arrTime[2] . $arrTime[3] . $arrTime[4] . $arrTime[5];
$authHash = md5 ($authKey);

// generate zoneminder URL
$imgURL = $zmURL . "/cgi-bin/zms?mode=single&monitor=" . $monitorId . "&scale=" . $monitorScale . "&user=" . $zmUser . "&pass=" . $zmPass;

// set jpeg header
header("Content-type: image/jpeg");

// get image
$imgData = file_get_contents($imgURL);
echo $imgData; 
?>
