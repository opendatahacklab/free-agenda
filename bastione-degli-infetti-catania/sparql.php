<?php
/*--------------------------------------------------------------/
| PROXY.PHP                                                     |
| Created By: Évelyne Lachance                                  |
| Contact: eslachance@gmail.com                                 |
| Source: http://github.com/eslachance/php-transparent-proxy	|
| Description: This proxy does a POST or GET request from any   |
|         page on the authorized domain to the defined URL      |
/--------------------------------------------------------------*/

// Destination URL: Where this proxy leads to.
$destinationURL = 'http://localhost:9004/sparql';
// The only domain from which requests are authorized.  Remove this to authorize all requests
//$RequestDomain = 'example.com';
// That's it for configuration!


// Credits to Chris Hope (http://www.electrictoolbox.com/chris-hope/) for this function.
// http://www.electrictoolbox.com/php-get-headers-sent-from-browser/
if(!function_exists('apache_request_headers')) {
    function apache_request_headers() {
        $headers = array();
        foreach($_SERVER as $key => $value) {
            if(substr($key, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))))] = $value;
            }
        }
        return $headers;
    }
}
// Figure out requester's IP to ship it to X-Forwarded-For
$ip = '';
if (!empty($_SERVER['HTTP_CLIENT_IP'])) { 
    $ip = $_SERVER['HTTP_CLIENT_IP'];
    //echo "HTTP_CLIENT_IP: ".$ip;
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    //echo "HTTP_X_FORWARDED_FOR: ".$ip;
} else {
    $ip = $_SERVER['REMOTE_ADDR'];
    //echo "REMOTE_ADDR: ".$ip;
}

// IF domain name matches the authorized domain, proceed with request.
if(!isset($RequestDomain) || checkRequestAllowed($RequestDomain)) {
    $method = $_SERVER['REQUEST_METHOD'];
	if ($method == "GET") {
		$data=$_GET;
	} elseif ($method=="POST" && count($_POST)>0) {
		$data=$_POST;
	} else {
		//see http://us.php.net/manual/en/wrappers.php.php
		$data = file_get_contents('php://input');
		//$data = $HTTP_RAW_POST_DATA;
	}
    $response = proxy_request($destinationURL, $data, $method);
    $headerArray = explode("\r\n", $response['header']);
	$is_gzip = false;
	$is_chunked = false;
    foreach($headerArray as $headerLine) {
		// Toggle gzip decompression when appropriate.
		if($headerLine == "Content-Encoding: gzip") {
			global $is_gzip;
			$is_gzip = true;
		// Toggle chunk merging when appropriate
		} elseif($headerLine == "Transfer-Encoding: chunked") {
			global $is_chunked;
			$is_chunked = true;
		} else {
			// todo: Find out why this doesn't work (removes all contents!)
			header($headerLine, FALSE);
		}
    }
	$contents = $response['content'];
	if($is_chunked) {
		$contents = decode_chunked($contents);
	}
	if($is_gzip) {
		$contents = gzdecode($contents);
	}
	echo $contents;
  } else {
    echo "Unauthorized";
  }

  
  function proxy_request($url, $data, $method) {
// Based on post_request from http://www.jonasjohn.de/snippets/php/post-request.htm


	$req_dump = print_r($data, TRUE);
	// parse the given URL
	$url = parse_url($url);
	
    global $ip;
    // Convert the data array into URL Parameters like a=b&foo=bar etc.
	if ($method == "GET")  {
		$data = http_build_query($data);
		// Add GET params from destination URL
		if (array_key_exists('query',$url))
			$data = $data . $url['query'];
	} elseif ($method=="POST" && count($_POST)>0) {
		$data = http_build_query($data);
		// Add GET params from destination URL
		if (array_key_exists('query',$url))
			$data = $data . $url['query'];
	} else {
		$data = $data;
	}
    $datalength = strlen($data);
 
    // parse the given URL
    //$url = parse_url($url);
 
    if ($url['scheme'] != 'http') { 
        die('Error: Only HTTP request are supported !');
    }
 
    // extract host and path:
    $host = $url['host'];
    $path = $url['path'];
    if (array_key_exists('port', $url))
	    $port = intval($url['port']);
	else if ($url['scheme'] == 'http') 
		$port = 80;
	else if ($url['scheme'] == 'https')
		$port = 443;
	
	$fp = fsockopen($host, $port, $errno, $errstr, 30);
 
    if ($fp){
        // send the request headers:
        if($method == "POST") {
            fputs($fp, "POST $path HTTP/1.1\r\n");
        } else {
            fputs($fp, "GET $path?$data HTTP/1.1\r\n");
        }
        fputs($fp, "Host: $host\r\n");
        
        fputs($fp, "X-Forwarded-For: $ip\r\n");
        fputs($fp, "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7\r\n"); 
        
           $requestHeaders = apache_request_headers();
        while ((list($header, $value) = each($requestHeaders))) {
            if($header == "Content-Length") {
                fputs($fp, "Content-Length: $datalength\r\n");
            } else if($header !== "Connection" && $header !== "Host" && $header !== "Content-length") {
                fputs($fp, "$header: $value\r\n");
            }
        }
        fputs($fp, "Connection: close\r\n\r\n");
        fputs($fp, $data);
 
        $result = ''; 
        while(!feof($fp)) {
            // receive the results of the request
            $result .= fgets($fp, 128);
        }
    }
    else { 
        return array(
            'status' => 'err', 
            'error' => "$errstr ($errno)"
        );
    }
 
    // close the socket connection:
    fclose($fp);

    // split the result header from the content
    $result = explode("\r\n\r\n", $result, 2);
    $header = isset($result[0]) ? $result[0] : '';
    $content = isset($result[1]) ? $result[1] : '';

    // return as structured array:
    return array(
        'status' => 'ok',
        'header' => $header,
        'content' => $content
    );
}

// Credits to @flowfree (http://stackoverflow.com/users/1396314/flowfree) for this function.
// http://stackoverflow.com/questions/10793017/how-to-easily-decode-http-chunked-encoded-string-when-making-raw-http-request
function decode_chunked($str) {
  for ($res = ''; !empty($str); $str = trim($str)) {
    $pos = strpos($str, "\r\n");
    $len = hexdec(substr($str, 0, $pos));
    $res.= substr($str, $pos + 2, $len);
    $str = substr($str, $pos + 2 + $len);
  }
  return $res;
}

/**
 * Check if the current requests is allowed
 * 
 * @param string $allowedDomain
 * @return boolean
 */
function checkRequestAllowed($allowedDomain){
	if (!(array_contains_key('HTTP_REFERER', $_SERVER)))
		return false;
		$req_parts = parse_url($_SERVER['HTTP_REFERER']);
		return $req_parts["host"] == $allowedDomain;
}

?>
