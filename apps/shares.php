<?php

$userFile;
$perfFile;

function getUserFiles(){
	$authObj = json_decode($_COOKIE["authData"]);
	$username = $authObj->Username;
	$userFile = MEDIA_PATH . "/" . getHost() . "/apppermisson/$username.json";
	$perfFile = file_exists($userFile) ? $userFile : (strpos($perfFile, '.dev.') !== false ? "default.dev.json" : "default.json");
}


        function recurse_rmdir($dir) {
          $files = array_diff(scandir($dir), array('.','..'));
          foreach ($files as $file) {
            (is_dir("$dir/$file")) ? recurse_rmdir("$dir/$file") : unlink("$dir/$file");
          }
          return rmdir($dir);
        }

if (isset($share)){ //share an app
	$appKey = $relativePath;

	$allFile = MEDIA_PATH . "/" . getHost() . "/apppermisson/all.json";
	$allApps =  json_decode(file_get_contents($allFile));
	$appObj = $allApps->$appKey;

	$shareUsers = explode(",", $share);

	foreach ($shareUsers as $shareUser) {
		$userFile = MEDIA_PATH . "/" . getHost() . "/apppermisson/" . trim($shareUser) . ".json";
		$perfFile = file_exists($userFile) ? $userFile : (strpos($userFile, '.dev.') !== false ? "default.dev.json" : "default.json");
		$userAppObj = json_decode(file_get_contents($perfFile));
		$userAppObj->$appKey = $appObj;
		file_put_contents($userFile, json_encode($userAppObj));
	}

	echo '{"success":true, "message": "$appKey is shared amoung $shareUsers"}';
} 
else if (isset($unshare)){ //unshare an app
/*
	$appKey = $relativePath;

	$allFile = MEDIA_PATH . "/" . getHost() . "/apppermisson/all.json";
	$allApps =  json_decode(file_get_contents($allFile));
	$appObj = $allApps->$appKey;

	$shareUsers = explode(",", $unshare);

	foreach ($shareUsers as $shareUser) {
		$userFile = MEDIA_PATH . "/" . getHost() . "/apppermisson/" . trim($shareUser) . ".json";
		if (!file_exists($userFile))
			copy ("default.json", $userFile);
		$userAppObj = json_decode(file_get_contents($userFile));
		if (isset($userAppObj->$appKey)){
			unset($userAppObj->$appKey);
			file_put_contents($userFile, json_encode($userAppObj));
		}
		
	}

	$isAvailable = false;
	if (!$isAvailable && isset ($allApps->$appKey)){
		unset ($allApps->$appKey);
		file_put_contents($allFile, json_encode($allApps));
		$appContentFolder = MEDIA_PATH . "/" . getHost() . "/apps/$appKey";
		if (file_exists($appContentFolder))
			recurse_rmdir($appContentFolder);
	}
*/
	echo '{"success":true, "message": "$appKey is unshared amoung $shareUsers"}';

}
else { //get shares of apps
	$appKey = substr($relativePath, 1);
	$filterUsers = strlen($appKey) <= 1 ? false: true;

	$outArray = array();
	if ($dh = opendir(MEDIA_PATH . "/" . getHost() . "/apppermisson")){
		while (($file = readdir($dh)) !== false){
		  $userApps =  json_decode(file_get_contents($allFile));
		  if (isset($userApps->$appKey))
		  	array_push($outArray, str_replace(".json", "", $file));
		}
			closedir($dh);
	}
	echo json_encode($outArray);
}

header('Content-Type: application/json');

?>
