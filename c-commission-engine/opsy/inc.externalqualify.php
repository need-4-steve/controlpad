<?php

///////////////////////////////////
// Handle processing curl method //
///////////////////////////////////
function SimpleCURL($headers)
{
	global $g_coredomain;

	// This should really be defined in an external from project include file. i.e /etc/ceapi/inc.global.php
	$g_coredomain = "https://ceapidev.controlpad.com:8080"; // test-live is port 8080 //

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $g_coredomain);
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

//////////////////////////
// Add external qualify //
//////////////////////////
function AddExtQualify($systemid, $userid, $varid, $value, $eventdate)
{
	// Authentication //
	$headers[] = "authemail: ".$g_masterauthemail;
	$headers[] = "apikey: ".$g_masterapikey;
	//$headers[] = "authemail: ".$_SESSION['authemail'];
	//$headers[] = "authpass: ".$_SESSION['authpass'];
	//$headers[] = "affiliateemail: ".$_SESSION['useremail'];
	//$headers[] = "affiliatepass: ".$_SESSION['userpass'];

	// Parameters //
	$headers[] = "command: addextqualify";
	$headers[] = "systemid: ".$systemid;
	$headers[] = "userid: ".$userid;
	$headers[] = "varid: ".$varid;
	$headers[] = "value: ".$value;
	$headers[] = "eventdate: ".$eventdate;
	
	$json = SimpleCURL($headers);
	return $json;
}

///////////////////////////
// Edit external qualify //
///////////////////////////
function EditExtQualify($systemid, $id, $userid, $varid, $value, $eventdate)
{
	// Authentication //
	$headers[] = "authemail: ".$g_masterauthemail;
	$headers[] = "apikey: ".$g_masterapikey;
	//$headers[] = "authemail: ".$_SESSION['authemail'];
	//$headers[] = "authpass: ".$_SESSION['authpass'];
	//$headers[] = "affiliateemail: ".$_SESSION['useremail'];
	//$headers[] = "affiliatepass: ".$_SESSION['userpass'];

	// Parameters //
	$headers[] = "command: editextqualify";
	$headers[] = "systemid: ".$systemid;
	$headers[] = "id: ".$id;
	$headers[] = "userid: ".$userid;
	$headers[] = "varid: ".$varid;
	$headers[] = "value: ".$value;
	$headers[] = "eventdate: ".$eventdate;
	
	$json = SimpleCURL($headers);
	return $json;
}

///////////////////////////
// Edit external qualify //
///////////////////////////
function QueryExtQualify($systemid)
{
	// Authentication //
	$headers[] = "authemail: ".$g_masterauthemail;
	$headers[] = "apikey: ".$g_masterapikey;
	//$headers[] = "authemail: ".$_SESSION['authemail'];
	//$headers[] = "authpass: ".$_SESSION['authpass'];
	//$headers[] = "affiliateemail: ".$_SESSION['useremail'];
	//$headers[] = "affiliatepass: ".$_SESSION['userpass'];

	// Parameters //
	$headers[] = "command: queryextqualify";
	$headers[] = "systemid: ".$systemid;
	
	$json = SimpleCURL($headers);
	return $json;
}

//////////////////////////////
// Disable external qualify //
//////////////////////////////
function DisableExtQualify($systemid, $id)
{
	// Authentication //
	$headers[] = "authemail: ".$g_masterauthemail;
	$headers[] = "apikey: ".$g_masterapikey;
	//$headers[] = "authemail: ".$_SESSION['authemail'];
	//$headers[] = "authpass: ".$_SESSION['authpass'];
	//$headers[] = "affiliateemail: ".$_SESSION['useremail'];
	//$headers[] = "affiliatepass: ".$_SESSION['userpass'];

	// Parameters //
	$headers[] = "command: disableextqualify";
	$headers[] = "systemid: ".$systemid;
	$headers[] = "id: ".$id;
	
	$json = SimpleCURL($headers);
	return $json;
}

/////////////////////////////
// Enable external qualify //
/////////////////////////////
function EnableExtQualify($systemid, $id)
{
	// Authentication //
	$headers[] = "authemail: ".$g_masterauthemail;
	$headers[] = "apikey: ".$g_masterapikey;
	//$headers[] = "authemail: ".$_SESSION['authemail'];
	//$headers[] = "authpass: ".$_SESSION['authpass'];
	//$headers[] = "affiliateemail: ".$_SESSION['useremail'];
	//$headers[] = "affiliatepass: ".$_SESSION['userpass'];

	// Parameters //
	$headers[] = "command: enableextqualify";
	$headers[] = "systemid: ".$systemid;
	$headers[] = "id: ".$id;
	
	$json = SimpleCURL($headers);
	return $json;
}

/////////////////////////////
// Enable external qualify //
/////////////////////////////
function GetExtQualify($systemid, $id)
{
	// Authentication //
	$headers[] = "authemail: ".$g_masterauthemail;
	$headers[] = "apikey: ".$g_masterapikey;
	//$headers[] = "authemail: ".$_SESSION['authemail'];
	//$headers[] = "authpass: ".$_SESSION['authpass'];
	//$headers[] = "affiliateemail: ".$_SESSION['useremail'];
	//$headers[] = "affiliatepass: ".$_SESSION['userpass'];

	// Parameters //
	$headers[] = "command: getextqualify";
	$headers[] = "systemid: ".$systemid;
	$headers[] = "id: ".$id;
	
	$json = SimpleCURL($headers);
	return $json;
}

?>