<?php
// -------------------------------------------------------
// Webcam image wall display, based on ZoneMinder config
//
// Display parameters :
//   row    - Number of camera rows on the wall (default=6)
//   column - Number of camera per row on the wall (default=7)
//   width  - Width of the wall in pixels (default=1980px)
//   height - Height of the wall in pixels (default=1080px)
//   zoom   - Height of a zoomed image in pixels (default=720px)
//
// Camera selection :
//   index  - Index of first cam to display in ZM sequence list
//   cams   - ordered list of cams to display using ZM mid (exemple : 1-10-4-8-14-2)
//
// Revision history :
//   07/06/2017 - V1.0 - Creation by N. Bernaerts
//   14/07/2017 - V1.1 - Adapt to use cam-resize.php
//   02/09/2017 - V2.0 - Rewrite to get cam list and cam pictures from ZoneMinder API
//   10/11/2017 - V2.1 - Change refresh algo to unload server and optimize refresh rate
//   17/11/2017 - V2.2 - Remove network topology difference (internet or lan)
//   15/05/2018 - V2.3 - Add alert status
//   18/11/2018 - V2.4 - Add cams parameter to specify camera list to be displayed
//   22/01/2019 - V2.5 - Remove disabled cams from the wall
//   03/02/2020 - V3.0 - Switch the wall from table to grid
// -------------------------------------------------------

// zoneminder configuration
require_once ("camera-config.inc");

// initialisation
$arrCam       = Array ();
$arrDisplay   = Array ();
$arrZMCookie  = Array ();
$arrCamCookie = Array ();
$arrMonitor   = Array ();
$arrOrdered   = Array ();

// Parameter : Number of camera columns
$nbrColumn = 7;
if (isset($_GET["column"])) $nbrColumn = $_GET["column"];

// Parameter : Maximum number of cameras
$maxCam = $nbrColumn * $nbrColumn;
if (isset($_GET["maxcam"])) $maxCam = $_GET["maxcam"];

// Parameter : Width of the wall (in pixels)
$wallWidth = 1920;
if (isset($_GET["width"])) $wallWidth = $_GET["width"];

// Parameter : Width of the zoomed picture (in pixels)
$zoomWidth = 1280;
if (isset($_GET["zoom"])) $zoomHeight = $_GET["zoom"];

// Parameter : Index of first cam on the wall (starts from 0)
$wallIndex = 0;	
if (isset($_GET["index"])) $wallIndex = $_GET["index"];

// Parameter : List of cams to display
if (isset($_GET["cams"])) $lstCam = $_GET["cams"];

// login to zoneminder
$ch = curl_init();
curl_setopt ($ch,CURLOPT_RETURNTRANSFER, 1);
curl_setopt ($ch,CURLOPT_URL, $zmURL . "/index.php");
curl_setopt ($ch,CURLOPT_HEADER, 1);
curl_setopt ($ch,CURLOPT_POST, 4);
curl_setopt ($ch,CURLOPT_POSTFIELDS, "username=" . $zmUser . "&password=" . $zmPass . "&action=login&view=console");
$response = curl_exec ($ch);
curl_close ($ch);

// retrieve session cookie
preg_match_all ('/^Set-Cookie:\s*([^;]*)/mi', $response, $arrZMCookie);
$arrCamCookie['session'] = $arrZMCookie[1][0];

// get monitor list
$ch = curl_init();
curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt ($ch, CURLOPT_URL, $zmURL . "/api/monitors.json");
curl_setopt ($ch, CURLOPT_HTTPHEADER, array ("Cookie: " . $arrCamCookie['session']));
$json = curl_exec ($ch);
curl_close ($ch);

// convert json to array
$arrMonitor = json_decode ($json, true);

// if monitors list is provided, generate array of cams to be displayed
if (isset($lstCam))
{
	// loop thru candidates
	$arrCandidate = explode ("-", $lstCam);
	foreach ($arrCandidate as $idxCandidate)
	{
		// loop thru monitors to get their Id
		foreach ($arrMonitor["monitors"] as $idxMonitor => $monitor)
		{
			// if monitor index is candidate, add it to the display list
			if ($idxCandidate == $monitor["Monitor"]["Id"]) $arrDisplay[] = $idxMonitor;
		}
	}
}

// if no list provided, generate ordered list from start index and max number of cams
if (empty($arrDisplay))
{
	// sort monitor array in sequence order
	foreach ($arrMonitor["monitors"] as $idxMonitor => $monitor) $arrOrdered[$idxMonitor] = $monitor["Monitor"]["Sequence"];
	asort ($arrOrdered);
	
	// loop thru ordered monitors to add monitorr to the array of cams to be displayed
	$count = 0;
	foreach ($arrOrdered as $idxMonitor => $idxDisplay) 
	{
		# check if monitor is enabled
		$monitor = $arrMonitor["monitors"][$idxMonitor];
		$enabled = $monitor["Monitor"]["Enabled"];

		// populate cams array according to camera status, start index and wall size
		if (($enabled == "1") && ($idxDisplay >= $wallIndex) && ($count < $maxCam))
		{
			// add monitor to display list
			$arrDisplay[] = $idxMonitor;

			// increment counter
			$count++;
		}
	}
}

