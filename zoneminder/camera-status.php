<?php
// -------------------------------------------------------
// Webcam detection status
//
// Parameters :
//   index - Index of cam on the wall
//
// Revision history :
//   15/05/2018 - V1.0 - Creation by N. Bernaerts
//   15/11/2020 - V1.1 - Switch to authentification token
// -------------------------------------------------------

// zoneminder configuration
require_once ("camera-config.inc");

// Parameters
$camIndex = $_GET["index"];

// recover cam array thru saved cookie
$arrCamCookie = unserialize($_COOKIE['cams']);

// retrieve zoneminder cam id from cookie array
$camID = $arrCamCookie['cam'][$camIndex];

// retrieve zoneminder session id from cookie array
$sessionID = $arrCamCookie['session'];

// get cam status thru zoneminder
$ch = curl_init ();
curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt ($ch, CURLOPT_URL, $zmURL . "/api/monitors/alarm/id:" . $camID . "/command:status.json?" . $strToken);
$json = curl_exec($ch);
curl_close ($ch);

// convert json to array
$arrResult = json_decode ($json, true);

// display status
switch ($arrResult["status"])
{
  case 0: echo "#FFFFFF"; break;
  case 2: echo "#FF0000"; break;
  case 3: echo "#FFA500"; break;
}

?>
