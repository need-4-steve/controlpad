#!/usr/bin/php
<?php
include "includes/inc.ce-comm.php";
include "includes/inc.tests.php";

include "includes/inc.systemusers.php";
include "includes/inc.systems.php";
include "includes/inc.apikey.php";
include "includes/inc.users.php";
include "includes/inc.receipts.php";
include "includes/inc.rankrules.php";
include "includes/inc.commrules.php";
include "includes/inc.cmcommrules.php";
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
include "includes/inc.basiccommrules.php";

global $g_failcount;
global $g_passcount;
$g_failcount = 0;
$g_passcount = 0;

$starttime = time();

// Generate Random Email //
$sysuser_email = "info@chalkcouture.com";
$sysuser_password = "Aasdfasdf1";
$_SESSION['authemail'] = $sysuser_email;
$_SESSION['authpass'] = $sysuser_password;

////////////////////////
// Commissions System //
////////////////////////

// Handle Savy Seller Rules //
$comm_systemid = 1;
/*
// Add Savvy Seller Rule //
$basiccommrule = AddBasicCommRule($comm_systemid, 1, 1, 2000, 4000, 1, 1, 10, 0, 0, "false");
TestCheck("true", $basiccommrule, "Commission - AddBasicCommRule #1");

$basiccommrule = AddBasicCommRule($comm_systemid, 1, 1, 4000, 0, 1, 1, 20, 0, 0, "false");
TestCheck("true", $basiccommrule, "Commission - AddBasicCommRule #2");

$basiccommrule = AddBasicCommRule($comm_systemid, 1, 1, 2000, 4000, 5, 1, 10, 0, 0, "false");
TestCheck("true", $basiccommrule, "Commission - AddBasicCommRule #3");

$basiccommrule = AddBasicCommRule($comm_systemid, 1, 1, 4000, 0, 5, 1, 20, 0, 0, "false");
TestCheck("true", $basiccommrule, "Commission - AddBasicCommRule #4");

// 25% on all affiliate orders //
$basiccommrule = AddBasicCommRule($comm_systemid, 0, 1, 1, 0, 5, 1, 25, 0, 0, "false");
TestCheck("true", $basiccommrule, "Commission - AddBasicCommRule #5");

///////////////////
// Credit System //
///////////////////
$credit_system = AddSystem("Credits", 1, 1, 15, 5, 0, 3, $comm_systemid);
//$credit_system['id'] = 2;

// Handle Designer Dollars //
$invtype = 1; // Ask Mike. Is this for 5 also? // PV 
$result = AddBasicCommRule($credit_system['id'], 1, 0, 0, 0, $invtype, 1, 10, 20, 0, "true");
TestCheck("true", $basiccommrule, "Credit Designer Dollars - AddBasicCommRule #1");

// Handle Designer Debut - First Quarter... including part first month? //
//AddBasicCommRule($credit_system["id"], 1, 1, 600, 0, $invtype, 1, 10, 60, 60);

// This needs to be run on the server //
echo "UPDATE ce_rankrules SET qualify_type='12' WHERE qualify_type='1'\n";
echo "UPDATE ce_rankrules SET qualify_type='20' WHERE qualify_type='14'\n";
*/

?>