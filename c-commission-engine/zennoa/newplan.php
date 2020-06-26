#!/usr/bin/php
<?php

$_SERVER["SCRIPT_NAME"] = "/zennoa-live/newplan.php";

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
$sysuser_email = "wanderson@controlpad.com";
$sysuser_password = "easyplan";
$_SESSION['authemail'] = $sysuser_email;
$_SESSION['authpass'] = $sysuser_password;

// Add a system User //
echo "[ Zennoa - AddSystemUser:: ]\n";
$result = AddSystemUser($sysuser_email);
TestCheck("true", $result, "Zennoa - AddSystemUser");

/////////////////////////////////////////////
// 2 - Retail Sales & Customer Commissions //
/////////////////////////////////////////////
// Add a system //
echo "[ Zennoa - Retail Sales/Comm:: ]\n";
$commtype = 1;
$payouttype = 1;
$payoutmonthday = 15;
$minpay = 5;
$signupbonus = 0;
$teamgenmax = 3;
$piggyid = 0;
$psqlimit = 0;
$compression = "true";
$system = AddSystem("2-Retail Sales/Comm", $commtype, $payouttype, $payoutmonthday, $minpay, $signupbonus, $teamgenmax, $piggyid, $psqlimit, $compression);
TestCheck("true", $system, "Zennoa - AddSystem (Retail Sales/Comm)");

// Wave 0 - 1st //
$generation = 0;
$qualifytype = 12; // Personal Volume //
$invtype = 1; // Wholesale INV = 1 //
$event = 1; // Wholesale EVENT = 1 //
$percent = 10;
$paytype = 1; // Wholesale = 1, Retail = 2 //
$result = AddBasicCommRule($system['id'], $generation, $qualifytype, 151, 300, $invtype, $event,  $percent, "", 0, "", $paytype); //, $rank['Wave-0: 175PV']);
TestCheck("true", $result, "Wave 0 - 1st - AddBasicCommRule #1");

// Wave 0 - 2nd //
$generation = 0;
$qualifytype = 12; // Personal Volume //
$invtype = 1; // Wholesale INV = 1 //
$event = 1; // Wholesale EVENT = 1 //
$percent = 15;
$paytype = 1; // Wholesale = 1, Retail = 2 //
$result = AddBasicCommRule($system['id'], $generation, $qualifytype, 301, 500, $invtype, $event,  $percent, "", 0, "", $paytype); //, $rank['Wave-0: 175PV']);
TestCheck("true", $result, "Wave 0 - 2nd - AddBasicCommRule #1");

// Wave 0 - 3rd //
$generation = 0;
$qualifytype = 12; // Personal Volume //
$invtype = 1; // Wholesale INV = 1 //
$event = 1; // Wholesale EVENT = 1 //
$percent = 20;
$paytype = 1; // Wholesale = 1, Retail = 2 //
$result = AddBasicCommRule($system['id'], $generation, $qualifytype, 501, 1000, $invtype, $event,  $percent, "", 0, "", $paytype); //, $rank['Wave-0: 175PV']);
TestCheck("true", $result, "Wave 0 - 3rd - AddBasicCommRule #1");

// Wave 0 - 4th //
$generation = 0;
$qualifytype = 12; // Personal Volume //
$invtype = 1; // Wholesale INV = 1 //
$event = 1; // Wholesale EVENT = 1 //
$percent = 25;
$paytype = 1; // Wholesale = 1, Retail = 2 //
$result = AddBasicCommRule($system['id'], $generation, $qualifytype, 1001, 999999999, $invtype, $event,  $percent, "", 0, "", $paytype); //, $rank['Wave-0: 175PV']);
TestCheck("true", $result, "Wave 0 - 4th - AddBasicCommRule #1");


// Wave 1 - 1st //
$generation = 1;
$qualifytype = 12; // Personal Volume //
$invtype = 1; // Wholesale INV = 1 //
$event = 1; // Wholesale EVENT = 1 //
$percent = 5;
$paytype = 1; // Wholesale = 1, Retail = 2 //
$result = AddBasicCommRule($system['id'], $generation, $qualifytype, 151, 300, $invtype, $event,  $percent, "", 0, "", $paytype); //, $rank['Wave-0: 175PV']);
TestCheck("true", $result, "Wave 1 - 1st - AddBasicCommRule #1");

