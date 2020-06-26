<?php

////////////////////////////////////////
// Add multiple users into the system //
////////////////////////////////////////
function GranAddUsers($systemid, $maxusers)
{
	for ($index=1; $index <= $maxusers; $index++)
	{
		$userid = $index;
		$sponsorid = rand(0, $index-1);
		$signupdate = date("Y")."-".rand(1, 12)."-".rand(1, 27);
		$usertype = rand(1, 2);

		echo "userid=".$userid.", sponsorid=".$sponsorid.", signupdate=".$signupdate."<br>";

		AddUser($systemid, $userid, $sponsorid, $signupdate, $usertype, "false");
	}
}

////////////////////////////////////////
// Add multiple users into the system //
////////////////////////////////////////
function GranAddReceipts($systemid, $maxusers, $maxreceipts)
{
	$receipttotal = 0;

	for ($index=1; $index <= $maxreceipts; $index++)
	{
		$receiptid = $index;
		$userid = rand(1, $maxusers);
		$amount = rand(1, 5000).".".rand(1, 99);
		$purchasedate = date("Y")."-".rand(1, 12)."-".rand(1, 27);
		$randcomm = rand(1, 3);
		if ($randcomm == 3) // 1 in 3 chance not commissionable //
			$commissionable = "false";
		else
			$commissionable = "true";
		
		echo "receiptid=".$receiptid.", userid=".$userid.", date=".$purchasedate.", amount=".$amount."<br>";

		AddReceipt($systemid, $receiptid, $userid, $amount, $purchasedate, $commissionable, "false");

		$receipttotal += $amount;
	}

	return $receipttotal;
}

/////////////////////////////////////////
// Define the rank rules in the system //
/////////////////////////////////////////
function GranAddRankRules($systemid, $qualifytype, $rankrulemax)
{
	for ($index=1; $index <= $rankrulemax; $index++)
	{
		$rank = $index;
		
		if (($qualifytype == 1) || ($qualifytype == 2))
			$qualifythreshold = $index."000";
		else if ($qualifytype == 3) // Signup Count //
			$qualifythreshold = $index;
		$achvbonus = $index."00";
		if ($index == $rankrulemax)
			$breakage = "true";
		else
			$breakage = "false";

		AddRankRule($systemid, $rank, $qualifytype, $qualifythreshold, $achvbonus, $breakage, $index, "false");
	}
}

//////////////////////////////
// Add the commission rules //
//////////////////////////////
function GranAddCommRules($systemid, $qualifytype, $qualifythreshold, $commrulemax)
{
	for ($index=1; $index <= $commrulemax; $index++)
	{	
		$rank = $index;
		$percent = rand(1, 10);
		$infinity = "true";
		$randinf = rand(1, 2);
		if ($randinf == 1)
			$infinity = "true";
		else
			$infinity = "false";

		$retval = AddCommissionRule($systemid, $rank, 1, 4, $qualifytype, $qualifythreshold*$index, $infinity, $percent, "false");
		Pre($retval);
	}
}

///////////////////////////////////////
// Do a full similaton of all system //
///////////////////////////////////////
function GranSimilateFull($commtype, $qualifytype, $payouttype)
{
	$maxusers = 25;
	$maxreceipts = 25;
	$infinitycap = "2";
	$updatedurl = "http://".$_SERVER['SERVER_NAME']."/tests/testupdatedurl.php";

	echo "<b>Granular Full Similate: commtype=".$commtype.", qualifytype=".$qualifytype."</b><br>";

	$systemid = AddSystem("sys.1.".strtolower(GenRanStr(12)), $commtype, $payouttype, 15, 5, "false", $infinitycap, $updatedurl, "testusername", "testpassword", "false");
	echo "<i>systemid = ".$systemid."</i><br>";
	GranAddRankRules($systemid, $qualifytype, 5);
	GranAddCommRules($systemid, $qualifytype, 1000, 5);
	GranAddUsers($systemid, $maxusers);
	$receipttotal = GranAddReceipts($systemid, $maxusers, $maxreceipts);
	$receipttotal = number_format($receipttotal, 2, '.', ''); // english dollar/cents format //

	// We need to calculate our own achvbonus and commission values to fully compare grand total //
	$record = CalcPredictCommissions($systemid, date("Y")."-1-1", date("Y")."-12-31", $receipttotal, "true");

	Pre($record);
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

	// Payments? //
	
}

///////////////////////////
// Do the granular tests //
///////////////////////////
function TestGranular()
{
	// Qualify types //
	// 1 - Person Sales
	// 2 - Group Sales
	// 3 - Signup Count

	echo "<br><b>Granular Tests:</b><br>";
	//$record = PredictGrandTotal(2, date("Y")."-1-1", date("Y")."-12-31", "255993.72", "true");
	//Pre($record);
	GranSimilateFull(1, 1, 3);
	//GranSimilateFull(2, 1, 4);
	//GranSimilateFull(3, 1, 4);

/*
	// BREAKAWAY = 2 // A person breaks out of a downline to start their own branch //
	$systemid = AddSystem("sys.2.".strtolower(GenRanStr(12)), 2, 2, 15, 5, "false");
	GranAddUsers($systemid, 10);
	
	// BINARY = 3 // Take the two top legs. Pay the lesser of the two //
	$systemid = AddSystem("sys.3.".strtolower(GenRanStr(12)), 3, 3, 15, 5, "false");
	GranAddUsers($systemid, 10);

	// External payout type //
	$systemid = AddSystem("sys.4.".strtolower(GenRanStr(12)), rand(1, 3), 4, 15, 5, "false");
	GranAddUsers($systemid, 10);
*/	
	//$systemid = 20;
	//$precomm = CalcPredictCommissions($systemid, "2016-1-1", "2016-12-31", "true");
	//Pre($precomm);
}

/*

DELETE FROM users;
DELETE FROM receipts;
DELETE FROM breakdown;
DELETE FROM commrules;
DELETE FROM rankrules;
DELETE FROM batches;
DELETE FROM commissions;
DELETE FROM achvbonus;
DELETE FROM grandtotals;
DELETE FROM poolpots;
DELETE FROM poolrules;
DELETE FROM poolpayouts;
DELETE FROM binaryledger;

*/
?>