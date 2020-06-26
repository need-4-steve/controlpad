<?php

// nginx vs apache - Stoopid work around //
if (strstr($_SERVER['SERVER_SOFTWARE'], "nginx") != FALSE)
{
	$CORESERVER = "nginx";
	$http_auth = "HTTP_AUTHORIZATION";
	$_ALLHEADERS = $_SERVER;
}
else // apache failsafe //
{
	$CORESERVER = "apache";
	$http_auth = "authorization";
	$_ALLHEADERS = getallheaders(); // apache2 - Stoopid work around //
}

// Preflight headers //
if ($CORESERVER == "nginx")
{
    header("Access-Control-Allow-Origin: {$_ALLHEADERS['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400'); // cache for 1 day

    // Access-Control headers are received during OPTIONS requests
	if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS')
	{
	    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
	    	header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE, PATCH");
	        //header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

	    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
	        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
	    
	    return;
	}
}
else if ($CORESERVER == "apache")
{
	header("Access-Control-Allow-Origin: *");
	header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE, PATCH");
	header("Access-Control-Allow-Credentials: true");
	header("Access-Control-Max-Age: 86400");

	if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS')
	{
		//file_put_contents("/tmp/headers.txt", $_ALLHEADERS["Access-Control-Request-Headers"]);

		if (stripos($_ALLHEADERS['HTTP_USER_AGENT'], 'Chrome') !== false)
			header("Access-Control-Allow-Headers: *");
		else
			header("Access-Control-Allow-Headers: Origin, Content-Type, Range, Authorization, limit, orderdir, orderby, offset, sort, command, systemid, userid, varname, batchid, X-Cp-Request-Id, X-Cp-Org-Id");
			
		//////////////////////////////////////////////////
		// Problems with iPhone and Safari. 	        //
		// It'll have to be done dynamically eventually //
		//////////////////////////////////////////////////

		//else // All other browsers //
		//	header("Access-Control-Allow-Headers: *");

	 	//header("Access-Control-Allow-Headers: ".$_ALLHEADERS["Access-Control-Request-Headers"]);
	 	//header("Access-Control-Allow-Headers: Origin, Content-Type, Range, Authorization, orderdir, orderby, offset, sort, command, systemid, userid, X-Cp-Request-Id");

		// elseif (stripos($_ALLHEADERS['HTTP_USER_AGENT'], 'Safari') !== false)

	 	return;
	}
}

// Define which ceapi server we are talking to //
global $g_apiserver;
$g_apiserver = "http://127.0.0.1";

// Debug to temp files //
//file_put_contents("/tmp/api-affil-post.txt", json_encode($_POST));
//file_put_contents("/tmp/api-affil-get.txt", json_encode($_GET));
//file_put_contents("/tmp/api-affil-server.txt", json_encode($_SERVER));
//file_put_contents("/tmp/api-affil-allheaders.txt", json_encode(getallheaders()));

if (empty($_ALLHEADERS[$http_auth])) // Safari workaround //
	$http_auth = "Authorization"; // Capitol A needed for safari //
	
// jwt auth error message //
if (empty($_ALLHEADERS[$http_auth])) 
{
	echo '{"response":"error","errormessage":"jwt authorization and bearer missing in http headers"}';
}

$pretoken = $_ALLHEADERS[$http_auth]; // Stoopid work around //
$token = str_replace("Bearer ", "", $pretoken);
$base = explode(".", $token);
$json = base64_decode($base[1]);

if (empty($token))
{
	echo '{"response":"error","errormessage":"Problem with empty jwt data in Authorization headers"}';
	return;
}

$obj = json_decode($json, true);

// Scan all "*-live.ini" files in /etc/ceapi/
$files = scandir("/etc/ceapi/");
foreach ($files as $filename)
{
	if (strstr($filename, "-live.ini") != FALSE)
	{
		$orgid = FindValueInFile($filename, "orgid");
		if (trim($orgid) == $obj['orgId'])
		{
			// Get the port from the correct file //
			$port = FindValueInFile($filename, "listen_port");

			// Find out which sim in use //
			$headers["authorization"] = $token; //$_POST['authorization'];
			$headers["command"] = "settingsget"; //\r\n";
			$headers["systemid"] = "1"; //\r\n";
			$headers["varname"] = "sim-inuse"; //\r\n";

//file_put_contents("/tmp/api-headers.txt", json_encode($headers));

			$livedata = SendCommandServer($port, $headers);

//file_put_contents("/tmp/api-livedata.txt", $livedata);

			if (strlen($livedata) == 0)
			{
				echo '{"response":"error","errormessage":"siminuse lookup failed"}';
				return;
			}

			if (strstr($livedata, "error") != FALSE)
			{
				echo $livedata;
				return;
			}
		
			$livejson = json_decode($livedata);		
			
			$siminuse = $livejson->settings[0]->value;
			if (strlen($siminuse) == 0)
			{
				echo $livejson; //'{"response":"error","errormessage":"siminuse lookup data not present"}';
				return;
			}

			// Build data for sim to send commands to //
			$base = str_replace("live.ini", "", $filename);
			$simfile = $base.$siminuse.".ini";
			$simport = FindValueInFile($simfile, "listen_port");

			// Send the forward the commands to the server //
			$_POST["authorization"] = $token;
			//$retval = SendCommandServer($simport, $_ALLHEADERS); //$_SERVER); //$_POST); //$_POST);
			$retval = SendCommandServer($simport, $_ALLHEADERS);
			
//file_put_contents("/tmp/api-retval.txt", $retval);

			echo $retval;
			return; // Remove possible duplicates //
		}
	}
}

echo '{"response":"error","errormessage":"orgid not found"}';

///////////////////////////
// Easily debug with Pre //
///////////////////////////
function Pre($array)
{
	echo "<pre>";
	print_r($array);
	echo "</pre>";
}

/////////////////////////////////
// Find a port in the ini file //
/////////////////////////////////
function FindValueInFile($filename, $varname)
{
	if (($handle = fopen("/etc/ceapi/".$filename, "r")) !== FALSE)
	{
	    while (($data = fgetcsv($handle, 1000, "=")) !== FALSE)
	    {
	    	if (trim($data[0]) == $varname)
	    	{
	    		return trim($data[1]);
	    	}
	    }
	}

   	fclose($handle);
}

///////////////////////////////
// Find the sim server inuse //
///////////////////////////////
function SendCommandServer($port, $post)
{
	global $g_apiserver;

	// Handle POST fields //
	foreach ($post as $field => $value)
	{
		$headers[] = $field.": ".$value;
	} 

	$domain = $g_apiserver.":".$port;

//file_put_contents("/tmp/api-domain.txt", $domain);
//file_put_contents("/tmp/api-send-headers.txt", json_encode($headers));

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $domain);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	//curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
	//curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20000);
	curl_setopt($ch, CURLOPT_TIMEOUT, 20000);

	// The quick work around for ssl //
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	// Use the proper fix for production //
	//http://unitstep.net/blog/2009/05/05/using-curl-in-php-to-access-https-ssltls-protected-sites/

	$data = curl_exec($ch);
	$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);

	return $data;
}

?>