// Wave 1 - 2nd //
$generation = 1;
$qualifytype = 12; // Personal Volume //
$invtype = 1; // Wholesale INV = 1 //
$event = 1; // Wholesale EVENT = 1 //
$percent = 4;
$paytype = 1; // Wholesale = 1, Retail = 2 //
$result = AddBasicCommRule($system['id'], $generation, $qualifytype, 301, 500, $invtype, $event,  $percent, "", 0, "", $paytype); //, $rank['Wave-0: 175PV']);
TestCheck("true", $result, "Wave 1 - 2nd - AddBasicCommRule #1");

// Wave 1 - 3rd //
$generation = 1;
$qualifytype = 12; // Personal Volume //
$invtype = 1; // Wholesale INV = 1 //
$event = 1; // Wholesale EVENT = 1 //
$percent = 3;
$paytype = 1; // Wholesale = 1, Retail = 2 //
$result = AddBasicCommRule($system['id'], $generation, $qualifytype, 501, 1000, $invtype, $event,  $percent, "", 0, "", $paytype); //, $rank['Wave-0: 175PV']);
TestCheck("true", $result, "Wave 1 - 3rd - AddBasicCommRule #1");

// Wave 1 - 4th //
$generation = 1;
$qualifytype = 12; // Personal Volume //
$invtype = 1; // Wholesale INV = 1 //
$event = 1; // Wholesale EVENT = 1 //
$percent = 2;
$paytype = 1; // Wholesale = 1, Retail = 2 //
$result = AddBasicCommRule($system['id'], $generation, $qualifytype, 1001, 999999999, $invtype, $event,  $percent, "", 0, "", $paytype); //, $rank['Wave-0: 175PV']);
TestCheck("true", $result, "Wave 1 - 4th - AddBasicCommRule #1");

// Wave 2 - 1st //
$generation = 2;
$qualifytype = 12; // Personal Volume //
$invtype = 1; // Wholesale INV = 1 //
$event = 1; // Wholesale EVENT = 1 //
$percent = 5;
$paytype = 1; // Wholesale = 1, Retail = 2 //
$result = AddBasicCommRule($system['id'], $generation, $qualifytype, 151, 300, $invtype, $event,  $percent, "", 0, "", $paytype); //, $rank['Wave-0: 175PV']);
TestCheck("true", $result, "Wave 2 - 1st - AddBasicCommRule #1");

// Wave 2 - 2nd //
$generation = 2;
$qualifytype = 12; // Personal Volume //
$invtype = 1; // Wholesale INV = 1 //
$event = 1; // Wholesale EVENT = 1 //
$percent = 4;
$paytype = 1; // Wholesale = 1, Retail = 2 //
$result = AddBasicCommRule($system['id'], $generation, $qualifytype, 301, 500, $invtype, $event,  $percent, "", 0, "", $paytype); //, $rank['Wave-0: 175PV']);
TestCheck("true", $result, "Wave 2 - 2nd - AddBasicCommRule #1");

// Wave 2 - 3rd //
$generation = 2;
$qualifytype = 12; // Personal Volume //
$invtype = 1; // Wholesale INV = 1 //
$event = 1; // Wholesale EVENT = 1 //
$percent = 3;
$paytype = 1; // Wholesale = 1, Retail = 2 //
$result = AddBasicCommRule($system['id'], $generation, $qualifytype, 501, 1000, $invtype, $event,  $percent, "", 0, "", $paytype); //, $rank['Wave-0: 175PV']);
TestCheck("true", $result, "Wave 2 - 3rd - AddBasicCommRule #1");

// Wave 2 - 4th //
$generation = 2;
$qualifytype = 12; // Personal Volume //
$invtype = 1; // Wholesale INV = 1 //
$event = 1; // Wholesale EVENT = 1 //
$percent = 2;
$paytype = 1; // Wholesale = 1, Retail = 2 //
$result = AddBasicCommRule($system['id'], $generation, $qualifytype, 1001, 999999999, $invtype, $event,  $percent, "", 0, "", $paytype); //, $rank['Wave-0: 175PV']);
TestCheck("true", $result, "Wave 2 - 4th - AddBasicCommRule #1");

