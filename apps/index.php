<?php
header("Access-Control-Allow-Origin: *");
//ini_set('display_errors', 1); 
//error_reporting(E_ALL);
require_once("../include/config.php");
//define("SVC_MEDIA_URL", "dw-storage");
if (defined("SVC_MEDIA_URL")) require_once("forward.php");

function getHost(){
	return ($_SERVER["HTTP_HOST"] == "localhost" ? "12door.dev" : $_SERVER["HTTP_HOST"]);
}

if (!function_exists('apache_request_headers')) {
        function apache_request_headers() {
            foreach($_SERVER as $key=>$value) {
                if (substr($key,0,5)=="HTTP_") {
                    $key=str_replace(" ","-",ucwords(strtolower(str_replace("_"," ",substr($key,5)))));
                    $out[$key]=$value;
                }else{
                    $out[$key]=$value;
        }
            }

		if (defined("TMP_APP_KEY"))
			$out["AppKey"] = TMP_APP_KEY;
            return $out;
        }
} 

function checkPermission($appKey){
	$isPermitted = true;
	if (!$isPermitted) {
		http_response_code(403);
		echo "Unauthorized (403)";
		exit();
	}
}	

function readResources($root, $dir){
	  $arr = array();
	  $files = array_diff(scandir($dir), array('.','..'));
	  foreach ($files as $file) {
	    if (is_dir("$dir/$file")) $arr = array_merge(readResources ($root, $arr, "$dir/$file"), $arr);
	    else {
	    	$fileObj = new stdClass();
	    	$fileObj->id = str_replace($root. "/", "", "$dir/$file");
	    	$fileObj->data = file_get_contents("$dir/$file");
	   		array_push($arr, $fileObj);
	    }
	  }
	  return $arr;
}

function system_extension_mime_types() {
    # Returns the system MIME type mapping of extensions to MIME types, as defined in /etc/mime.types.
    $out = array();
    $file = fopen('/etc/mime.types', 'r');
    while(($line = fgets($file)) !== false) {
        $line = trim(preg_replace('/#.*/', '', $line));
        if(!$line)
            continue;
        $parts = preg_split('/\s+/', $line);
        if(count($parts) == 1)
            continue;
        $type = array_shift($parts);
        foreach($parts as $part)
            $out[$part] = $type;
    }
    fclose($file);
    return $out;
}

function system_extension_mime_type($file) {
    static $types;
    if(!isset($types))
        $types = system_extension_mime_types();
    $ext = pathinfo($file, PATHINFO_EXTENSION);
    if(!$ext)
        $ext = $file;
    $ext = strtolower($ext);
    return isset($types[$ext]) ? $types[$ext] : null;
}


$relativePath = str_replace('/apps','',$_SERVER["REQUEST_URI"]);

$meta;
if (isset($_GET["meta"])){
	$meta = $_GET["meta"];
	$relativePath = str_replace("?meta=$meta",'',$relativePath);
}

$ins;
if (isset($_GET["install"])){
	$ins = $_GET["install"];
	$relativePath = str_replace("?install=$ins",'',$relativePath);
}

$share;
if (isset($_GET["share"])){
	$share = $_GET["share"];
	$relativePath = str_replace("?share=$share",'',$relativePath);
}

$unshare;
if (isset($_GET["unshare"])){
	$unshare = $_GET["unshare"];
	$relativePath = str_replace("?unshare=$unshare",'',$relativePath);
}

$uninst;
if (isset($_GET["uninstall"])){
        $uninst = $_GET["uninstall"];
        $relativePath = str_replace("?uninstall=$uninst",'',$relativePath);
}

if ($relativePath =="/"){
	if ($_SERVER['REQUEST_METHOD'] == "GET") require_once("allapps.php");
	else require_once("localinstall.php");
}else{

	$relativePath = substr($relativePath, 1);
	if (isset($meta))require_once("meta.php");
	else if (isset($ins)) require_once("remoteinstall.php");
	else if (isset($share)) require_once("shares.php");
	else if (isset($unshare)) require_once("shares.php");
	else if (isset($uninst)) require_once("uninstall.php");
	else { 
		$slashPos = strpos($relativePath, "/");
		$appKey = substr($relativePath, 0, $slashPos);
		checkPermission($appKey);
		$relativePath = substr($relativePath, $slashPos);
		$fullPath = rawurldecode(MEDIA_PATH . "/" . getHost() . "/apps/$appKey/resources$relativePath");
		if (!file_exists($fullPath)){
		  http_response_code(404);
		  echo "Requested resource not found (404)";
		} else {
		  header('Content-Type: '. system_extension_mime_type($fullPath));
		  echo file_get_contents($fullPath);  
		}
	}

}

?>

