<?php

/////////////////////////////////
// Convert values for checkbox //
/////////////////////////////////
function ConvCheckbox($value)
{
	if ($value == "on")
		return "true";
	else
		return "false";
}

//////////////////////////////////////////////////////////
// Allow Perfect Cents. None of those rounding problems //
//////////////////////////////////////////////////////////
function PerfectCents($number, $decimal = '.')
{
	$broken_number = explode($decimal, $number);
	$final_cents = substr($broken_number[1], 0, 2);
    $comma_number = number_format($broken_number[0]).$decimal.$final_cents;
    return str_replace(",", "", $comma_number);
}