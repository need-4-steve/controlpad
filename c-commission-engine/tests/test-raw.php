#!/usr/bin/php
<?php

if (empty($argv[1]))
{
	echo "run-tests.php: A base needs to be defined. Example: ./run-tests.php test-live\n";
	return;
}

$base = $argv[1];
$_SERVER["SCRIPT_NAME"] = "/".$base."/run-tests.php";

include "includes/inc.ce-comm.php";
include "includes/inc.tests.php";

include "includes/inc.systemusers.php";
include "includes/inc.systems.php";
include "includes/inc.apikey.php";
include "includes/inc.users.php";
include "includes/inc.receipts.php";
include "includes/inc.rankgenbonusrules.php";
include "includes/inc.rankrules.php";
include "includes/inc.basiccommrules.php";
include "includes/inc.commrules.php";
include "includes/inc.cmcommrules.php";
include "includes/inc.cmrankrules.php";
include "includes/inc.pools.php";
include "includes/inc.poolrules.php";
include "includes/inc.bonus.php";
include "includes/inc.signupbonus.php";
include "includes/inc.commissions.php";
include "includes/inc.my-affiliate.php";
include "includes/inc.grandpayouts.php";
include "includes/inc.reports.php";
include "includes/inc.ledger.php";
include "includes/inc.simulations.php";
include "includes/inc.rankrulesmissed.php";
include "includes/inc.chalkatour.php";
include "includes/inc.settings.php";

global $g_failcount;
global $g_passcount;
$g_failcount = 0;
$g_passcount = 0;

$starttime = time();

// Generate Random Email //
$sysuseremail = rand(1, 9999999)."wanderson@controlpad.com";
$password = "Aasdfasdf1";

$chalk_system["id"] = 1;
$batch['id'] = 3;

$mydownlinestatsfull = MyDownlineStatsFull($chalk_system["id"], 1, $batch['id']);
TestCheck("true", $mydownlinestatsfull, "MyDownlineStatsFull");


/*
$domain = "http://127.0.0.1:8080";
$headers[] = "system: 1";

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

echo $data."\n";
curl_close($ch);
*/

?>