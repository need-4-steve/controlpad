<?php

include '../tests/includes/inc.global.php';
include '../tests/includes/inc.systemusers.php';
include '../tests/includes/inc.systems.php';
include '../tests/includes/inc.rankrules.php';
include '../tests/includes/inc.commissionrules.php';
include '../tests/includes/inc.poolpots.php';
include '../tests/includes/inc.poolrules.php';
include '../tests/includes/inc.users.php';
include '../tests/includes/inc.receipts.php';
include '../tests/includes/inc.bankaccounts.php';
include '../tests/includes/inc.accountvalidation.php';
include '../tests/includes/inc.granular.php';
include '../tests/includes/inc.commissiontools.php';
include '../tests/includes/inc.exit.php';

// Build Script for Hope5000 Commission plan //
echo "Hope5000 Build Commmission Script<br>";

//ini_set('max_execution_time', 300);
//set_time_limit(3600);

$acct_email = "hope5000@testemail.com";
$password = "asdfasdf";

// Add the new systemuser //
$newuser = AddSystemUser($acct_email, $password);

// Set to newuser to communicate with the API //
global $authemail;
global $apikey;
$authemail = $newuser['authemail'];
$apikey = $newuser['apikey'];

// Add the system //
$systemname = "hope5000";
$commtype = 1;
$payouttype = 3;
$payoutmonthday = '15';
$payoutweekday = '5';
$autoauthgrand = "false";
$infinitycap = '2'; // No more than 2% of commission on sales volume for pay period //
$updatedurl = "http://".$_SERVER['SERVER_NAME']."/testupdatedurl.php";
$updatedusername = "testusername";
$updatedpassword = "testpassword";
$systemid = AddSystem($systemname, $commtype, $payouttype, $payoutmonthday, $payoutweekday, $autoauthgrand, $infinitycap, $updatedurl, $updatedusername, $updatedpassword);

// Rank Rules //
// Example //
//AddRankRule($systemid, $rank, $qualifytype, $qualifythreshold, $achvbonus, $breakage, "true");

// Rank 0 // Only AchvBonus //
$rulegroup = 1;
$maxdacleg = 0;
AddRankRule($systemid, "0", "4", "0", "40", "false", $rulegroup, $maxdacleg, "true"); // QLFY_RANK=0 //
AddRankRule($systemid, "0", "5", "10", "40", "false", $rulegroup, $maxdacleg, "true"); // bonus=$40 //

// Rank 1 //
$rulegroup = 2;
$maxdacleg = 10;
AddRankRule($systemid, "1", "8", "3", "0", "false", $rulegroup, $maxdacleg, "true"); // PAC Customer (LVL1) //
AddRankRule($systemid, "1", "7", "10", "0", "false", $rulegroup, $maxdacleg, "true"); // DAC // Customer+Affiliate (Entire Downline)
AddRankRule($systemid, "1", "5", "5", "0", "false", $rulegroup, $maxdacleg, "true"); // DCC // Customer (Entire Downline)
$rulegroup = 3;
AddRankRule($systemid, "-1", "4", "1", "40", "false", $rulegroup, $maxdacleg, "true"); // QLFY_RANK=1 //
AddRankRule($systemid, "-1", "5", "20", "40", "false", $rulegroup, $maxdacleg, "true"); // bonus=$40 //

// Rank 2 //
$rulegroup = 4;
$maxdacleg = 25;
AddRankRule($systemid, "2", "8", "4", "0", "false", $rulegroup, $maxdacleg, "true"); // PAC Customer (LVL1) //
AddRankRule($systemid, "2", "7", "25", "0", "false", $rulegroup, $maxdacleg, "true"); // DAC // Customer+Affiliate (Entire Downline)
AddRankRule($systemid, "2", "5", "13", "0", "false", $rulegroup, $maxdacleg, "true"); // DCC // Customer (Entire Downline)
$rulegroup = 5;
AddRankRule($systemid, "-2", "4", "2", "40", "false", $rulegroup, $maxdacleg, "true"); // QLFY_RANK=2 //
AddRankRule($systemid, "-2", "5", "30", "40", "false", $rulegroup, $maxdacleg, "true"); // bonus=$40 //

// Rank 3 //
$rulegroup = 6;
$maxdacleg = 63;
AddRankRule($systemid, "3", "8", "5", "0", "false", $rulegroup, $maxdacleg, "true"); // PAC Customer (LVL1) //
AddRankRule($systemid, "3", "7", "75", "0", "false", $rulegroup, $maxdacleg, "true"); // DAC // Customer+Affiliate (Entire Downline)
AddRankRule($systemid, "3", "5", "38", "0", "false", $rulegroup, $maxdacleg, "true"); // DCC // Customer (Entire Downline)
$rulegroup = 7;
AddRankRule($systemid, "-3", "4", "3", "40", "false", $rulegroup, $maxdacleg, "true"); // QLFY_RANK=3 //
AddRankRule($systemid, "-3", "5", "40", "40", "false", $rulegroup, $maxdacleg, "true"); // bonus=$40 //

