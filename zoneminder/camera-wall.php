<?php
// -------------------------------------------------------
// Zoneminder camera wall display using Zoneminder API
//
// Display parameters :
//   ratio  - Camera ratio (default=1.77)
//   column - Number of camera per line
//   width  - Width of the wall in pixels (default=1980px)
//   zoom   - Width of a zoomed image in pixels (default=1080px)
//
// Camera selection :
//   index  - Index of first cam to display in ZM sequence list
//   maxcam - Maximum number of cameras to display
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
//   15/11/2020 - V3.1 - Switch to authentification token for ZM 1.34 API compatibility (the dirty way)
//                       Add hd and 4k resolution
//   21/05/2021 - V3.2 - Bugfix: Disable loading ZoomImage if it isn't visible
// -------------------------------------------------------

// zoneminder configuration
require_once ("camera-config.inc");

// initialisation
$arrAnswer    = Array ();
$arrCam       = Array ();
$arrDisplay   = Array ();
$arrZMCookie  = Array ();
$arrCamCookie = Array ();
$arrMonitor   = Array ();
$arrOrdered   = Array ();

// Parameter : Display ratio for cameras
$camRatio = 1.77;
if (isset($_GET["ratio"])) $camRatio = $_GET["ratio"];

// Parameter : Maximum number of cameras
$maxCam = 0;
if (isset($_GET["maxcam"])) $maxCam = $_GET["maxcam"];

// Parameter : Number of columns
$nbrColumn = 0;
if (isset($_GET["column"])) $nbrColumn = $_GET["column"];

// Parameter : Width of the wall (in pixels)
$wallWidth = 1920;
if (isset($_GET["width"])) $wallWidth = $_GET["width"];

// Parameter : wall HD (1920x1080)
if (isset($_GET["hd"]))
{
  $wallWidth = 1920;
  $camRatio  = 1.77;
}

// Parameter : wall 4K (3840x2160)
if (isset($_GET["4k"]))
{
  $wallWidth = 3840;
  $camRatio  = 1.77;
}

// Parameter : Width of the zoomed picture (in pixels)
$zoomWidth = min (1920, $wallWidth);
if (isset($_GET["zoom"])) $zoomWidth = $_GET["zoom"];

// Parameter : Index of first cam on the wall (starts from 0)
$wallIndex = 0;	
if (isset($_GET["index"])) $wallIndex = $_GET["index"];

// Parameter : List of cams to display
if (isset($_GET["cams"])) $lstCam = $_GET["cams"];

// get monitor list
$ch = curl_init();
curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt ($ch, CURLOPT_URL, $zmURL . "/api/monitors.json?" . $strToken);
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
    if (($enabled == "1") && ($idxDisplay >= $wallIndex) && (($maxCam == 0) || ($count < $maxCam)))
    {
      // add monitor to display list
      $arrDisplay[] = $idxMonitor;

      // increment counter
      $count++;
    }
  }
}

// number of cameras to display
$nbrCam = count ($arrDisplay);

// if number of column not defined, set it according to total number of cams
if ($nbrColumn == 0) $nbrColumn = ceil (sqrt ($nbrCam));

// calculate number of rows
$nbrRow = ceil ($nbrCam / $nbrColumn);
$vwRow  = floor (100 / $nbrColumn / $camRatio);

// calculate font size according to number of columns
$fontSize = 0.7 * ((7 / 3) - ($nbrColumn / 6));

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
  $sizeThumb  = $wallWidth / $nbrColumn;
  $scaleThumb = max (min (100, ceil (100 * $sizeThumb / $camWidth)), min (100, ceil (100 * $sizeThumb / $camHeight)));
  $scaleZoom  = ceil (100 * $zoomWidth / $camWidth);

  // add cam to array
  $arrCam[$idxDisplay]['id']       = $monitor["Monitor"]["Id"];
  $arrCam[$idxDisplay]['width']    = $camWidth;
  $arrCam[$idxDisplay]['height']   = $camHeight;
  $arrCam[$idxDisplay]['name']     = "[" . $monitor["Monitor"]["Id"] . "] " . $monitor["Monitor"]["Name"];
  $arrCam[$idxDisplay]['urlthumb'] = "/camera-image.jpeg.php?id=" . $monitor["Monitor"]["Id"] . "&scale=" . $scaleThumb . "&timestamp=1";
  $arrCam[$idxDisplay]['urlzoom']  = "/camera-image.jpeg.php?id=" . $monitor["Monitor"]["Id"] . "&scale=" . $scaleZoom  . "&timestamp=1";

  // prepare cookie array
  $arrCamCookie['cam'][$count] = $monitor["Monitor"]["Id"];
}

// save array in cookie (to be used by cam-status.php)
setcookie ('cams', serialize($arrCamCookie), 0);
?>

<!doctype html>
<html lang="fr">
<head>

<style type="text/css">
	
*, *::after, *::before { margin:0; padding:0; box-sizing:inherit; }

body { background-color:black; }
html { box-sizing:border-box; font-size:62.5%; }
span { position:absolute; z-index:1; margin:Opx; padding:0.1vw 0.3vw; border-radius:5px; border:0px solid white; 
       color:black; background-color:white; opacity:0.7; font-family:arial,serif; font-style:italic; }

.gallery_img { width:100%; height:100%; object-fit:cover; }

<?php
echo ("span { font-size:" . $fontSize . "vw; }");
echo (".gallery { display:grid; grid-template-columns:repeat(" . $nbrColumn . ", 1fr); grid-template-rows:repeat(" . $nbrRow . ", " . $vwRow . "vw); grid-gap:1px; }");
?>

</style>

<title><?php echo ($strWallName . " - " . $nbrCam . " cameras"); ?></title>
<meta charset="UTF-8">
<script src="/js-global/FancyZoom.js" type="text/javascript"></script>
<script src="/js-global/FancyZoomHTML.js" type="text/javascript"></script>
<script type='text/javascript'>

// total number of cams on the wall
nbrCam="<?php echo ($nbrCam); ?>";

function zoomboxActive()
{
  zoomClose = document.getElementById('ZoomClose');
  if (zoomClose != null)
  {
    return zoomClose.style.visibility !== 'hidden';
  }
  return false;
}

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
  if (zoomImg != null && zoomboxActive())
  {
    // if zoom image element is fully downloaded
    if (zoomImg.complete == true) 
    {
      // update next cam URL to force refresh
      now=new Date();
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
  echo ("</figure>\n");

  // increment counters
  $idxCam++;
}	

echo ($arrAnswer);
?>


</div> 

</body>

</html>
