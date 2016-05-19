<?php

function zip($source, $destination){
    if (!extension_loaded('zip') || !file_exists($source)) return false;

    $zip = new ZipArchive();
    if (!$zip->open($destination, ZIPARCHIVE::CREATE)) return false;

    $source = str_replace('\\', '/', realpath($source));

    if (is_dir($source) === true){
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);

        foreach ($files as $file){
            $file = str_replace('\\', '/', $file);
            if( in_array(substr($file, strrpos($file, '/')+1), array('.', '..'))) continue;
        	
            //$file = str_replace($source, "", realpath($file)); //;
            if (is_dir($file) === true)
                $zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
            else if (is_file($file) === true)
                $zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
        }
    }
    else if (is_file($source) === true)
        $zip->addFromString(basename($source), file_get_contents($source));
    
    return $zip->close();
}

 function postZip($zipName, $appKey, $tenant){
	$zipContents = file_get_contents($zipName);
echo (filesize($zipName));
    $ch = curl_init();

	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	curl_setopt($ch, CURLOPT_POST,1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $zipContents); 
	curl_setopt($ch, CURLOPT_HTTPHEADER, array("Host: $tenant", "AppKey: $appKey")); 
    curl_setopt($ch, CURLOPT_URL, (strcmp($_SERVER["HTTP_HOST"], "localhost") ==0? "http://localhost/apps/" : "http://localhost/apps/"));
    $data = curl_exec($ch);
    echo $data;
    curl_close($ch);
}

//get descriptor
//remote install by calling the url

$appPath = MEDIA_PATH . "/" . getHost() . "/apps/$relativePath/";

if(file_exists($appPath)){
    $descriptor = file_get_contents(MEDIA_PATH . "/" . getHost() . "/apps/$relativePath/descriptor.json");
    $appType =  ((json_decode($descriptor)->type));

    if (strcmp($appType, "APPBUNDLE") ==0 ) {
        $bundledapps = json_decode(file_get_contents(MEDIA_PATH . "/" . getHost() . "/apps/$relativePath/resources/bundle.json"));

        foreach ($bundledapps as $bapp=>$bappV){
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array("Host: ". $_SERVER["HTTP_HOST"] , "AppKey: $bapp")); 
            //curl_setopt($ch, CURLOPT_URL, "http://". $_SERVER["HTTP_HOST"] . "/apps/$bapp?install=$ins");
            //curl_setopt($ch, CURLOPT_HTTPHEADER, array("Host: ". (strcmp($_SERVER["HTTP_HOST"], getHost()) ? "localhost" : getHost()) , "AppKey: $bapp")); 
            curl_setopt($ch, CURLOPT_URL, "http://localhost/apps/$bapp?install=$ins");
            $data = curl_exec($ch);
            echo $data;
            curl_close($ch);
        }
    }


    $tempFolder = MEDIA_PATH . "/" . getHost() . "/installtemp/tempuser/";
    if (!file_exists($tempFolder))
        mkdir($tempFolder, 0777, true);



    zip($appPath, "$tempFolder$relativePath.zip");

    postZip("$tempFolder$relativePath.zip",$relativePath, $ins);
echo "3";
  //  unlink("$tempFolder$relativePath.zip");
}



?>
