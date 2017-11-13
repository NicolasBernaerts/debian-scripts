<?php
// -------------------------------------------------------
// Webcam image wall display, based on ZoneMinder config
//
// Parameters :
//   camperline - Number of images per line
//
// Revision history :
//   07/06/2017 - V1.0 - Creation by N. Bernaerts
//   14/07/2017 - V1.1 - Adapt to use cam-resize.php
//   02/09/2017 - V2.0 - Rewrite to get cam list and cam pictures from ZoneMinder API
//   10/11/2017 - V2.1 - Change refresh algo to avoid server overload
//                       and adjust rate to network
// -------------------------------------------------------

// zoneminder configuration
require_once ("cam-config.php");

// initilisation
$arrCam = Array ();

// set client type (lan or internet)
$clientIP = $_SERVER['REMOTE_ADDR'];
$typeZone="internet";
if ($clientIP == "127.0.0.1") $typeZone = "lan";
if (substr($clientIP,0,8) == "192.168.") $typeZone = "lan";
if (substr($clientIP,0,5) == "10.0.") $typeZone = "lan";

// parameters
$wallIndex = 1;		// Index of first cam on the wall
if (isset($_GET["index"])) $wallIndex = $_GET["index"];
$nbrRow = 6;		// number of lines
if (isset($_GET["row"])) $nbrRow = $_GET["row"];
$nbrColumn = 7;		// number of columns
if (isset($_GET["column"])) $nbrColumn = $_GET["column"];
$wallWidth = 1920;	// Width of the wall (in pixels)
if (isset($_GET["width"])) $wallWidth = $_GET["width"];
$wallHeight = 1080;	// Height of the wall (in pixels)
if (isset($_GET["height"])) $wallHeight = $_GET["height"];
$zoomHeight = 720;	// Height of the zoomed picture (in pixels)
if (isset($_GET["zoom"])) $zoomHeight = $_GET["zoom"];

// calculate size on the wall
$maxCam = $nbrColumn * $nbrRow;
$maxThumbWidth  = floor ($wallWidth / $nbrColumn) - 6;
$maxThumbHeight = floor ($wallHeight / $nbrRow) - 6;

// login to zoneminder
$ch=curl_init();
curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch,CURLOPT_URL,$zmURL . "/index.php");
curl_setopt($ch,CURLOPT_HEADER, 1);
curl_setopt($ch,CURLOPT_POST, 4);
curl_setopt($ch,CURLOPT_POSTFIELDS, "username=" . $zmUser . "&password=" . $zmPass . "&action=login&view=console");
$response=curl_exec($ch);
curl_close($ch);

// retrieve session cookie
preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $response, $arrCookie);
$cookie = "Cookie: " . $arrCookie[1][0];

// get monitor list
$ch=curl_init();
curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch,CURLOPT_URL,$zmURL . "/api/monitors.json");
curl_setopt($ch, CURLOPT_HTTPHEADER, array($cookie));
$json=curl_exec($ch);
curl_close($ch);

// convert json to array
$arrMonitor = json_decode($json, true);

// tri par ordre de sequence
foreach ($arrMonitor["monitors"] as $idxMonitor => $monitor) $arrSequence[$idxMonitor] = $monitor["Monitor"]["Sequence"];
asort($arrSequence);

