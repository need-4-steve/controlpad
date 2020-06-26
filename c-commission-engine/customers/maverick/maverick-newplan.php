#!/usr/bin/php
<?php

$_SERVER["SCRIPT_NAME"] = "/maverick-live/newplan.php";

include "../../tests/includes/inc.ce-comm.php";
include "../../tests/includes/inc.systemusers.php";
include "../../tests/includes/inc.systems.php";
include "../../tests/includes/inc.rankrules.php";
include "../../tests/includes/inc.commrules.php";
include "../../tests/includes/inc.basiccommrules.php";
include "../../tests/includes/inc.faststart.php";
include "../../tests/includes/inc.tests.php";

$starttime = time();

// Main Account //
$sysmain_email = "master@commissions.com";
$sysmain_pass = "my.co#5YvhgW34&&.gf:gf*()23oties.com";

// Generate Random Email //
$sysuser_email = "wanderson@controlpad.com";
$sysuser_password = "easypass";
$_SESSION['authemail'] = $sysuser_email;
$_SESSION['authpass'] = $sysuser_password;

/*
// Add a system User //
echo "[ Maverick - AddSystemUser:: ]\n";
$result = AddSystemUser($sysuser_email);
TestCheck("true", $result, "Maverick - AddSystemUser");
//Pre($result);

// Add a system //
echo "[ Maverick - AddSystem:: ]\n";
$commtype = 1;
$payouttype = 1;
$payoutmonthday = 15;
$minpay = 5;
$signupbonus = 0;
$teamgenmax = 3;
$piggyid = 0;
$psqlimit = 0; // They must have 350 in personal sales to even qualify //
$compression = "true";
$system_comm = AddSystem("Commissions", $commtype, $payouttype, $payoutmonthday, $minpay, $signupbonus, $teamgenmax, $piggyid, $psqlimit, $compression);
TestCheck("true", $system_comm, "WestPlan #1 - AddSystem");
//Pre($system);

$system["id"] = 1; // 
 
$invtype = "1"; // Wholesale //
$event = "1"; // On Retail Date //
$paytype = "1";
$max_rank = "2";
*/
// 25% off retail //

// Personal Sales Volume Bonus //
// $2,400 = $100
// $4,800 = $250
// $7,200 = $400
// $9,600 = $600
// $19,200 = $1300


/*
    Personal retail sales
    Team retail sales 
    Personal volume of Wholesale purchase 
    Team volume of Wholesale purchases
    Team Volume of Wholesale purchases by level up to 5 levels
    # of wholesale items purchased by rep 
    # of wholesale items purchased by team 
    Affiliate Sales
*/

//AddRankRule($systemid, $label, $rank, $qualifytype, $threshold, $achvbonus, $rulegroup, $sumrankstart, $sumrankend)

//#define QLFY_ITEMCOUNTRETAIL_PV	    25 // Piphany - Retail //
//#define QLFY_PSQ						21 // Number of personally sponsored qualified //
//#define QLFY_RANKSUMLEG				16 // Sum of all //
//#define QLFY_ITEMCOUNTWHOLESALE_PV      24 // Piphany - PV Wholesale //
//#define QLFY_ITEMCOUNTWHOLESALE_EV      28 // Maverick - EV Itemcount Wholesale Enterprise Volume//

$system_comm['id'] = 1;

/////////////
// Stylist //
/////////////
$result = AddRankRule($system_comm['id'], "Stylist", "1", 24, 20, 0, 1, 0, 0);
TestCheck("true", $result, "Stylist - AddRankRule ITEMCOUNT");

//////////////
// Designer //
//////////////
$result = AddRankRule($system_comm['id'], "Designer", "2", 24, 35, 0, 2, 0, 0);
TestCheck("true", $result, "Designer - AddRankRule ITEMCOUNT");
$result = AddRankRule($system_comm['id'], "Designer", "2", 21, 2, 0, 2, 0, 0);
TestCheck("true", $result, "Designer - AddRankRule QLFY_PSQ");
$result = AddRankRule($system_comm['id'], "Designer", "2", 28, 200, 0, 2, 0, 0);
TestCheck("true", $result, "Designer - AddRankRule QLFY_ITEMCOUNTWHOLESALE_EV");