// Rank 4 //
$rulegroup = 8;
$maxdacleg = 125;
AddRankRule($systemid, "4", "8", "6", "0", "false", $rulegroup, $maxdacleg, "true"); // PAC Customer (LVL1) //
AddRankRule($systemid, "4", "7", "200", "0", "false", $rulegroup, $maxdacleg, "true"); // DAC // Customer+Affiliate (Entire Downline)
AddRankRule($systemid, "4", "5", "100", "0", "false", $rulegroup, $maxdacleg, "true"); // DCC // Customer (Entire Downline)
$rulegroup = 9;
AddRankRule($systemid, "-4", "4", "4", "40", "false", $rulegroup, $maxdacleg, "true"); // QLFY_RANK=4 //
AddRankRule($systemid, "-4", "5", "50", "40", "false", $rulegroup, $maxdacleg, "true"); // bonus=$40 //

// Rank 5 //
$rulegroup = 10;
$maxdacleg = 250;
AddRankRule($systemid, "5", "8", "8", "0", "false", $rulegroup, $maxdacleg, "true"); // PAC Customer (LVL1) //
AddRankRule($systemid, "5", "7", "500", "0", "false", $rulegroup, $maxdacleg, "true"); // DAC // Customer+Affiliate (Entire Downline)
AddRankRule($systemid, "5", "5", "250", "0", "false", $rulegroup, $maxdacleg, "true"); // DCC // Customer (Entire Downline)
$rulegroup = 11;
AddRankRule($systemid, "-5", "4", "5", "40", "false", $rulegroup, $maxdacleg, "true"); // QLFY_RANK=4 //
AddRankRule($systemid, "-5", "5", "60", "40", "false", $rulegroup, $maxdacleg, "true"); // bonus=$40 //

// Rank 6 //
$rulegroup = 12;
$maxdacleg = 500;
AddRankRule($systemid, "6", "8", "8", "0", "false", $rulegroup, $maxdacleg, "true"); // PAC Customer (LVL1) //
AddRankRule($systemid, "6", "7", "1000", "0", "false", $rulegroup, $maxdacleg, "true"); // DAC // Customer+Affiliate (Entire Downline)
AddRankRule($systemid, "6", "5", "500", "0", "false", $rulegroup, $maxdacleg, "true"); // DCC // Customer (Entire Downline)
$rulegroup = 13;
AddRankRule($systemid, "-6", "4", "6", "40", "false", $rulegroup, $maxdacleg, "true"); // QLFY_RANK=4 //
AddRankRule($systemid, "-6", "5", "70", "40", "false", $rulegroup, $maxdacleg, "true"); // bonus=$40 //

// Rank 7 //
$rulegroup = 14;
$maxdacleg = 1250;
AddRankRule($systemid, "7", "8", "8", "0", "false", $rulegroup, $maxdacleg, "true"); // PAC Customer (LVL1) //
AddRankRule($systemid, "7", "7", "2500", "0", "false", $rulegroup, $maxdacleg, "true"); // DAC // Customer+Affiliate (Entire Downline)
AddRankRule($systemid, "7", "5", "1250", "0", "false", $rulegroup, $maxdacleg, "true"); // DCC // Customer (Entire Downline)
$rulegroup = 15;
AddRankRule($systemid, "-7", "4", "7", "40", "false", $rulegroup, $maxdacleg, "true"); // QLFY_RANK=4 //
AddRankRule($systemid, "-7", "5", "80", "40", "false", $rulegroup, $maxdacleg, "true"); // bonus=$40 //

