#!/usr/bin/php
<?php

$_SERVER["SCRIPT_NAME"] = "/tzin-live/newplan.php";

include "../tests/includes/inc.ce-comm.php";
include "../tests/includes/inc.systemusers.php";
include "../tests/includes/inc.systems.php";
include "../tests/includes/inc.rankrules.php";
include "../tests/includes/inc.commrules.php";
include "../tests/includes/inc.basiccommrules.php";
include "../tests/includes/inc.tests.php";

$starttime = time();

// Main Account //
$sysmain_email = "master@commissions.com";
$sysmain_pass = "my.co#5YvhgW34&&.gf:gf*()23oties.com";

// Generate Random Email //
$sysuser_email = "wanderson5@controlpad.com";
$sysuser_password = "easypass";
$_SESSION['authemail'] = $sysuser_email;
$_SESSION['authpass'] = $sysuser_password;

// Add a system User //
echo "[ TZIN - AddSystemUser:: ]\n";
$result = AddSystemUser($sysuser_email);
TestCheck("true", $result, "TZIN - AddSystemUser");
Pre($result);

// Add a system //
echo "[ TZIN - AddSystem:: ]\n";
$commtype = 1;
$payouttype = 1;
$payoutmonthday = 15;
$minpay = 5;
$signupbonus = 0;
$teamgenmax = 3;
//$piggyid = 1;
$psqlimit = 0; // They must have 350 in personal sales to even qualify //
$compression = "true";
$system_comm = AddSystem("Commissions", $commtype, $payouttype, $payoutmonthday, $minpay, $signupbonus, $teamgenmax, $piggyid, $psqlimit, $compression);
TestCheck("true", $system_comm, "TZIN - AddSystem");
//Pre($system);

$invtype = "1"; // Wholesale //
$event = "1"; // On Retail Date //
$paytype = "1";
$max_rank = "2";

/////////////
// Stylist //
/////////////
$result = AddRankRule($system_comm['id'], "Stylist", "1", 12, 500, 0, 1, 0, 0);
TestCheck("true", $result, "Stylist - AddRankRule PV");

////////////////////
// Stylist-SENIOR //
////////////////////
$result = AddRankRule($system_comm['id'], "Stylist-SENIOR", "2", 12, 1500, 0, 2, 0, 0);
TestCheck("true", $result, "Stylist-SENIOR - AddRankRule PV");

$result = AddRankRule($system_comm['id'], "Stylist-SENIOR", "2", 17, 3, 0, 1, 2, 4);
TestCheck("true", $result, "Stylist-SENIOR - AddRankRule RANKSUM_LVL1");

$result = AddRankRule($system_comm['id'], "Stylist-SENIOR", "2", 2, 2500, 0, 2, 0, 0);
TestCheck("true", $result, "Stylist-SENIOR - AddRankRule EV");

//////////////////////
// Stylist-DIRECTOR //
//////////////////////
$result = AddRankRule($system_comm['id'], "Stylist-DIRECTOR", "2", 12, 3000, 0, 3, 0, 0);
TestCheck("true", $result, "Stylist-DIRECTOR - AddRankRule PV");

$result = AddRankRule($system_comm['id'], "Stylist-DIRECTOR", "2", 17, 6, 0, 3, 1, 4);
TestCheck("true", $result, "Stylist-DIRECTOR - AddRankRule RANKSUM_LVL1");

$result = AddRankRule($system_comm['id'], "Stylist-DIRECTOR", "2", 16, 2, 0, 3, 2, 4);
TestCheck("true", $result, "Stylist-DIRECTOR - AddRankRule RANKSUMLEG");

$result = AddRankRule($system_comm['id'], "Stylist-DIRECTOR", "2", 2, 7500, 0, 3, 0, 0);
TestCheck("true", $result, "Stylist-DIRECTOR - AddRankRule EV");

