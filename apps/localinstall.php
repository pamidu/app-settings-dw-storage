<?php


	function recurse_rmdir($dir) {
	  $files = array_diff(scandir($dir), array('.','..'));
	  foreach ($files as $file) {
	    (is_dir("$dir/$file")) ? recurse_rmdir("$dir/$file") : unlink("$dir/$file");
	  }
	  return rmdir($dir);
	}

function rmove($src, $dest){
    if(!is_dir($src)) return false;

    if(!is_dir($dest)) { 
        if(!mkdir($dest)) {
            return false;
        }    
    }

    $i = new DirectoryIterator($src);
    foreach($i as $f) {
        if($f->isFile()) {
            rename($f->getRealPath(), "$dest/" . $f->getFilename());
        } else if(!$f->isDot() && $f->isDir()) {
            rmove($f->getRealPath(), "$dest/$f");
            unlink($f->getRealPath());
        }
    }
    unlink($src);
}

	function extractContents(){
		$headers = apache_request_headers();
		if (isset($headers["AppKey"])){
			$folder = MEDIA_PATH . "/" . getHost() . "/apps/". $headers["AppKey"];

			if (file_exists($folder))
				recurse_rmdir($folder);
			mkdir($folder, 0777, true);

			$zipFile = "$folder/publish.zip";
			$entityBody = file_get_contents('php://input');
			file_put_contents($zipFile, $entityBody);

			$zip = new ZipArchive;
			$res = $zip->open($zipFile);
			if ($res === TRUE) {
			    $zip->extractTo($folder);
			    $zip->close();
			    echo '{"success" : true}';
			} else {
			    echo '{"success" : false}';
			}

			unlink($zipFile);
		}else{

                        $folder = MEDIA_PATH . "/" . getHost() . "/appinstalltemp/". date('YmdHis');
                        if (file_exists($folder))
                                recurse_rmdir($folder);
                        mkdir($folder, 0777, true);
                        $zipFile = "$folder/publish.zip";
                        $entityBody = file_get_contents('php://input');
                        file_put_contents($zipFile, $entityBody);
                        $zip = new ZipArchive;
                        $res = $zip->open($zipFile);
                        if ($res === TRUE) {
                            $zip->extractTo($folder);
                            $zip->close();
                            echo '{"success" : true}';
                        } else {
                            echo '{"success" : false}';
                        }
//			echo filesize($zipFile);
                        unlink($zipFile);

			$descFile = json_decode(file_get_contents("$folder/descriptor.json"));
			define("TMP_APP_KEY", $descFile->appKey);
			if ($descFile){
				$newFolder = MEDIA_PATH . "/" . getHost() . "/apps/". TMP_APP_KEY;
				
                	        if (file_exists($newFolder))
                        	        recurse_rmdir($newFolder);
	                        mkdir($newFolder, 0777, true);
	
				rmove($folder, $newFolder);
			}
			recurse_rmdir($folder);
		}
	}


	function addToRegistry(){
		$headers = apache_request_headers();
		$folder = MEDIA_PATH . "/" . getHost() . "/apps/". $headers["AppKey"];

		$perfFolder = MEDIA_PATH . "/" . getHost() . "/apppermisson/";
		if (!file_exists($perfFolder))
			mkdir($perfFolder, 0777, true);

		$perfFile = "$perfFolder/all.json";
		$perfContents;
		if (!file_exists($perfFile))
			$perfContents = new stdClass();
		else
			$perfContents = json_decode(file_get_contents($perfFile));

		if (isset($perfContents->$headers["AppKey"]))
			unset($perfContents->$headers["AppKey"]);

		$appFile = "$folder/app.json";
		$appObj = json_decode(file_get_contents($appFile));
		if (isset($appObj->secretKey))
			unset($appObj->secretKey);

		$perfContents->$headers["AppKey"] = $appObj;

		file_put_contents($perfFile, json_encode($perfContents));


		//new contents
		if (isset($_COOKIE["authData"])){
			$authData = json_decode($_COOKIE["authData"]);
			$userFile = "$perfFolder/" . $authData->Username . ".json";
			$userPerfFile = file_exists($userFile) ? $userFile : (strpos($perfFile, '.dev.') !== false ? "default.dev.json" : "default.json");
			$perfContents =  json_decode(file_get_contents($userPerfFile));
		
	               if (isset($perfContents->$headers["AppKey"]))
        	                unset($perfContents->$headers["AppKey"]);
			$perfContents->$headers["AppKey"] = $appObj;
			file_put_contents($userFile, json_encode($perfContents));
		}

	}

	function addToObjectStore(){
		require_once($_SERVER["DOCUMENT_ROOT"]. "/dwcommon.php");
		require_once($_SERVER["DOCUMENT_ROOT"]. "/payapi/duoapi/objectstoreproxy.php");
		$headers = apache_request_headers();
		$appFile = MEDIA_PATH . "/" . getHost() . "/apps/". $headers["AppKey"] . "/app.json";
		$appObj = json_decode(file_get_contents($appFile));
		$client = ObjectStoreClient::WithNamespace(getHost(),"application","123");
		$res = $client->store()->byKeyField("ApplicationID")->andStore($appObj);
		var_dump($res);
	}

	extractContents();
	addToRegistry();
	addToObjectStore();
?>
