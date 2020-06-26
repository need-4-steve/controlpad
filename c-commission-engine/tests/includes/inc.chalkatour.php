<?php

function AddChalkatourCommRules($system_id, $invtype)
{
	//////////////
	// Designer //
	//////////////
	//$result = AddCommRule($system_id, "1", 0, "false", "25", $invtype, 1); // PSV gen=0 //
	//TestCheck("true", $result, "Chalk AddCommRule #0.0");

	///////////////////
	// Lead Designer //
	///////////////////

	$result = AddCommRule($system_id, "2", 1, "false", "3", $invtype, 1, 1);
	TestCheck("true", $result, "Chalk AddCommRule #1.0");
	$result = AddCommRule($system_id, "2", 1, "false", "1", $invtype, 1, 1);
	TestCheck("true", $result, "Chalk AddCommRule #1.1");

	/////////////////////
	// Master Designer //
	/////////////////////
	$result = AddCommRule($system_id, "3", 1, "false", "3", $invtype, 1, 1);
	TestCheck("true", $result, "Chalk AddCommRule #2.0");
	$result = AddCommRule($system_id, "3", 1, "false", "2", $invtype, 1, 1);
	TestCheck("true", $result, "Chalk AddCommRule #2.1");
	$result = AddCommRule($system_id, "3", 2, "false", "2", $invtype, 1, 1);
	TestCheck("true", $result, "Chalk AddCommRule #2.2");

	////////////
	// Mentor //
	////////////
	$result = AddCommRule($system_id, "4", 1, "false", "4", $invtype, 1, 1);
	TestCheck("true", $result, "Chalk AddCommRule #3.0");
	$result = AddCommRule($system_id, "4", 1, "false", "4", $invtype, 1, 1);
	TestCheck("true", $result, "Chalk AddCommRule #3.1");
	$result = AddCommRule($system_id, "4", 2, "false", "3", $invtype, 1, 1);
	TestCheck("true", $result, "Chalk AddCommRule #3.2");

	////////////////////
	// Leading Mentor //
	////////////////////
	$result = AddCommRule($system_id, "5", 1, "false", "4", $invtype, 1, 1);
	TestCheck("true", $result, "Chalk AddCommRule #4.0");
	$result = AddCommRule($system_id, "5", 1, "false", "6", $invtype, 1, 1);
	TestCheck("true", $result, "Chalk AddCommRule #4.1");
	$result = AddCommRule($system_id, "5", 2, "false", "4", $invtype, 1, 1);
	TestCheck("true", $result, "Chalk AddCommRule #4.2");
	$result = AddCommRule($system_id, "5", 3, "false", "1", $invtype, 1, 1);
	TestCheck("true", $result, "Chalk AddCommRule #4.3");

	///////////////////
	// Master Mentor //
	///////////////////
	$result = AddCommRule($system_id, "6", 1, "false", "4", $invtype, 1, 1);
	TestCheck("true", $result, "Chalk AddCommRule #5.0");
	$result = AddCommRule($system_id, "6", 1, "false", "8", $invtype, 1, 1);
	TestCheck("true", $result, "Chalk AddCommRule #5.1");
	$result = AddCommRule($system_id, "6", 2, "false", "5", $invtype, 1, 1);
	TestCheck("true", $result, "Chalk AddCommRule #5.2");
	$result = AddCommRule($system_id, "6", 3, "false", "2", $invtype, 1, 1);
	TestCheck("true", $result, "Chalk AddCommRule #5.3");

	///////////////
	// Couturier //
	///////////////
	$result = AddCommRule($system_id, "7", 1, "false", "5", $invtype, 1, 1);
	TestCheck("true", $result, "Chalk AddCommRule #6.0");
	$result = AddCommRule($system_id, "7", 1, "false", "9", $invtype, 1, 1);
	TestCheck("true", $result, "Chalk AddCommRule #6.1");
	$result = AddCommRule($system_id, "7", 2, "false", "6", $invtype, 1, 1);
	TestCheck("true", $result, "Chalk AddCommRule #6.2");
	$result = AddCommRule($system_id, "7", 3, "false", "2", $invtype, 1, 1);
	TestCheck("true", $result, "Chalk AddCommRule #6.3");

	/////////////////////////
	// Executive Couturier //
	/////////////////////////
	$result = AddCommRule($system_id, "8", 1, "false", "5", $invtype, 1, 1);
	TestCheck("true", $result, "Chalk AddCommRule #7.0");
	$result = AddCommRule($system_id, "8", 1, "false", "10", $invtype, 1, 1);
	TestCheck("true", $result, "Chalk AddCommRule #7.1");
	$result = AddCommRule($system_id, "8", 2, "false", "7", $invtype, 1, 1);
	TestCheck("true", $result, "Chalk AddCommRule #7.2");
	$result = AddCommRule($system_id, "8", 3, "false", "2", $invtype, 1, 1);
	TestCheck("true", $result, "Chalk AddCommRule #7.3");

	//////////////////////
	// Master Couturier //
	//////////////////////
	$result = AddCommRule($system_id, "9", 1, "false", "5", $invtype, 1, 1);
	TestCheck("true", $result, "Chalk AddCommRule #8.0");
	$result = AddCommRule($system_id, "9", 1, "false", "11", $invtype, 1, 1);
	TestCheck("true", $result, "Chalk AddCommRule #8.1");
	$result = AddCommRule($system_id, "9", 2, "false", "8", $invtype, 1, 1);
	TestCheck("true", $result, "Chalk AddCommRule #8.2");
	$result = AddCommRule($system_id, "9", 3, "false", "2", $invtype, 1, 1);
	TestCheck("true", $result, "Chalk AddCommRule #8.3");
}

?>