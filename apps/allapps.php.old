<?php

$authObj = json_decode($_COOKIE["authData"]);
$username = $authObj->Username;

$spaceOwner = str_replace(".", "", str_replace("@", "", $username)). ".space." . $mainDomain;
if (strcmp($spaceOwner, $_SERVER["HTTP_HOST"]) !==false)
	$perfFile = MEDIA_PATH . "/" . getHost() . "/apppermisson/all.json";
else{
	$perfFile = MEDIA_PATH . "/" . getHost() . "/apppermisson/$username.json";
	$perfFile = file_exists($perfFile) ? $perfFile : (strpos($perfFile, '.dev.') !== false ? "default.dev.json" : "default.json");	
}

$contents =  json_decode(file_get_contents($perfFile));

$allApps = array();
foreach ($contents as $k)
if ($k!==null){
	array_push($allApps, $k);
}

echo json_encode($allApps);
header('Content-Type: application/json');

?>