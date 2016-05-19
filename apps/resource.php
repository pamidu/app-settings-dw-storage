<?php
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
?>
