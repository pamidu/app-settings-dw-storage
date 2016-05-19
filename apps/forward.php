<?php

function detectRequestBody() {
    $rawInput = fopen('php://input', 'r');
    $tempStream = fopen('php://temp', 'r+');
    stream_copy_to_stream($rawInput, $tempStream);
    rewind($tempStream);
    return stream_get_contents($tempStream);
}


if (!function_exists('apache_request_headers'))  {
    function apache_request_headers()
    {
        if (!is_array($_SERVER)) {
            return array();
        }
        $headers = array();
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }

        return $headers;
    }
}

function forwardRequest($forwardHost, $tenantId){
	$ch=curl_init();
	
	$cookies = array();
	foreach ($_COOKIE as $key => $value)
	    if ($key != 'Array')
	        $cookies[] = $key . '=' . $value;
	
	curl_setopt($ch, CURLOPT_COOKIE, implode(';', $cookies));
        $currentHeaders = apache_request_headers();
        $forwardHeaders = array("Host: $tenantId", "Content-Type: application/json");
        foreach ($currentHeaders as $key => $value)
                if (!(strcmp(strtolower($key), "host") ===0 || strcmp(strtolower($key),"content-type")===0))
                        array_push($forwardHeaders, "$key : $value");
	//var_dump($forwardHeaders);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $forwardHeaders);
	curl_setopt($ch, CURLOPT_URL, "http://$forwardHost". $_SERVER["REQUEST_URI"]);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

/*
	curl_setopt($ch, CURLOPT_COOKIE, implode(';', $cookies));
	curl_setopt($ch, CURLOPT_HTTPHEADER, array("Host: $tenantId", "Content-Type: application/json"));
	curl_setopt($ch, CURLOPT_URL, "http://$forwardHost". $_SERVER["REQUEST_URI"]);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
*/	
	if($_SERVER["REQUEST_METHOD"]!="GET"){
		$postData = detectRequestBody();
		curl_setopt($ch, CURLOPT_POST, count($postData));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
	}
	$data = curl_exec($ch);
	$content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
	header("Content-type:$content_type");
	echo $data;
	exit();	
}

forwardRequest(SVC_MEDIA_URL, $_SERVER["HTTP_HOST"]);

?>