/////////////////
// Trendsetter //
/////////////////
$result = AddRankRule($system_comm['id'], "Trendsetter", "3", 24, 50, 0, 3, 0, 0);
TestCheck("true", $result, "Trendsetter - AddRankRule ITEMCOUNT");

$result = AddRankRule($system_comm['id'], "Trendsetter", "3", 21, 4, 0, 3, 0, 0);
TestCheck("true", $result, "Trendsetter - AddRankRule QLFY_PSQ");

$result = AddRankRule($system_comm['id'], "Trendsetter", "3", 16, 2, 0, 3, 1, 5);
TestCheck("true", $result, "Trendsetter - AddRankRule QLFY_RANKSUMLEG - Designer");

$result = AddRankRule($system_comm['id'], "Trendsetter", "2", 28, 650, 0, 3, 0, 0);
TestCheck("true", $result, "Trendsetter - AddRankRule QLFY_ITEMCOUNTWHOLESALE_EV");

///////////////
// Innovator //
///////////////
$result = AddRankRule($system_comm['id'], "Innovator", "4", 24, 100, 0, 4, 0, 0);
TestCheck("true", $result, "Innovator - AddRankRule ITEMCOUNT");

$result = AddRankRule($system_comm['id'], "Innovator", "4", 21, 6, 0, 4, 0, 0);
TestCheck("true", $result, "Innovator - AddRankRule QLFY_PSQ");

$result = AddRankRule($system_comm['id'], "Innovator", "4", 16, 2, 0, 4, 1, 5);
TestCheck("true", $result, "Innovator - AddRankRule QLFY_RANKSUMLEG - Designer");

$result = AddRankRule($system_comm['id'], "Innovator", "4", 16, 2, 0, 4, 2, 5);
TestCheck("true", $result, "Innovator - AddRankRule QLFY_RANKSUMLEG - Trendsetter");

$result = AddRankRule($system_comm['id'], "Innovator", "2", 28, 1500, 0, 4, 0, 0);
TestCheck("true", $result, "Innovator - AddRankRule QLFY_ITEMCOUNTWHOLESALE_EV");

////////////////
// Influencer //
////////////////
$result = AddRankRule($system_comm['id'], "Influencer", "5", 24, 150, 0, 5, 0, 0);
TestCheck("true", $result, "Influencer - AddRankRule ITEMCOUNT");

$result = AddRankRule($system_comm['id'], "Influencer", "5", 21, 8, 0, 5, 0, 0);
TestCheck("true", $result, "Influencer - AddRankRule QLFY_PSQ");

$result = AddRankRule($system_comm['id'], "Influencer", "5", 16, 2, 0, 5, 1, 5);
TestCheck("true", $result, "Influencer - AddRankRule QLFY_RANKSUMLEG - Designer");

$result = AddRankRule($system_comm['id'], "Influencer", "5", 16, 2, 0, 5, 2, 5);
TestCheck("true", $result, "Influencer - AddRankRule QLFY_RANKSUMLEG - Trendsetter");

$result = AddRankRule($system_comm['id'], "Influencer", "5", 16, 2, 0, 5, 3, 5);
TestCheck("true", $result, "Influencer - AddRankRule QLFY_RANKSUMLEG - Influencer");
 
$result = AddRankRule($system_comm['id'], "Influencer", "2", 28, 4000, 0, 5, 0, 0);
TestCheck("true", $result, "Influencer - AddRankRule QLFY_ITEMCOUNTWHOLESALE_EV");

//Personal qualifying pieces
//Rank Requirements

////////////////
// Comm Rules //
////////////////
//AddCommRule($systemid, $rank, $generation, $infinitybonus, $percent, $invtype, $event, $paytype)

/////////////
// Stylist //
/////////////
$result = AddCommRule($system_comm['id'], "1", "1", "false", "5", "1", "2", "1"); // Gen 1 //
TestCheck("true", $result, "Stylist - AddCommRule Gen 1");