////////////////////
// Stylist-LEADER //
////////////////////
$result = AddRankRule($system_comm['id'], "Stylist-LEADER", "2", 12, 5000, 0, 4, 0, 0);
TestCheck("true", $result, "Stylist-LEADER - AddRankRule PV");

$result = AddRankRule($system_comm['id'], "Stylist-LEADER", "2", 17, 15, 0, 4, 1, 4);
TestCheck("true", $result, "Stylist-LEADER - AddRankRule RANKSUM_LVL1");

$result = AddRankRule($system_comm['id'], "Stylist-LEADER", "2", 16, 5, 0, 4, 2, 4);
TestCheck("true", $result, "Stylist-LEADER - AddRankRule RANKSUMLEG #1");

$result = AddRankRule($system_comm['id'], "Stylist-LEADER", "2", 16, 2, 0, 4, 3, 4);
TestCheck("true", $result, "Stylist-LEADER - AddRankRule RANKSUMLEG #2");

$result = AddRankRule($system_comm['id'], "Stylist-LEADER", "2", 2, 15000, 0, 4, 0, 0);
TestCheck("true", $result, "Stylist-LEADER - AddRankRule EV");

////////////////////////
// Payout for Stylist //
////////////////////////
$result = AddCommRule($system_comm['id'], "1", "0", "false", "25", "4", "2", "1"); // Sold on Corporate //
TestCheck("true", $result, "Stylist - AddCommRule - Sold on Corporate");
$result = AddCommRule($system_comm['id'], "1", "0", "false", "50", "3", "2", "1"); // Cash and Carry //
TestCheck("true", $result, "Stylist - AddCommRule - Cash and Carry");
$result = AddBasicCommRule($system_comm['id'], "0", "12", 5000, 999999999, 2, 2, 1, "", 0, 0, 1, 1); // Personal Volume Bonus //
TestCheck("true", $result, "Stylist - AddBasicCommRule - Personal Volume Bonus");
$result = AddCommRule($system_comm['id'], "1", "1", "false", "2", "2", "2", "1"); // Team Volume Bonus Level 1 //
TestCheck("true", $result, "Stylist - AddCommRule - Team Volume Bonus Level 1");

///////////////////////////////
// Payout for Stylist-SENIOR //
///////////////////////////////
$result = AddCommRule($system_comm['id'], "2", "0", "false", "25", "4", "2", "1"); // Sold on Corporate //
TestCheck("true", $result, "Stylist-SENIOR - AddCommRule - Sold on Corporate");
$result = AddCommRule($system_comm['id'], "2", "0", "false", "50", "3", "2", "1"); // Cash and Carry //
TestCheck("true", $result, "Stylist-SENIOR - AddCommRule - Cash and Carry");
$result = AddBasicCommRule($system_comm['id'], "0", "12", 5000, 999999999, 2, 2, 2, "", 0, 0, 1, 2);
TestCheck("true", $result, "Stylist-SENIOR - AddBasicCommRule - Personal Volume Bonus");
$result = AddCommRule($system_comm['id'], "2", "1", "false", "3", "2", "2", "1");   // Team Volume Bonus Level 1 //
TestCheck("true", $result, "Stylist-SENIOR - AddCommRule - Team Volume Bonus Level 1");
$result = AddCommRule($system_comm['id'], "2", "2", "false", "1.5", "2", "2", "1"); // Team Volume Bonus Level 2 //
TestCheck("true", $result, "Stylist-SENIOR - AddCommRule - Team Volume Bonus Level 2");

