<?php
// -------------------------------------------------------
//        Credentials for ZoneMinder camera wall
// -------------------------------------------------------

// zoneminder parameters
// ---------------------

// server URL
$zmURL  = "http://127.0.0.1/zm";

// credentials
$zmUser = "youruser";
$zmPass = "yourpassword";

// wall name
$strWallName = "Your wall title";

// token generation
// ----------------

// token cache file
$zmToken = "/tmp/camera-wall.token";

// check token validity
$last_modified = (file_exists($zmToken)) ? filemtime ($zmToken) : 0;
if ((time () - 3000) < $last_modified) $strToken = file_get_contents ($zmToken);
else
{	
  // login to zoneminder
  $ch = curl_init();
  curl_setopt ($ch,CURLOPT_RETURNTRANSFER, 1);
  curl_setopt ($ch,CURLOPT_URL, $zmURL . "/api/host/login.json");
  curl_setopt ($ch,CURLOPT_POSTFIELDS, "username=" . $zmUser . "&password=" . $zmPass);
  $json = curl_exec ($ch);
  curl_close ($ch);

  // convert json to array
  $arrAnswer = json_decode($json, true);
  $strToken  = "token=" . $arrAnswer["access_token"];

  // save token
  file_put_contents ($zmToken, $strToken);
}
?>