// Wave 3 - 1st //
$generation = 3;
$qualifytype = 12; // Personal Volume //
$invtype = 1; // Wholesale INV = 1 //
$event = 1; // Wholesale EVENT = 1 //
$percent = 5;
$paytype = 1; // Wholesale = 1, Retail = 2 //
$result = AddBasicCommRule($system['id'], $generation, $qualifytype, 151, 300, $invtype, $event,  $percent, "", 0, "", $paytype); //, $rank['Wave-0: 175PV']);
TestCheck("true", $result, "Wave 3 - 1st - AddBasicCommRule #1");

// Wave 3 - 2nd //
$generation = 3;
$qualifytype = 12; // Personal Volume //
$invtype = 1; // Wholesale INV = 1 //
$event = 1; // Wholesale EVENT = 1 //
$percent = 4;
$paytype = 1; // Wholesale = 1, Retail = 2 //
$result = AddBasicCommRule($system['id'], $generation, $qualifytype, 301, 500, $invtype, $event,  $percent, "", 0, "", $paytype); //, $rank['Wave-0: 175PV']);
TestCheck("true", $result, "Wave 3 - 2nd - AddBasicCommRule #1");

// Wave 3 - 3rd //
$generation = 3;
$qualifytype = 12; // Personal Volume //
$invtype = 1; // Wholesale INV = 1 //
$event = 1; // Wholesale EVENT = 1 //
$percent = 3;
$paytype = 1; // Wholesale = 1, Retail = 2 //
$result = AddBasicCommRule($system['id'], $generation, $qualifytype, 501, 1000, $invtype, $event,  $percent, "", 0, "", $paytype); //, $rank['Wave-0: 175PV']);
TestCheck("true", $result, "Wave 3 - 3rd - AddBasicCommRule #1");

// Wave 3 - 4th //
$generation = 3;
$qualifytype = 12; // Personal Volume //
$invtype = 1; // Wholesale INV = 1 //
$event = 1; // Wholesale EVENT = 1 //
$percent = 2;
$paytype = 1; // Wholesale = 1, Retail = 2 //
$result = AddBasicCommRule($system['id'], $generation, $qualifytype, 1001, 999999999, $invtype, $event,  $percent, "", 0, "", $paytype); //, $rank['Wave-0: 175PV']);
TestCheck("true", $result, "Wave 3 - 4th - AddBasicCommRule #1");

// Wave 4 - 1st //
$generation = 4;
$qualifytype = 12; // Personal Volume //
$invtype = 1; // Wholesale INV = 1 //
$event = 1; // Wholesale EVENT = 1 //
$percent = 5;
$paytype = 1; // Wholesale = 1, Retail = 2 //
$result = AddBasicCommRule($system['id'], $generation, $qualifytype, 151, 300, $invtype, $event,  $percent, "", 0, "", $paytype); //, $rank['Wave-0: 175PV']);
TestCheck("true", $result, "Wave 4 - 1st - AddBasicCommRule #1");

// Wave 4 - 2nd //
$generation = 4;
$qualifytype = 12; // Personal Volume //
$invtype = 1; // Wholesale INV = 1 //
$event = 1; // Wholesale EVENT = 1 //
$percent = 4;
$paytype = 1; // Wholesale = 1, Retail = 2 //
$result = AddBasicCommRule($system['id'], $generation, $qualifytype, 301, 500, $invtype, $event,  $percent, "", 0, "", $paytype); //, $rank['Wave-0: 175PV']);
TestCheck("true", $result, "Wave 4 - 2nd - AddBasicCommRule #1");

// Wave 4 - 3rd //
$generation = 4;
$qualifytype = 12; // Personal Volume //
$invtype = 1; // Wholesale INV = 1 //
$event = 1; // Wholesale EVENT = 1 //
$percent = 3;
$paytype = 1; // Wholesale = 1, Retail = 2 //
$result = AddBasicCommRule($system['id'], $generation, $qualifytype, 501, 1000, $invtype, $event,  $percent, "", 0, "", $paytype); //, $rank['Wave-0: 175PV']);
TestCheck("true", $result, "Wave 4 - 3rd - AddBasicCommRule #1");