// Commission Rules //
// AddCommissionRule($systemid, $rank, $startgen, $endgen, $qualifytype, $qualifythreshold, $infinity, $percent, $display)
AddCommissionRule($systemid, 0, 1, 1, "0", "0", "false", 3, "true");
AddCommissionRule($systemid, 1, 1, 1, "0", "0", "false", 5, "true");
AddCommissionRule($systemid, 2, 1, 1, "0", "0", "false", 6, "true");
AddCommissionRule($systemid, 2, 2, 2, "0", "0", "false", 2, "true");
AddCommissionRule($systemid, 3, 1, 1, "0", "0", "false", 7, "true");
AddCommissionRule($systemid, 3, 2, 2, "0", "0", "false", 3, "true");
AddCommissionRule($systemid, 3, 3, 3, "0", "0", "false", 2, "true");
AddCommissionRule($systemid, 4, 1, 1, "0", "0", "false", 8, "true");
AddCommissionRule($systemid, 4, 2, 2, "0", "0", "false", 4, "true");
AddCommissionRule($systemid, 4, 3, 3, "0", "0", "false", 2, "true");
AddCommissionRule($systemid, 5, 1, 1, "0", "0", "false", 8, "true");
AddCommissionRule($systemid, 5, 2, 2, "0", "0", "false", 5, "true");
AddCommissionRule($systemid, 5, 3, 3, "0", "0", "false", 3, "true");
AddCommissionRule($systemid, 5, 4, 4, "0", "0", "false", 2, "true");
AddCommissionRule($systemid, 6, 1, 1, "0", "0", "false", 8, "true");
AddCommissionRule($systemid, 6, 2, 2, "0", "0", "false", 5, "true");
AddCommissionRule($systemid, 6, 3, 3, "0", "0", "false", 3, "true");
AddCommissionRule($systemid, 6, 4, 4, "0", "0", "false", 2, "true");
AddCommissionRule($systemid, 6, 5, 5, "0", "0", "false", 2, "true");
AddCommissionRule($systemid, 6, 6, 6, "0", "0", "true", 0.5, "true");
AddCommissionRule($systemid, 7, 1, 1, "0", "0", "false", 8, "true");
AddCommissionRule($systemid, 7, 2, 2, "0", "0", "false", 5, "true");
AddCommissionRule($systemid, 7, 3, 3, "0", "0", "false", 3, "true");
AddCommissionRule($systemid, 7, 4, 4, "0", "0", "false", 2, "true");
AddCommissionRule($systemid, 7, 5, 5, "0", "0", "false", 2, "true");
AddCommissionRule($systemid, 7, 6, 6, "0", "0", "true", 1, "true");

$maxusers = 100;
$maxreceipts = 100;

$sponsorArray = array();
$sponsorcount = 0;

////////////////////
// Add Test Users //
////////////////////
for ($index=1; $index <= $maxusers; $index++)
{
	$userid = $index;
		
	// Testdata: Only the first 10 are allowed to be sponsors // 
	if ($index-1 > 10)
		$maxsponsor = 10;
	else
		$maxsponsor = $index-1;

	if ($index <= 10)
		$sponsorid = rand(0, $maxsponsor);
	else
		$sponsorid = rand(1, $maxsponsor);

	//if ($sponsorcount == 0)
	//	$sponsorid = 0;
	//else
	//{
	//	$userindex = rand(1, $sponsorcount);
	//	$sponsorid = $sponsorArray[$userindex];
	//}

	$signupdate = date("Y")."-".rand(1, 12)."-".rand(1, 27);
	$usertype = rand(1, 2);

	//if ($usertype == 1)
	//{
	//	$sponsorcount = array_push($sponsorArray, $userid);
	//}

	//echo "userid=".$userid.", sponsorid=".$sponsorid.", signupdate=".$signupdate."<br>";
	AddUser($systemid, $userid, $sponsorid, $signupdate, $usertype, "false");
}

///////////////////////
// Add Test Receipts //
///////////////////////
//GranAddReceipts($systemid, $maxusers, $maxreceipts);

for ($index=1; $index <= $maxreceipts; $index++)
{
	$receiptid = $index;
	$userid = rand(1, $maxusers); // All users make a purchase // $index;
	$amount = rand(1, 200).".".rand(1, 99);
	$purchasedate = date("Y")."-".rand(1, 12)."-".rand(1, 27);
	//$randcomm = rand(1, 3);
	//if ($randcomm == 3) // 1 in 3 chance not commissionable //
	//	$commissionable = "false";
	//else
		$commissionable = "true";
	
	echo "receiptid=".$receiptid.", userid=".$userid.", date=".$purchasedate.", amount=".$amount."<br>";
	AddReceipt($systemid, $receiptid, $userid, $amount, $purchasedate, $commissionable, "false");

	$receipttotal += $amount;
}

$record = CalcPredictGrandTotal($systemid, date("Y")."-1-1", date("Y")."-12-31", $receipttotal, "true");
if (($record['grandpayouts']['receipts'] == 0) ||
	($record['grandpayouts']['commissions'] == 0) ||
	($record['grandpayouts']['achvbonuses'] == 0))
{
	echo "<font color=red><b>Predict Empty grandpayouts<b></font><br>";
	Pre($record);
}

// Do actual calculations //
$record = CalcCommissions($systemid, date("Y")."-1-1", date("Y")."-12-31", $receipttotal, "true");
if (($record['grandpayouts']['receipts'] == 0) ||
	($record['grandpayouts']['commissions'] == 0) ||
	($record['grandpayouts']['achvbonuses'] == 0))
{
	echo "<font color=red><b>Calc Empty grandpayouts<b></font><br>";
	Pre($record);
}

// Exit out to generate the test files //
ExitTest($systemid);

?>