//////////////
// Designer //
//////////////
$result = AddCommRule($system_comm['id'], "2", "1", "false", "5", "1", "2", "1"); // Gen 1 //
TestCheck("true", $result, "Designer - AddCommRule Gen 1");
$result = AddCommRule($system_comm['id'], "2", "2", "false", "4", "1", "2", "1"); // Gen 2 //
TestCheck("true", $result, "Designer - AddCommRule Gen 2");

/////////////////
// Trendsetter //
/////////////////
$result = AddCommRule($system_comm['id'], "3", "1", "false", "5", "1", "2", "1"); // Gen 1 //
TestCheck("true", $result, "Trendsetter - AddCommRule Gen 1");
$result = AddCommRule($system_comm['id'], "3", "2", "false", "4", "1", "2", "1"); // Gen 2 //
TestCheck("true", $result, "Trendsetter - AddCommRule Gen 2");
$result = AddCommRule($system_comm['id'], "3", "3", "false", "3", "1", "2", "1"); // Gen 3 //
TestCheck("true", $result, "Trendsetter - AddCommRule Gen 3");

///////////////
// Innovator //
///////////////
$result = AddCommRule($system_comm['id'], "4", "1", "false", "5", "1", "2", "1"); // Gen 1 //
TestCheck("true", $result, "Innovator - AddCommRule Gen 1");
$result = AddCommRule($system_comm['id'], "4", "2", "false", "4", "1", "2", "1"); // Gen 2 //
TestCheck("true", $result, "Innovator - AddCommRule Gen 2");
$result = AddCommRule($system_comm['id'], "4", "3", "false", "3", "1", "2", "1"); // Gen 3 //
TestCheck("true", $result, "Innovator - AddCommRule Gen 3");
$result = AddCommRule($system_comm['id'], "4", "4", "false", "2", "1", "2", "1"); // Gen 4 //
TestCheck("true", $result, "Innovator - AddCommRule Gen 4");

////////////////
// Influencer //
////////////////
$result = AddCommRule($system_comm['id'], "5", "1", "false", "5", "1", "2", "1"); // Gen 1 //
TestCheck("true", $result, "Influencer - AddCommRule Gen 1");
$result = AddCommRule($system_comm['id'], "5", "2", "false", "4", "1", "2", "1"); // Gen 2 //
TestCheck("true", $result, "Influencer - AddCommRule Gen 2");
$result = AddCommRule($system_comm['id'], "5", "3", "false", "3", "1", "2", "1"); // Gen 3 //
TestCheck("true", $result, "Influencer - AddCommRule Gen 3");
$result = AddCommRule($system_comm['id'], "5", "4", "false", "2", "1", "2", "1"); // Gen 4 //
TestCheck("true", $result, "Influencer - AddCommRule Gen 4");
$result = AddCommRule($system_comm['id'], "5", "5", "false", "1", "1", "2", "1"); // Gen 5 //
TestCheck("true", $result, "Influencer - AddCommRule Gen 5");


////////////////////////////
// Fast Start Bonus Rules //
////////////////////////////
$qualifytype = 30;
$days_count = 30;
$rank = "-1"; // -1 affects all ranks //
$result = AddFastStartBonus($system_comm['id'], $rank, $qualifytype, 2400, $days_count, 100, 1);
TestCheck("true", $result, "FastStartBonus - 2400 = 100");
$result = AddFastStartBonus($system_comm['id'], $rank, $qualifytype, 4800, $days_count, 250, 1);
TestCheck("true", $result, "FastStartBonus - 4800 = 250");
$result = AddFastStartBonus($system_comm['id'], $rank, $qualifytype, 7200, $days_count, 400, 1);
TestCheck("true", $result, "FastStartBonus - 7200 = 400");
$result = AddFastStartBonus($system_comm['id'], $rank, $qualifytype, 9600, $days_count, 600, 1);
TestCheck("true", $result, "FastStartBonus - 9600 = 600");
$result = AddFastStartBonus($system_comm['id'], $rank, $qualifytype, 19200, $days_count, 1300, 1);
TestCheck("true", $result, "FastStartBonus - 19200 = 1300");