// Wave 4 - 4th //
$generation = 4;
$qualifytype = 12; // Personal Volume //
$invtype = 1; // Wholesale INV = 1 //
$event = 1; // Wholesale EVENT = 1 //
$percent = 2;
$paytype = 1; // Wholesale = 1, Retail = 2 //
$result = AddBasicCommRule($system['id'], $generation, $qualifytype, 1001, 999999999, $invtype, $event,  $percent, "", 0, "", $paytype); //, $rank['Wave-0: 175PV']);
TestCheck("true", $result, "Wave 4 - 4th - AddBasicCommRule #1");

// Wave 5 - 1st //
$generation = 5;
$qualifytype = 12; // Personal Volume //
$invtype = 1; // Wholesale INV = 1 //
$event = 1; // Wholesale EVENT = 1 //
$percent = 5;
$paytype = 1; // Wholesale = 1, Retail = 2 //
$result = AddBasicCommRule($system['id'], $generation, $qualifytype, 151, 300, $invtype, $event,  $percent, "", 0, "", $paytype); //, $rank['Wave-0: 175PV']);
TestCheck("true", $result, "Wave 5 - 1st - AddBasicCommRule #1");

// Wave 5 - 2nd //
$generation = 5;
$qualifytype = 12; // Personal Volume //
$invtype = 1; // Wholesale INV = 1 //
$event = 1; // Wholesale EVENT = 1 //
$percent = 4;
$paytype = 1; // Wholesale = 1, Retail = 2 //
$result = AddBasicCommRule($system['id'], $generation, $qualifytype, 301, 500, $invtype, $event,  $percent, "", 0, "", $paytype); //, $rank['Wave-0: 175PV']);
TestCheck("true", $result, "Wave 5 - 2nd - AddBasicCommRule #1");

// Wave 5 - 3rd //
$generation = 5;
$qualifytype = 12; // Personal Volume //
$invtype = 1; // Wholesale INV = 1 //
$event = 1; // Wholesale EVENT = 1 //
$percent = 3;
$paytype = 1; // Wholesale = 1, Retail = 2 //
$result = AddBasicCommRule($system['id'], $generation, $qualifytype, 501, 1000, $invtype, $event,  $percent, "", 0, "", $paytype); //, $rank['Wave-0: 175PV']);
TestCheck("true", $result, "Wave 5 - 3rd - AddBasicCommRule #1");

// Wave 5 - 4th //
$generation = 5;
$qualifytype = 12; // Personal Volume //
$invtype = 1; // Wholesale INV = 1 //
$event = 1; // Wholesale EVENT = 1 //
$percent = 2;
$paytype = 1; // Wholesale = 1, Retail = 2 //
$result = AddBasicCommRule($system['id'], $generation, $qualifytype, 1001, 999999999, $invtype, $event,  $percent, "", 0, "", $paytype); //, $rank['Wave-0: 175PV']);
TestCheck("true", $result, "Wave 5 - 4th - AddBasicCommRule #1");

///////////////////////////////
// 4 - Pipeline Sales Reward //
///////////////////////////////
// Add a system //
echo "[ Zennoa - AddSystem Pipline Sales:: ]\n";
$commtype = 1;
$payouttype = 1;
$payoutmonthday = 15;
$minpay = 5;
$signupbonus = 0;
$teamgenmax = 3;
$piggyid = 1;
$psqlimit = 0;
$compression = "true";
$system = AddSystem("4-Pipline Sales", $commtype, $payouttype, $payoutmonthday, $minpay, $signupbonus, $teamgenmax, $piggyid, $psqlimit, $compression);
TestCheck("true", $system, "Zennoa - AddSystem (Pipline Sales)");
//Pre($system);

//$system["id"] = 1; //
 
$invtype = "1"; // Wholesale //
$event = "1"; // On Retail Date //
$paytype = "1";
$max_rank = "16";

// Notes?
// We assume Pipine Volume is Enterprise Volume for #4? //
// Or is it a different Team Volume Depth Defined? //

//AddRankRule($systemid, $label, $rank, $qualifytype, $threshold, $achvbonus, $rulegroup, $sumrankstart, $sumrankend);

//////////////////
// Wave Trainer //
//////////////////
$rulegroup = 0;

