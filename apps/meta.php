<?php

                checkPermission($relativePath);
                
                $appPath = MEDIA_PATH . "/" . getHost() . "/apps/$relativePath/";
                switch ($meta){
                        case "desc":
                                echo file_get_contents($appPath. "descriptor.json");
                                header('Content-Type: application/json');
                                break;
                        case "res":
                                echo json_encode(readResources($appPath . "resources", $appPath . "resources"));
                                header('Content-Type: application/json');
                                break;
                        case "icon":
                                echo file_get_contents($appPath. "icon.png");
                                header('Content-Type: image/png');
                                break;
                        case "shares": //show shares for a particular app
                                require_once("shares.php");
                                break;
			case "bundle":
                                if (file_exists($appPath."bundle.json"))
                                        echo file_get_contents($appPath."bundle.json");
                                else echo "{\"appKey\":\"$appKey\", \"functions\":[]}";
				header('Content-Type: application/json');
				break;
			case "functions":
				if (file_exists($appPath."functions.json"))
					echo file_get_contents($appPath."functions.json");
				else echo "{\"appKey\":\"$appKey\", \"functions\":[]}";
				header('Content-Type: application/json');
				break;
                }

?>
