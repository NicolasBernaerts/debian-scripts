<?php
// -------------------------------------------------------
// Webcam image wall display, based on ZoneMinder config
//
// Parameters :
//   index  - Index of first cam to display on the wall
//   row    - Number of camera rows on the wall
//   column - Number of camera per row on the wall
//   width  - Width of the wall in pixels (default = 1980px)
//   height - Height of the wall in pixels (default = 1080px)
//   zoom   - Height of a zoomed image in pixels (default = 720px)
//
// Revision history :
//   07/06/2017 - V1.0 - Creation by N. Bernaerts
//   14/07/2017 - V1.1 - Adapt to use cam-resize.php
//   02/09/2017 - V2.0 - Rewrite to get cam list and cam pictures from ZoneMinder API
//   10/11/2017 - V2.1 - Change refresh algo to unload server and optimize refresh rate
//   17/11/2017 - V2.2 - Remove network topology difference (internet or lan)
// -------------------------------------------------------

// zoneminder configuration
require_once ("cam-config.inc");

// Parameter : Index of first cam on the wall
$wallIndex = 1;	
if (isset($_GET["index"])) $wallIndex = $_GET["index"];

// Parameter : Number of camera lines
$nbrRow = 6;
if (isset($_GET["row"])) $nbrRow = $_GET["row"];

// Parameter : Number of camera columns
$nbrColumn = 7;
if (isset($_GET["column"])) $nbrColumn = $_GET["column"];

// Parameter : Width of the wall (in pixels)
$wallWidth = 1920;
if (isset($_GET["width"])) $wallWidth = $_GET["width"];

// Parameter : Height of the wall (in pixels)
$wallHeight = 1080;
if (isset($_GET["height"])) $wallHeight = $_GET["height"];

// Parameter : Height of the zoomed picture (in pixels)
$zoomHeight = 720;
if (isset($_GET["zoom"])) $zoomHeight = $_GET["zoom"];

// calculate size on the wall
$maxCam = $nbrColumn * $nbrRow;
$maxThumbWidth  = ceil ($wallWidth / $nbrColumn);
$maxThumbHeight = ceil ($wallHeight / $nbrRow);
$percentColumn = floor (100 / $nbrColumn);

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
$arrCam = Array ();
foreach ($arrSequence as $idxMonitor => $sequence) 
{
	// populate cams array according to start index and wall size
	if (($sequence >= $wallIndex) && ($countCam < $maxCam))
	{
		// increment counter
		$countCam++;

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

// number of cameras to display
$nbrCam = count ($arrCam);
?>

<!doctype html>
<html lang="fr">
<head>

<style type="text/css">
body { background-color:black; }
table { width:100%; border:0px; padding:0px; margin:0px; }
table tr { padding:0px; margin:0px; }
table td { padding:0px; margin:0px; width:<?php echo ($percentColumn); ?>%; }
table img { padding:1px; margin:0px; border-radius:5px; } 
table span { position:absolute; z-index:1; padding:2px 5px; border-radius:5px; color:black; background-color:white; opacity:0.75; font-family:arial,serif; font-size:0.8em; font-style:italic; }
</style>

<title><?php echo ($strWallName . " - " . $nbrCam . " cameras"); ?></title>
<meta charset="UTF-8">
<script src="/js-global/FancyZoom.js" type="text/javascript"></script>
<script src="/js-global/FancyZoomHTML.js" type="text/javascript"></script>
<script type='text/javascript'>

function updateImage(camIndex)
{
	var nbrColumn=<?php echo ($nbrColumn); ?>;

	var camID="cam" + camIndex;
	if (document.getElementById(camID).complete == true) 
	{
		var now=new Date();
		camIndex++;
		if (camIndex > <?php echo ($nbrCam); ?>) camIndex=1;
		camID="cam" + camIndex;
		imgCam=document.getElementById(camID)
		var URL=imgCam.src;
		imgCam.src=URL.substring(0,URL.indexOf("timestamp=")) + 'timestamp=' + now.getTime();
	}

	imgZoom=document.getElementById('ZoomImage');
	if (imgZoom != null)
	{
		if (imgZoom.complete == true) 
		{
			var now=new Date();
			var URL=imgZoom.src;
			imgZoom.src=URL.substring(0,URL.indexOf("timestamp=")) + 'timestamp=' + now.getTime();
		}
	}

	setTimeout(function() { updateImage(camIndex); }, 10);
}

setTimeout(function() { updateImage(1); }, 1000);

</script>
</head>

<body id="wall" onload="setupZoom()">
<table>

<?php

// initilise counters
$idxCam = 1;
$idxRow = 1;
$idxColumn = 1;

// loop to declare cameras
foreach ($arrCam as $cam) 
{
	// calculate label vertical margin according to image size
	$marginTop = $cam['theight'] - 20;

	// if needed, display new row
	if ($idxColumn == 1) { echo "<tr>\n"; }

	// display current camera
	echo ("<td><span style='margin-left:4px; margin-top:2px;' >" . $cam['name'] . "</span>");
	echo ("<a href='" . $cam['urlzoom'] . "' title='" . $cam['name'] . "' ><img id='cam" . $idxCam . "' src='" . $cam['urlthumb'] . "' width=100% ></a>");
	echo ("</td>\n");

	// increment counters
	$idxCam++;
	$idxColumn++;

	// if needed, end row
	if ($idxColumn > $nbrColumn) { echo "</tr>\n"; $idxColumn = 1; $idxRow++; }
}

// if needed, end row
if ($idxColumn > 1) { echo "</tr>\n"; }

?>

</table> 
</body>

</html>