// 5k in sales //
$result = AddRankRule($system['id'], "Wave Trainer", "1", 2, 5000, 25, $rulegroup, 0, 0);
TestCheck("true", $result, "Wave Trainer - AddRankRule #1");
// has 3 personal active or channels @ 75 PV //

// is personal active same as PSQ? Or at least 3 at Wave Trainer (1) //

////////////////////
// Wave Trainer 1 //
////////////////////
$rulegroup = 2;

// 10k sales //
$result = AddRankRule($system['id'], "Wave Trainer 1", "2", 2, 10000, 50, $rulegroup, 0, 0);
TestCheck("true", $result, "Wave Trainer 1 - AddRankRule #1");

// 1x wave trainer //
// Make sure this is full downline or PSQ //
//QLFY_RANKSUMLEG					16
//QLFY_RANKSUMLVL1				    17
$result = AddRankRule($system['id'], "Wave Trainer 1", "2", 16, 1, 50, $rulegroup, 1, 16);
TestCheck("true", $result, "Wave Trainer 1 - AddRankRule #2");

////////////////////
// Wave Trainer 2 //
////////////////////
$rulegroup = 3;

// 20k sales //
$result = AddRankRule($system['id'], "Wave Trainer 2", "3", 2, 20000, 100, $rulegroup, 0, 0);
TestCheck("true", $result, "Wave Trainer 2 - AddRankRule #1");

// 2x wave trainer //
$result = AddRankRule($system['id'], "Wave Trainer 2", "3", 16, 2, 100, $rulegroup, 1, 16);
TestCheck("true", $result, "Wave Trainer 2 - AddRankRule #2");

//////////////
// Wave Pro //
//////////////
$rulegroup = 4;

// 35k sales //
$result = AddRankRule($system['id'], "Wave Pro", "4", 2, 35000, 200, $rulegroup, 0, 0);
TestCheck("true", $result, "Wave Pro - AddRankRule #1");

// 3x wave trainer //
$result = AddRankRule($system['id'], "Wave Pro", "4", 16, 3, 200, $rulegroup, 1, 16);
TestCheck("true", $result, "Wave Pro - AddRankRule #2");

////////////////
// Wave Pro 1 //
////////////////
$rulegroup = 5;

// 55k sales //
$result = AddRankRule($system['id'], "Wave Pro 1", "5", 2, 55000, 300, $rulegroup, 0, 0);
TestCheck("true", $result, "Wave Pro 1 - AddRankRule #1");

// 1x wave pro (4) //
$result = AddRankRule($system['id'], "Wave Pro 1", "5", 16, 1, 300, $rulegroup, 4, 16);
TestCheck("true", $result, "Wave Pro 1 - AddRankRule #2");

////////////////
// Wave Pro 2 //
////////////////
$rulegroup = 6;

// 100k sales //
$result = AddRankRule($system['id'], "Wave Pro 2", "6", 2, 100000, 500, $rulegroup, 0, 0);
TestCheck("true", $result, "Wave Pro 2 - AddRankRule #1");

// 2x wave pro (4) //
$result = AddRankRule($system['id'], "Wave Pro 2", "6", 16, 2, 500, $rulegroup, 4, 16);
TestCheck("true", $result, "Wave Pro 2 - AddRankRule #2");

/////////////////////
// Pipeline Master //
/////////////////////
$rulegroup = 7;

// 150k sales //
$result = AddRankRule($system['id'], "Pipeline Master", "7", 2, 150000, 800, $rulegroup, 0, 0);
TestCheck("true", $result, "Pipeline Master - AddRankRule #1");

// 3x wave pro (4) //
$result = AddRankRule($system['id'], "Pipeline Master", "7", 16, 3, 800, $rulegroup, 4, 16);
TestCheck("true", $result, "Pipeline Master - AddRankRule #2");

///////////////////////
// Pipeline Master 1 //
///////////////////////
$rulegroup = 8;

// 200k sales //
$result = AddRankRule($system['id'], "Pipeline Master 1", "8", 2, 200000, 1100, $rulegroup, 0, 0);
TestCheck("true", $result, "Pipeline Master 1 - AddRankRule #1");

// 1x pipeline master (7) //
$result = AddRankRule($system['id'], "Pipeline Master 1", "8", 16, 1, 1100, $rulegroup, 7, 16);
TestCheck("true", $result, "Pipeline Master 1 - AddRankRule #2");