/////////////////////////////////
// Payout for Stylist-DIRECTOR //
/////////////////////////////////
$result = AddCommRule($system_comm['id'], "3", "0", "false", "25", "4", "2", "1"); // Sold on Corporate //
TestCheck("true", $result, "Stylist-DIRECTOR - AddCommRule - Sold on Corporate");
$result = AddCommRule($system_comm['id'], "3", "0", "false", "50", "3", "2", "1"); // Cash and Carry //
TestCheck("true", $result, "Stylist-DIRECTOR - AddCommRule - Cash and Carry");
$result = AddBasicCommRule($system_comm['id'], "0", "12", 5000, 999999999, 2, 2, 3, "", 0, 0, 1, 3);
TestCheck("true", $result, "Stylist-DIRECTOR - AddBasicCommRule - Personal Volume Bonus");
$result = AddCommRule($system_comm['id'], "3", "1", "false", "4", "2", "2", "1"); // Team Volume Bonus Level 1 //
TestCheck("true", $result, "Stylist-DIRECTOR - AddCommRule - Team Volume Bonus Level 1");
$result = AddCommRule($system_comm['id'], "3", "2", "false", "2", "2", "2", "1"); // Team Volume Bonus Level 2 //
TestCheck("true", $result, "Stylist-DIRECTOR - AddCommRule - Team Volume Bonus Level 2");
$result = AddCommRule($system_comm['id'], "3", "3", "false", "1", "2", "2", "1"); // Team Volume Bonus Level 3 //
TestCheck("true", $result, "Stylist-DIRECTOR - AddCommRule - Team Volume Bonus Level 3");

///////////////////////////////
// Payout for Stylist-LEADER //
///////////////////////////////
$result = AddCommRule($system_comm['id'], "4", "0", "false", "25", "4", "2", "1"); // Sold on Corporate //
TestCheck("true", $result, "Stylist-LEADER - AddCommRule - Sold on Corporate");
$result = AddCommRule($system_comm['id'], "4", "0", "false", "50", "3", "2", "1"); // Cash and Carry //
TestCheck("true", $result, "Stylist-LEADER - AddCommRule - Cash and Carry");
$result = AddBasicCommRule($system_comm['id'], "0", "12", 5000, 999999999, 2, 2, 4, "", 0, 0, 1, 4);
TestCheck("true", $result, "Stylist-LEADER - AddBasicCommRule - Personal Volume Bonus");
$result = AddCommRule($system_comm['id'], "4", "1", "false", "5", "2", "2", "1");    // Team Volume Bonus Level 1 //
TestCheck("true", $result, "Stylist-LEADER - AddCommRule - Team Volume Bonus Level 1");
$result = AddCommRule($system_comm['id'], "4", "2", "false", "2.5", "2", "2", "1");  // Team Volume Bonus Level 2 //
TestCheck("true", $result, "Stylist-LEADER - AddCommRule - Team Volume Bonus Level 2");
$result = AddCommRule($system_comm['id'], "4", "3", "false", "1.25", "2", "2", "1"); // Team Volume Bonus Level 3 //
TestCheck("true", $result, "Stylist-LEADER - AddCommRule - Team Volume Bonus Level 3");

//////////////////////
// ADVOCATE PROGRAM //
//////////////////////
// Add an advocate system //
$system_advocate = AddSystem("Advocate Program", $commtype, $payouttype, $payoutmonthday, $minpay, $signupbonus, $teamgenmax, $system_comm['id'], $psqlimit, $compression);
TestCheck("true", $system_advocate, "TZIN - AddSystem #2");

// Add basic commission rules //
$result = AddBasicCommRule($system_advocate['id'], "0", "26", 1,   100, 	  2, 2, 10, "", 0, 0, 1);
TestCheck("true", $result, "Advocate - AddBasicCommRule #1");
$result = AddBasicCommRule($system_advocate['id'], "0", "26", 101, 300, 	  2, 2, 13, "", 0, 0, 1);
TestCheck("true", $result, "Advocate - AddBasicCommRule #2");
$result = AddBasicCommRule($system_advocate['id'], "0", "26", 301, 600,       2, 2, 16, "", 0, 0, 1);
TestCheck("true", $result, "Advocate - AddBasicCommRule #3");
$result = AddBasicCommRule($system_advocate['id'], "0", "26", 601, 999999999, 2, 2, 16, "", 0, 0, 1);
TestCheck("true", $result, "Advocate - AddBasicCommRule #4");
