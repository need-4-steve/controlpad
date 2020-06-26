<?php

////////////////////////////////////////////
// Uppercase, Lowercase, Numeric, Special //
////////////////////////////////////////////
function is_userid($string)
{
	// 48-57 // 65-90 // 97-122
	$special = "=.";
	$valid = true;
	$length = strlen($string);
	for ($index=0; $index < $length; $index++)
	{
		$slength = strlen($special);
		$found = false;
		for ($sindex=0; $sindex < $slength; $sindex++)
		{
			if ($string[$index] == $special[$sindex])
				$found = true;
		}

		if ($found == false)
		{
			if (ord($string[$index]) < 48)
				return false;
			if (ord($string[$index]) > 122)
				return false;
			if ((ord($string[$index]) > 57) && (ord($string[$index]) < 65))
				return false;
			if ((ord($string[$index]) > 90) && (ord($string[$index]) < 97))
				return false;

		}
	}

	return true;
}

//////////////////////////////
// a-zA-z and space, period //
//////////////////////////////
function is_alpha($string)
{
	// 65-90 // 97-122
	$special = " .";
	$valid = true;
	$length = strlen($string);
	for ($index=0; $index < $length; $index++)
	{
		$slength = strlen($special);
		$found = false;
		for ($sindex=0; $sindex < $slength; $sindex++)
		{
			if ($string[$index] == $special[$sindex])
				$found = true;
		}

		if ($found == false)
		{
			if (ord($string[$index]) < 65)
				return false;
			if (ord($string[$index]) > 122)
				return false;
			if ((ord($string[$index]) > 90) && (ord($string[$index]) < 97))
				return false;
		}
	}

	return true;
}

function is_currency($number)
{
  	return preg_match("/^-?[0-9]+(?:\.[0-9]{1,2})?$/", $number);
}
?>