///////////////////////
// Pipeline Master 2 //
///////////////////////
$rulegroup = 9;

// 250k sales //
$result = AddRankRule($system['id'], "Pipeline Master 2", "9", 2, 250000, 1500, $rulegroup, 0, 0);
TestCheck("true", $result, "Pipeline Master 2 - AddRankRule #1");

// 2x pipeline master (7) //
$result = AddRankRule($system['id'], "Pipeline Master 2", "9", 16, 2, 1500, $rulegroup, 7, 16);
TestCheck("true", $result, "Pipeline Master 2 - AddRankRule #2");

/////////////////////
// Pipeline Legend //
/////////////////////
$rulegroup = 10;

// 325k sales //
$result = AddRankRule($system['id'], "Pipeline Legend", "10", 2, 325000, 2000, $rulegroup, 0, 0);
TestCheck("true", $result, "Pipeline Legend - AddRankRule #1");

// 3x pipeline master (7) //
$result = AddRankRule($system['id'], "Pipeline Legend", "10", 16, 3, 2000, $rulegroup, 7, 16);
TestCheck("true", $result, "Pipeline Legend - AddRankRule #2");

///////////////////////
// Pipeline Legend 1 //
///////////////////////
$rulegroup = 11;

// 400k sales //
$result = AddRankRule($system['id'], "Pipeline Legend 1", "11", 2, 400000, 2500, $rulegroup, 0, 0);
TestCheck("true", $result, "Pipeline Legend 1 - AddRankRule #1");

// 1x pipeline legend (10) //
$result = AddRankRule($system['id'], "Pipeline Legend 1", "11", 16, 1, 2500, $rulegroup, 10, 16);
TestCheck("true", $result, "Pipeline Legend 1 - AddRankRule #2");

///////////////////////
// Pipeline Legend 2 //
///////////////////////
$rulegroup = 12;

// 500k sales //
$result = AddRankRule($system['id'], "Pipeline Legend 2", "12", 2, 500000, 3100, $rulegroup, 0, 0);
TestCheck("true", $result, "Pipeline Legend 2 - AddRankRule #1");

// 2x pipeline legend (10) //
$result = AddRankRule($system['id'], "Pipeline Legend 2", "12", 16, 2, 3100, $rulegroup, 10, 16);
TestCheck("true", $result, "Pipeline Legend 2 - AddRankRule #2");

//////////////////////
// Boardroom Member //
//////////////////////
$rulegroup = 13;

// 600k sales //
$result = AddRankRule($system['id'], "Board Member", "13", 2, 600000, 4000, $rulegroup, 0, 0);
TestCheck("true", $result, "Board Member - AddRankRule #1");

// 3x pipeline legend (10) //
$result = AddRankRule($system['id'], "Board Member", "13", 16, 3, 4000, $rulegroup, 10, 16);
TestCheck("true", $result, "Board Member - AddRankRule #2");

////////////////////////
// Boardroom Member 1 //
////////////////////////
$rulegroup = 14;

// 600k sales //
$result = AddRankRule($system['id'], "Board Member 1", "14", 2, 725000, 5000, $rulegroup, 0, 0);
TestCheck("true", $result, "Board Member 1 - AddRankRule #1");

// 1x board member (13) //
$result = AddRankRule($system['id'], "Board Member 1", "14", 16, 1, 5000, $rulegroup, 13, 16);
TestCheck("true", $result, "Board Member 1 - AddRankRule #2");

////////////////////////
// Boardroom Member 2 //
////////////////////////
$rulegroup = 15;

// 600k sales //
$result = AddRankRule($system['id'], "Board Member 2", "15", 2, 850000, 7000, $rulegroup, 0, 0);
TestCheck("true", $result, "Board Member 2 - AddRankRule #1");

// 2x boardroom member (13) //
$result = AddRankRule($system['id'], "Board Member 2", "15", 16, 2, 7000, $rulegroup, 13, 16);
TestCheck("true", $result, "Board Member 2 - AddRankRule #2");

////////////////////////
// Boardroom Chairman //
////////////////////////
$rulegroup = 16;

