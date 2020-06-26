<?php

// These needed for Commission Engine API connection //
global $coredomain;
global $authemail;
global $apikey;
$coredomain = "http://127.0.0.1:8080"; //https://nospy.mobi"; //https://ce.controlpad.com";
$authemail = "master@commissions.com";
$apikey = "a86c56c8859e2e5a954891551db9d1019509c80e15cf1519277a048157d59";

// 742053e0f5cc38103819ce15aba2c02d5fc65047db2098d4795f187ae0b2b4c5

///////////////////////////////////
// Handle processing curl method //
///////////////////////////////////
function PostURL($url, $headers)
{
	// There was a speed performance issue I discovered with POST data
	// There was overhead of an extra packet sent back and forth //
	// Hence why it's all switched over to header vars //

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	//curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
	curl_setopt($ch, CURLOPT_TIMEOUT, 30);

	// The quick work around for ssl //
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	// Use the proper fix for production //
	//http://unitstep.net/blog/2009/05/05/using-curl-in-php-to-access-https-ssltls-protected-sites/

	$data = curl_exec($ch);
	$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	//echo $data."<br>";
	curl_close($ch);

	if (strlen($data) == 0)
	{
		echo "<h3><font color=red>Commission Engine API is down</font></h3>";
		echo "<h3><font color=blue>URL = ".$url."</font></h3>";
		exit; // No need to continue if it's down //
	}
	
	return $data;
}

//////////////////////
// Testing function //
//////////////////////
function Pre($array)
{
	echo "<pre>";
	print_r($array);
	echo "</pre>";
}

//////////////////////////
// Check the Json Error //
//////////////////////////
function JsonErrorCheck($retarray)
{
	if ($retarray['errors']['source'] == "API")
	{
		echo "<font color=red><b>Failed</b></font>";
		echo "<table cellpadding=0 cellspacing=0>";
		echo "<tr><td align=right>status:</td><td><font color=red>&nbsp;&nbsp;".$retarray['errors']['status']."</font></td></tr>";
		echo "<tr><td align=right>title:</td><td><font color=red>&nbsp;&nbsp;".$retarray['errors']['title']."</font></td></tr>";
		echo "<tr><td align=right>detail:</td><td><font color=red>&nbsp;&nbsp;".$retarray['errors']['detail']."</font></td></tr>";
		echo "</table>";
		return true;
	}
	return false;
}

////////////////////////////////////////////
// Generate a random string of characters //
////////////////////////////////////////////
function GenRanNumStr($length)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

////////////////////////////////////////////
// Generate a random string of characters //
////////////////////////////////////////////
function GenRanStr($length)
{
    $characters = 'abcdefghijklmnopqrstuvwxyz';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

////////////////////////////////////////////
// Generate a random string of characters //
////////////////////////////////////////////
function GenNumStr($length)
{
    $characters = '0123456789';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}
?>