// create camera array from sorted array of cams to be displayed
$count = 0;
foreach ($arrDisplay as $idxDisplay => $idxMonitor) 
{
	// increment counter
	$count++;

	// get monitor data
	$monitor = $arrMonitor["monitors"][$idxMonitor];
	$camWidth  = $monitor["Monitor"]["Width"];
	$camHeight = $monitor["Monitor"]["Height"];

	// calculate scale factor
	$scaleThumb = min (100, ceil (100 * $wallWidth / $nbrColumn / $camWidth));
	$scaleZoom  = min (100, ceil (100 * $zoomWidth / $camWidth));

	// add cam to array
	$arrCam[$idxDisplay]['id']       = $monitor["Monitor"]["Id"];
	$arrCam[$idxDisplay]['name']     = $monitor["Monitor"]["Name"];
	$arrCam[$idxDisplay]['width']    = $camWidth;
	$arrCam[$idxDisplay]['height']   = $camHeight;
	$arrCam[$idxDisplay]['urlthumb'] = "/camera-image.jpeg.php?id=" . $monitor["Monitor"]["Id"] . "&scale=" . $scaleThumb . "&timestamp=1";
	$arrCam[$idxDisplay]['urlzoom']  = "/camera-image.jpeg.php?id=" . $monitor["Monitor"]["Id"] . "&scale=" . $scaleZoom  . "&timestamp=1";

	// prepare cookie array
	$arrCamCookie['cam'][$count] = $monitor["Monitor"]["Id"];
}

// number of cameras to display
$nbrCam = count ($arrCam);

// save array in cookie (to be used by cam-status.php)
setcookie ('cams', serialize($arrCamCookie), 0);
?>

<!doctype html>
<html lang="fr">
<head>

<style type="text/css">
	
*, *::after, *::before { margin:0; padding:0; box-sizing:inherit; }
body { background-color:black; font-family:"Nunito", sans-serif; color:#333; font-weight:300; line-height:1.6;}
html { box-sizing:border-box; font-size:62.5%; }
.container { width: 60%; margin: 2rem auto; }
.gallery { display:grid; grid-template-columns:repeat(8,1fr); grid-gap:1px; }
.gallery_img { width:100%; height:100%; object-fit:cover; }
span { position:absolute; z-index:1; padding:2px 5px; border-radius:5px; border:0px solid white; color:black; background-color:white; opacity:0.6; font-family:arial,serif; font-size:0.8em; font-style:italic; margin-left:4px; margin-top:2px; }

<?php
echo (".gallery { display:grid; grid-template-columns:repeat(" . $nbrColumn . ", 1fr); grid-gap:1px; }");
?>

</style>

<title><?php echo ($strWallName . " - " . $nbrCam . " cameras"); ?></title>
<meta charset="UTF-8">
<script src="/js-global/FancyZoom.js" type="text/javascript"></script>
<script src="/js-global/FancyZoomHTML.js" type="text/javascript"></script>
<script type='text/javascript'>

// total number of cams on the wall
nbrCam="<?php echo ($nbrCam); ?>";

function updateImage(camIndex)
{
	// get cam image ID
	camImgId="cam" + camIndex;

	// if cam image element is fully downloaded
	if (document.getElementById(camImgId).complete == true) 
	{
		now=new Date();

		// update cam index to next cam
		camIndex++;
		if (camIndex > nbrCam) camIndex=1;

		// update next cam URL to force refresh
		camImgId="cam" + camIndex;
		camImg=document.getElementById(camImgId);
		camImgURL=camImg.src;
		camImg.src=camImgURL.substring(0,camImgURL.indexOf("timestamp=")) + 'timestamp=' + now.getTime();

		// get next cam alarm status
		statusReq = new XMLHttpRequest();
		statusReq.open("GET", "camera-status.php?index=" + camIndex, true);
		statusReq.onreadystatechange = function ()
		{
			spanId="span" + camIndex;
			spanObj=document.getElementById(spanId);
			spanObj.style.backgroundColor = statusReq.responseText;
		}
		statusReq.send(null);
	}

	// get zoom image element
	zoomImg=document.getElementById('ZoomImage');

	// if zoom image is displayed
	if (zoomImg != null)
	{
		// if zoom image element is fully downloaded
		if (zoomImg.complete == true) 
		{
			now=new Date();

			// update next cam URL to force refresh
			zoomImgURL=zoomImg.src;
			zoomImg.src=zoomImgURL.substring(0,zoomImgURL.indexOf("timestamp=")) + 'timestamp=' + now.getTime();
		}
	}

	// call update for current camera in 10 ms
	setTimeout(function() { updateImage(camIndex); }, 10);
}

// call first update for first camera in 2 s
setTimeout(function() { updateImage(1); }, 2000);

</script>
</head>

<body id="wall" onload="setupZoom()">

<div class="gallery">

<?php
// loop to declare cameras
$idxCam = 1;
foreach ($arrCam as $cam) 
{
	// display current camera
	echo ("<figure class='gallery_item'>");
	echo ("<span id='span" . $idxCam . "' >" . $cam['name'] . "</span>");
	echo ("<a href='" . $cam['urlzoom'] . "' title='" . $cam['name'] . "' ><img class='gallery_img' id='cam" . $idxCam . "' src='" . $cam['urlthumb'] . "'></a>");
	echo ("</figure>");

	// increment counters
	$idxCam++;
}	
?>

</div> 

</body>

</html>
