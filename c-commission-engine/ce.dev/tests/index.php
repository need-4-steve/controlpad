<?php

include 'includes/inc.global.php';
include 'includes/inc.systemusers.php';
include 'includes/inc.systems.php';
include 'includes/inc.rankrules.php';
include 'includes/inc.commissionrules.php';
include 'includes/inc.poolpots.php';
include 'includes/inc.poolrules.php';
include 'includes/inc.users.php';
include 'includes/inc.receipts.php';
include 'includes/inc.bankaccounts.php';
include 'includes/inc.accountvalidation.php';
include 'includes/inc.granular.php';
include 'includes/inc.commissiontools.php';

echo "Starting Commission Engine Tests...<br>";


/////////////////////////
// All the basic tests //
/////////////////////////

/*
TestSystemUsers(); 
$systemid = TestSystems();
TestRankRules($systemid);
TestCommissionRules($systemid);
$poolpotid = TestPoolPots($systemid);
$poolruleid = TestPoolRules($systemid, $poolpotid);
$userid = TestUsers($systemid);
$receipt = TestReceipts($systemid, $userid);
$bankacct = TestBankAccounts($systemid, $userid);
TestAccountValidation($systemid, $userid);

$receipt['total'] = number_format($receipt['total'], 2, '.', ''); // english dollar/cents format //

// Do Prediction and Commissions now //
$precomm = CalcPredictCommissions($systemid, "2016-1-1", "2016-12-31", "true");
$pregrand = CalcPredictGrandTotal($systemid, "2016-1-1", "2016-12-31", $receipt['total'], "true");
$realcomm = CalcCommissions($systemid, "2016-1-1", "2016-12-31", $receipt['total'], "true");

$batches = CalcQueryBatches($systemid, "true");
$usercomm = CalcQueryUserComm($systemid, $userid, "true");
$batchcomm = CalcQueryBatchComm($systemid, $batches['batches'][0]['id'], "true");
*/

// Granular Rank+Commission Rules Test //
// With users/receipts generate payout //
// Verify correct values //
TestGranular();

// Commission Tools //

// Payments //

?>