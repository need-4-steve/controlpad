<?php

////////////////////////////////////
// Conver to text commission type //
////////////////////////////////////
function DispCommType($commtype)
{
	if ($commtype == 1)
		return "Hybrid-Uni";
	else if ($commtype == 2)
		return "Breakaway";
	else if ($commtype == 3)
		return "Binary";
	else
		return "Unknown (".$commtype.")";
}

//////////////////////////////////
// Display the text payout type //
//////////////////////////////////
function DispPayoutType($payouttype)
{
	if ($payouttype == 1)
		return "Monthly";
	else if ($payouttype == 2)
		return "Weekly";
	else if ($payouttype == 3)
		return "Daily";
	else if ($payouttype == 4)
		return "API Triggered";
	else

		return "Unknown (".$payouttype.")";
}

///////////////////////////////
// Handle monthday 0 problem //
///////////////////////////////
function DispPayoutMonthday($monthday)
{
	if ($monthday == "0")
		return "";
	else
		return $monthday;
}

///////////////////////////////
// Handle monthday 0 problem //
///////////////////////////////
function DispPayoutWeekday($weekday)
{
	if ($weekday == "0")
		return "";
	else
		return $weekday;
}

////////////////////////////
// Handle Boolean display //
////////////////////////////
function DispBoolY($bool)
{
	if ($bool == "t")
		return "Y";
	else
		return "";
}

////////////////////////////
// Handle Boolean display //
////////////////////////////
function DispBoolN($bool)
{
	if ($bool == "t")
		return "";
	else
		return "N";
}

////////////////////////////
// Handle Boolean display //
////////////////////////////
function DispBoolYN($bool)
{
	if ($bool == "t")
		return "Y";
	else if ($bool == "f")
		return "N";
	else
		return "???";
}

////////////////////////////////
// Fix date to human readable //
////////////////////////////////
function DispTimestamp($timestamp)
{
	//return date("F jS, Y (H:ma)", strtotime($timestamp));
	return date("m-d-Y (H:ma)", strtotime($timestamp));
}

////////////////////////////////
// Fix date to human readable //
////////////////////////////////
function DispDate($date)
{
	if ($date == "")
		return "NULL";

	//return date("F jS, Y", strtotime($date));
	return date("m-d-Y", strtotime($date));
}

/////////////////////////////////////////
// Handle display of Bank Account type //
/////////////////////////////////////////
function DispBankAccountType($accounttype)
{
	if ($accounttype == 1)
		return "Checking";
	else if ($accounttype == 2)
		return "Savings";
	else
		return "Unknown (".$accounttype.")";
}

///////////////////////////////////////////
// Return text value for qualify type id //
///////////////////////////////////////////
function DispQualifyType($value)
{
	if ($value == 1)
		return "Personal Sales";
	if ($value == 2)
		return "Group Sales";
	if ($value == 3)
		return "Signup Count";
	if ($value == 4)
		return "Rank";
	if ($value == 5)
		return "Customer Count";
	if ($value == 6)
		return "Affiliate Count";
	if ($value == 7)
		return "Customer and Affiliate Count";
	if ($value == 8)
		return "Level 1 Customer Count";
	if ($value == 9)
		return "Level 1 Affiliate Count";
	if ($value == 10)
		return "RSVD GROUP USED";
	if ($value == 11)
		return "RSVD GROUP SALESUSED";
	if ($value == 12)
		return "My Wholesale (Type 2)";
	if ($value == 13)
		return "My Retail (Type 2)";
	if ($value == 14)
		return "Team Wholesale";
	if ($value == 15)
		return "Team Retail";
	if ($value == 16)
		return "Rank Sum Leg";
	if ($value == 17)
		return "Rank Sum Lvl1";
	if ($value == 20)
		return "Team and My Wholesale";
	if ($value == 21)
		return "PSQ";

	return "Unknown (".$value.")";
}

/////////////////////////////////////
// Get readable text of ledgertype //
/////////////////////////////////////
function DispLedgerType($value)
{
	if ($value == 1)
		return "Nacha";
	if ($value == 2)
		return "Rewards";
	if ($value == 3)
		return "Commission";
	if ($value == 4)
		return "Transferred";
	if ($value == 5)
		return "CM Purchased";
	if ($value == 6)
		return "CM Used";
	if ($value == 7)
		return "Bonus";
	if ($value == 8)
		return "Custom Payout";
	if ($value == 9)
		return "Repair";
	if ($value == 10)
		return "Signup Bonus";
	if ($value == 11)
		return "Pool Bonus";
	if ($value == 12)
		return "Courtier Bonus"; //Rank Gen Bonus";

	return "Unknown (".$value.")";
}

////////////////////
// Inventory Type //
////////////////////
function DispInvType($value)
{
	if ($value == 1)
		return "Wholesale";
	if ($value == 2)
		return "Retail";
	if ($value == 3)
		return "Cash and Carry";
	if ($value == 4)
		return "Sold on Corporate";
	if ($value == 5)
		return "Affiliate on Corporate";

	return "Unknown (".$value.")";
}

////////////////////
// Inventory Type //
////////////////////
function DispEvent($value)
{
	if ($value == 1)
		return "On Wholesale Date";
	if ($value == 2)
		return "On Retail Date";
	
	return "Unknown (".$value.")";
}

////////////////////
// Inventory Type //
////////////////////
function DispUserType($value)
{
	if ($value == 1)
		return "Affiliate";
	if ($value == 2)
		return "Customer";
	
	return "Unknown (".$value.")";
}

/////////////////////////////////////////////
// Just grab global from created in select //
/////////////////////////////////////////////
function DisplayBatch($value)
{
	global $g_BatchDisplay;
	return $g_BatchDisplay;
}

////////////////////
// Inventory Type //
////////////////////
function DispPayType($value)
{
	if ($value == 1)
		return "Wholesale";
	if ($value == 2)
		return "Retail";
	
	return "Unknown (".$value.")";
}

///////////////////////////////////////
// Compensate for display rank rules //
///////////////////////////////////////
function DispRankRule($value)
{
	//if ($value == "-1")
	//	return "1 - PSQ";
	//else
		return $value;
}

function DispGeneration($value)
{
	if ($value == "-1")
		return "1 - PS";
	else
		return $value;
}
 
//////////////////////////
// Display menu numbers //
//////////////////////////
function DispToolNumbers($varname, $defaultvalue)
{
	$retval = "<select name='".$varname."'>";
	for ($index=1; $index <= 9; $index++)
	{
		if ($defaultvalue == $index)
			$retval .= "<option selected value='".$index."'>".$index."</option>";
		else
			$retval .= "<option value='".$index."'>".$index."</option>";
	}
	$retval .= "</select>";
	return $retval;
}

?>