// create camera list from sort monitor sequence
$countCam = 0;
foreach ($arrSequence as $idxMonitor => $sequence) 
{
	// get monitor data
	$monitor = $arrMonitor["monitors"][$idxMonitor];

	// calculate scale factor
	$camWidth = $monitor["Monitor"]["Width"];
	$camHeight = $monitor["Monitor"]["Height"];
	$scaleWidth = $maxThumbWidth / $camWidth;
	$scaleHeight = $maxThumbHeight / $camHeight;
	$scaleFactor = min ($scaleWidth, $scaleHeight);

	// calculate thumb and zoom scaling
	$scaleThumb = round (100 * $scaleFactor) + 1;
	$scaleZoom = round (100 * $zoomHeight / $camHeight) + 1;

	// populate cams array
	if (($sequence >= $wallIndex) && ($countCam < $maxCam))
	{
		// increment counter
		$countCam++;

		// add cam to array
		$arrCam[$sequence]['id']     = $monitor["Monitor"]["Id"];
		$arrCam[$sequence]['name']   = $monitor["Monitor"]["Name"];
		$arrCam[$sequence]['width']  = $camWidth;
		$arrCam[$sequence]['height'] = $camHeight;
		$arrCam[$sequence]['twidth']  = floor ($camWidth * $scaleFactor);
		$arrCam[$sequence]['theight'] = floor ($camHeight * $scaleFactor);
		$arrCam[$sequence]['urlthumb']  = "/cam-image.jpeg.php?id=" . $monitor["Monitor"]["Id"] . "&scale=" . $scaleThumb . "&timestamp=1";
		$arrCam[$sequence]['urlzoom']   = "/cam-image.jpeg.php?id=" . $monitor["Monitor"]["Id"] . "&scale=" . $scaleZoom  . "&timestamp=1";
	}
}

// calculate number of cams
$nbrCam = count ($arrCam);
?>

<!doctype html>
<html lang="fr">
<head>

<style type="text/css">
body { background-color:black; }
div.wall { margin:auto; }
ul { padding:0px; margin:0px; }
li { float:left; display:inline; padding:0px; margin:0px; }

li img { padding:1px; border-radius:3px; } 
li span { position:absolute; z-index:1; padding:1px 5px; border-radius:5px; background-color:white; opacity:0.6; font-family:arial,serif; font-size:0.8em; font-style:italic; }

@media only screen and (max-width:1280px) { li span { font-size:0.6em;} }
</style>

<title>SCI La Soie - <?php echo ($nbrCam); ?> caméras (<?php echo ($typeZone); ?>)</title>
<meta charset="UTF-8">
<script src="/js-global/FancyZoom.js" type="text/javascript"></script>
<script src="/js-global/FancyZoomHTML.js" type="text/javascript"></script>
<script type='text/javascript'>

function updateImage(camIndex)
{
	var camID="cam" + camIndex;
	if (document.getElementById(camID).complete == true) 
	{
		var now=new Date();
		camIndex++;
		if (camIndex > <?php echo ($nbrCam); ?>) camIndex=1;
		camID="cam" + camIndex;
		var URL=document.getElementById(camID).src;
		document.getElementById(camID).src=URL.substring(0,URL.indexOf("timestamp=")) + 'timestamp=' + now.getTime();
	}

	imgZoom=document.getElementById('ZoomImage');
	if (imgZoom != null)
	{
		if (document.getElementById('ZoomImage').complete == true) 
		{
			var now=new Date();
			var URL=document.getElementById('ZoomImage').src;
			document.getElementById('ZoomImage').src=URL.substring(0,URL.indexOf("timestamp=")) + 'timestamp=' + now.getTime();
		}
	}

	setTimeout(function() { updateImage(camIndex); }, 10);
}

setTimeout(function() { updateImage(1); }, 10);

</script>
</head>

<body id="wall" onload="setupZoom()">
<div class="wall">

<?php

// display monitors
$index = 1;
foreach ($arrCam as $cam) 
{
	// calculate label vertical margin according to image size
	$marginTop = $cam['theight'] - 20;

	echo ("<li><span style='margin-left:4px; margin-top:" . $marginTop . "px;' >" . $cam['name'] . "</span>");
	echo ("<a href='" . $cam['urlzoom'] . "' title='" . $cam['name'] . "' ><img id='cam" . $index . "' src='" . $cam['urlthumb'] . "' width=" . $cam['twidth'] . " height=" . $cam['theight'] . "></a>");
	echo ("</li>\n");
	$index++;
}

?>

</div>
</body>

</html>
