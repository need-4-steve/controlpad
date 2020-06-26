<?php

global $g_masterauthemail;
global $g_masterapikey;
global $g_coredomain;
global $g_simdomain;
global $g_siminuse;

// Handle database dump through this account //
global $g_pguser;
global $g_pghost;

// Usually http for development vs https for live //
global $g_protocol;

//////////////////////////////////////////////////
// Grab the current URL path to identify client //
//////////////////////////////////////////////////
function GetUrlPath()
{
	// Grab the servername and path //
	//echo "SERVER_NAME = ".$_SERVER['SERVER_NAME']."<br>";
	//echo "SCRIPT_NAME = ".$_SERVER['SCRIPT_NAME']."<br>";
	$file = $_SERVER["SCRIPT_NAME"];
	$break = explode('/', $file);
	$phpfile = $break[count($break) - 1];
	$pathonly = str_replace($phpfile, "", $_SERVER['SCRIPT_NAME']); // This will identify which company //
	//echo "pathonly = ".$pathonly."<br>";
	//echo "final = ".$_SERVER['SERVER_NAME'].$pathonly."<br>";
	return $pathonly;
}

/////////////////////////////////////////
// Handle basic processing curl method //
/////////////////////////////////////////
function BasicPostURL($headers, $domain)
{
	global $g_coredomain;
	global $g_simdomain;
	global $g_command;

	// There was a speed performance issue I discovered with POST data
	// There was overhead of an extra packet sent back and forth //
	// Hence why it's all switched over to header vars //

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $domain);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	//curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
	//curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3000);
	curl_setopt($ch, CURLOPT_TIMEOUT, 3000);

	// The quick work around for ssl //
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	// Use the proper fix for production //
	//http://unitstep.net/blog/2009/05/05/using-curl-in-php-to-access-https-ssltls-protected-sites/

	$data = curl_exec($ch);
	$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

	//echo $data."<br>";
	curl_close($ch);

	return json_decode($data, true);
}

// 
////////////////////
// Query Settings //
////////////////////
function BasicGetSettingsValue($domain, $email, $apikey, $systemid, $search, $sort)
{
	$headers[] = "authemail: ".$email;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "command: settingsquerysystem";
	$headers[] = "systemid: ".$systemid;
	$headers[] = "search: ".$search;
	$headers[] = "sort: ".$sort."orderby=id&orderdir=asc&offset=0&limit=1";

	return BasicPostURL($headers, $domain);
}

?>