// 600k sales //
$result = AddRankRule($system['id'], "Board Chairman", "16", 2, 1000000, 10000, $rulegroup, 0, 0);
TestCheck("true", $result, "Board Chairman - AddRankRule #1");

// 3x boardroom member (13) //
$result = AddRankRule($system['id'], "Board Chairman", "16", 16, 3, 10000, $rulegroup, 13, 16);
TestCheck("true", $result, "Board Chairman - AddRankRule #2");



///////////////////////////
// 5 - Team Sales Reward //
///////////////////////////
// Add a system //
echo "[ Zennoa - AddSystem Team Sales Rewards:: ]\n";
$commtype = 1;
$payouttype = 1;
$payoutmonthday = 15;
$minpay = 5;
$signupbonus = 0;
$teamgenmax = 3;
$piggyid = 1;
$psqlimit = 0;
$compression = "true";
$system = AddSystem("5-Team Sales Rewards", $commtype, $payouttype, $payoutmonthday, $minpay, $signupbonus, $teamgenmax, $piggyid, $psqlimit, $compression);
TestCheck("true", $system, "Zennoa - AddSystem (Team Sales Rewards)");
//Pre($system);

//$system["id"] = 2; //
 
$invtype = "1"; // Wholesale //
$event = "1"; // On Retail Date //
$paytype = "1";
$max_rank = "16";

//AddRankRule($systemid, $label, $rank, $qualifytype, $threshold, $achvbonus, $rulegroup, $sumrankstart, $sumrankend);
///////////
// Zen 1 //
///////////
$rulegroup = 1;
$result = AddRankRule($system['id'], "Zen 1", "1", 12, 150, 50, $rulegroup, 0, 0);
TestCheck("true", $result, "Zen 1 - AddRankRule #1");
$result = AddRankRule($system['id'], "Zen 1", "1", 2, 750, 50, $rulegroup, 0, 0);
TestCheck("true", $result, "Zen 1 - AddRankRule #2");

///////////
// Zen 2 //
///////////
$rulegroup = 2;
$result = AddRankRule($system['id'], "Zen 2", "2", 12, 150, 150, $rulegroup, 0, 0);
TestCheck("true", $result, "Zen 2 - AddRankRule #1");
$result = AddRankRule($system['id'], "Zen 2", "2", 2, 1500, 150, $rulegroup, 0, 0);
TestCheck("true", $result, "Zen 2 - AddRankRule #2");

///////////
// Zen 3 //
///////////
$rulegroup = 3;
$result = AddRankRule($system['id'], "Zen 3", "3", 12, 150, 350, $rulegroup, 0, 0);
TestCheck("true", $result, "Zen 3 - AddRankRule #1");
$result = AddRankRule($system['id'], "Zen 3", "3", 2, 3000, 350, $rulegroup, 0, 0);
TestCheck("true", $result, "Zen 3 - AddRankRule #2");

///////////
// Zen 4 //
///////////
$rulegroup = 4;
$result = AddRankRule($system['id'], "Zen 4", "4", 12, 150, 750, $rulegroup, 0, 0);
TestCheck("true", $result, "Zen 4 - AddRankRule #1");
$result = AddRankRule($system['id'], "Zen 4", "4", 2, 6000, 750, $rulegroup, 0, 0);
TestCheck("true", $result, "Zen 4 - AddRankRule #2");

///////////
// Zen 5 //
///////////
$rulegroup = 5;
$result = AddRankRule($system['id'], "Zen 5", "5", 12, 150, 1500, $rulegroup, 0, 0);
TestCheck("true", $result, "Zen 5 - AddRankRule #1");
$result = AddRankRule($system['id'], "Zen 5", "5", 2, 12000, 1500, $rulegroup, 0, 0);
TestCheck("true", $result, "Zen 5 - AddRankRule #2");

///////////
// Zen 6 //
///////////
$rulegroup = 6;
$result = AddRankRule($system['id'], "Zen 6", "6", 12, 150, 3000, $rulegroup, 0, 0);
TestCheck("true", $result, "Zen 6 - AddRankRule #1");
$result = AddRankRule($system['id'], "Zen 6", "6", 2, 25000, 3000, $rulegroup, 0, 0);
TestCheck("true", $result, "Zen 6 - AddRankRule #2");

