<?php
	class User{
		
		public $username;
		public $email;
		public $firstname;
		public $lastname;
		public $userid;
	}
	
	class MediaService {

		
		private function test(){
			echo "Hello from media service";
		}

		private function uploadMedia($namespace,$class,$id){
			$this->forwardIfNecessary($namespace);

		 	$folderLocation = MEDIA_PATH . "/" . $namespace . "/media/" . $class;

			if (!file_exists($folderLocation)) {
				mkdir($folderLocation, 0777, true);
			}

			if (json_encode(file_put_contents($folderLocation."/$id", Flight::request()->getBody())))
				echo '{"success":true, "message":"Media successfully uploaded!!!"}';
			else{
				echo '{"success":false, "message":"Error uploading media"}';
				http_response_code(500);
			}

			header('Content-Type: application/json');
		}
	
		private function getMedia($namespace,$class,$id){
			$this->forwardIfNecessary($namespace);

			$mediaFile = MEDIA_PATH. "/" . $namespace . "/media/" . $class."/$id";
			if (file_exists($mediaFile)) {
				header('Content-Type: '. mime_content_type($mediaFile));
				echo file_get_contents($mediaFile);
			}
			else{
				header('Content-Type: application/json');
				http_response_code(404);
				echo '{"success":false, "message":"404 resource not found"}';
			}

		}

		private function getThumbnail($size, $namespace,$class,$id){

			$this->forwardIfNecessary($namespace);

			$originalfile=MEDIA_PATH. "/" . $namespace . "/media/" . $class;
			$storeFolder = THUMB_PATH. "/" . $namespace . "/media/" . $class;
			if (file_exists($originalfile."/".$id.".jpg")==false) {
				echo json_encode("File Not Found");
			}
			else {

				$file=glob($originalfile."/".$id."*");
				$filename = $file[0];
				$image = imagecreatefromjpeg($file[0]);
				echo json_encode($image);
				header('Content-Type: image/jpg');
				$newwidth =$size;
				$newheight = $size;
				$new_image = imagecreatetruecolor($newwidth, $newheight);
				imagecopyresampled($new_image, $image, 0, 0,0, 0, $newwidth, $newheight, $newwidth, $newheight);
				$image = $new_image; 
				imagejpeg($new_image, $storeFolder."/"."$id.jpg",95);
				header('Content-Type: image/jpg');
				$imagedata=file_get_contents($storeFolder."/"."$id.jpg");
				echo $imagedata;

			}
		}

		private function getUserSpace(){
			$authObj = json_decode($_COOKIE["authData"]);
			$username = $authObj->Username;

			return str_replace(".", "", str_replace("@", "", $username)). ".space." . MAIN_DOMAIN;
		}


		private function detectRequestBody() {
		    $rawInput = fopen('php://input', 'r');
		    $tempStream = fopen('php://temp', 'r+');
		    stream_copy_to_stream($rawInput, $tempStream);
		    rewind($tempStream);

		    return stream_get_contents($tempStream);
		}

		private function forwardIfNecessary($tenantId){
			return;
			if (strcmp($_SERVER["HTTP_HOST"], $tenantId) === 0){ //same domain
				if (defined("SVC_MEDIA_URL")){ //forward to media server
					$this->forwardRequest(SVC_MEDIA_URL, $tenantId);
				} else { //save in the same server
					return;	
				}
			} 
			else $this->forwardRequest($tenantId, $tenantId); //forward cross domain requests
		}

		private function forwardRequest($forwardHost, $tenantId){

			$ch=curl_init();
			
			$cookies = array();
			foreach ($_COOKIE as $key => $value)
			    if ($key != 'Array')
			        $cookies[] = $key . '=' . $value;
			
			curl_setopt($ch, CURLOPT_COOKIE, implode(';', $cookies));
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("Host: $tenantId", "Content-Type: application/json"));
			curl_setopt($ch, CURLOPT_URL, "http://$forwardHost". $_SERVER["REQUEST_URI"]);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

			if($_SERVER["REQUEST_METHOD"]!="GET"){
				$postData = $this->detectRequestBody();
				curl_setopt($ch, CURLOPT_POST, count($postData));
		        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
			}
			$data = curl_exec($ch);
			$content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
			header("Content-type:$content_type");
			echo $data;
			exit();	
		}

		function __construct(){

			Flight::route("POST /tenant/@class/@id", function($class,$id){$this->uploadMedia($_SERVER["HTTP_HOST"],$class,$id);});
			Flight::route("GET /tenant/@class/@id", function($class,$id){$this->getMedia($_SERVER["HTTP_HOST"],$class,$id);});
		
			Flight::route("POST /user/@class/@id", function($class,$id){$this->uploadMedia($this->getUserSpace(),$class,$id);});
			Flight::route("GET /user/@class/@id", function($class,$id){$this->getMedia($this->getUserSpace(),$class,$id);});

			Flight::route("GET /tenant/thumbnails/@size/@class/@id", function($size,$namespace,$class,$id){$this->getThumbnail($size,$_SERVER["HTTP_HOST"],$class,$id);});
			Flight::route("GET /user/thumbnails/@size/@class/@id", function($size,$namespace,$class,$id){$this->getThumbnail($size,$this->getUserSpace(),$class,$id);});

			Flight::route("GET /test",function(){$this->test();});
		}
	}

?>