///////////
// Zen 7 //
///////////
$rulegroup = 7;
$result = AddRankRule($system['id'], "Zen 7", "7", 12, 150, 5500, $rulegroup, 0, 0);
TestCheck("true", $result, "Zen 7 - AddRankRule #1");
$result = AddRankRule($system['id'], "Zen 7", "7", 2, 50000, 5500, $rulegroup, 0, 0);
TestCheck("true", $result, "Zen 7 - AddRankRule #2");

///////////
// Zen 8 //
///////////
$rulegroup = 8;
$result = AddRankRule($system['id'], "Zen 8", "8", 12, 150, 8500, $rulegroup, 0, 0);
TestCheck("true", $result, "Zen 8 - AddRankRule #1");
$result = AddRankRule($system['id'], "Zen 8", "8", 2, 100000, 8500, $rulegroup, 0, 0);
TestCheck("true", $result, "Zen 8 - AddRankRule #2");

///////////
// Zen 9 //
///////////
$rulegroup = 9;
$result = AddRankRule($system['id'], "Zen 9", "9", 12, 150, 12500, $rulegroup, 0, 0);
TestCheck("true", $result, "Zen 9 - AddRankRule #1");
$result = AddRankRule($system['id'], "Zen 9", "9", 2, 200000, 12500, $rulegroup, 0, 0);
TestCheck("true", $result, "Zen 9 - AddRankRule #2");

////////////
// Zen 10 //
////////////
$rulegroup = 10;
$result = AddRankRule($system['id'], "Zen 10", "10", 12, 150, 18000, $rulegroup, 0, 0);
TestCheck("true", $result, "Zen 10 - AddRankRule #1");
$result = AddRankRule($system['id'], "Zen 10", "10", 2, 350000, 18000, $rulegroup, 0, 0);
TestCheck("true", $result, "Zen 10 - AddRankRule #2");

////////////
// Zen 11 //
////////////
$rulegroup = 11;
$result = AddRankRule($system['id'], "Zen 11", "11", 12, 150, 24000, $rulegroup, 0, 0);
TestCheck("true", $result, "Zen 11 - AddRankRule #1");
$result = AddRankRule($system['id'], "Zen 11", "11", 2, 550000, 24000, $rulegroup, 0, 0);
TestCheck("true", $result, "Zen 11 - AddRankRule #2");

////////////
// Zen 12 //
////////////
$rulegroup = 12;
$result = AddRankRule($system['id'], "Zen 12", "12", 12, 150, 30000, $rulegroup, 0, 0);
TestCheck("true", $result, "Zen 12 - AddRankRule #1");
$result = AddRankRule($system['id'], "Zen 12", "12", 2, 800000, 30000, $rulegroup, 0, 0);
TestCheck("true", $result, "Zen 12 - AddRankRule #2");

////////////////
// Diamond 13 //
////////////////
$rulegroup = 13;
$result = AddRankRule($system['id'], "Diamond", "13", 12, 150, 40000, $rulegroup, 0, 0);
TestCheck("true", $result, "Diamond 13 - AddRankRule #1");
$result = AddRankRule($system['id'], "Diamond", "13", 2, 1200000, 40000, $rulegroup, 0, 0);
TestCheck("true", $result, "Diamond 13 - AddRankRule #2");

/////////////////////
// Blue Diamond 14 //
/////////////////////
$rulegroup = 14;
$result = AddRankRule($system['id'], "Blue Diamond", "14", 12, 150, 55000, $rulegroup, 0, 0);
TestCheck("true", $result, "Blue Diamond 14 - AddRankRule #1");
$result = AddRankRule($system['id'], "Blue Diamond", "14", 2, 2000000, 55000, $rulegroup, 0, 0);
TestCheck("true", $result, "Blue Diamond 14 - AddRankRule #2");

//////////////////////
// Crown Diamond 15 //
//////////////////////
$rulegroup = 15;
$result = AddRankRule($system['id'], "Crown Diamond", "15", 12, 150, 75000, $rulegroup, 0, 0);
TestCheck("true", $result, "Crown Diamond 15 - AddRankRule #1");
$result = AddRankRule($system['id'], "Crown Diamond", "15", 2, 3000000, 75000, $rulegroup, 0, 0);
TestCheck("true", $result, "Crown Diamond 15 - AddRankRule #2");

