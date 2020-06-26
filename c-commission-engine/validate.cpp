
#include "validate.h"
#include "debug.h"
#include "ezTok.h"

/////////////////
// Constructor //
/////////////////
CValidate::CValidate()
{

}

/////////////////////////
// Verify it's numeric //
/////////////////////////
bool CValidate::is_number(const std::string& s)
{
	//if (s.size() > MAX_NUMERIC_LEN) // Bank account number doesn't meet criteria //
	//	return false;
	if (s.size() == 0)
		return false;

    //std::string::const_iterator it = s.begin();
    //while (it != s.end() && std::isdigit(*it)) ++it;
    //return !s.empty() && it == s.end();

	int length = (int)s.size();
	if (length == 0)
		return false;

	int index;
	for (index=0; index < length; index++)
	{
		std::string test;
		test += s.at(index);

		// Minus at front of a number //
		if ((index == 0) && (s.at(index) == '-'))
		{
			// Continue checking //
		}
		else if (((s.at(index) >= 48) && (s.at(index) <= 57))) // Check alpha ranges 0-9 //
		{
			// Continue checking //
		}
		else
		{
			return false;
		}
	}

	return true;

}

/////////////////////////
// Is it a decimal value //
/////////////////////////
bool CValidate::is_decimal(const std::string& s)
{
	int length = (int)s.size();
	if (length > MAX_NUMERIC_LEN) // Is there any logical reason why a decimal value should be longer than 50? //
		return false;
	if (length == 0)
		return false;

	int index;
	for (index=0; index < length; index++)
	{
		std::string test_char;
		test_char += s.at(index);

		// If it's not a number and it's not a period... then it's not a decimal //
		if ((is_number(test_char) == false) && (s.at(index) != '.'))
			return false;
	}

	return true;
}

////////////////////////
// Is it an ipaddress //
////////////////////////
bool CValidate::is_ipaddress(const std::string& s)
{
	int length = (int)s.size();
	if (length > MAX_IP_LEN)
		return false;
	if (length == 0)
		return false;

	int index;
	for (index=0; index < length; index++)
	{
		std::string test_char;
		test_char += s.at(index);

		// Make sure it's either a number or a period //
		if ((is_number(test_char) == false) && (s.at(index) != '.'))
			return false;
	}

	return true;
}

//////////////////////////////
// Is it an alphabet number //
//////////////////////////////
bool CValidate::is_basicalpha(const std::string& s)
{
	int length = (int)s.size();
	if (length == 0)
		return false;

	int index;
	for (index=0; index < length; index++)
	{
		std::string test;
		test += s.at(index);

		// Check alpha ranges A-z and a-z //
		if (((s.at(index) >= 65) && (s.at(index) <= 90)) ||
			((s.at(index) >= 97) && (s.at(index) <= 122)) || 
			(s.at(index) == 46))
		{
			// Continue checking //
		}
		else
		{
			return false;
		}
	}

	return true;
}

//////////////////////////////
// Is it an alphabet number //
//////////////////////////////
bool CValidate::is_alpha(const std::string& s)
{
	int length = (int)s.size();
	if (length == 0)
		return false;

	int index;
	for (index=0; index < length; index++)
	{
		std::string test;
		test += s.at(index);

		// Check alpha ranges A-z and a-z //
		if (((s.at(index) >= 65) && (s.at(index) <= 90)) ||
			((s.at(index) >= 97) && (s.at(index) <= 122)) || 	
			(s.at(index) == '-') || // Hyphen - //
			(s.at(index) == '_') || // Underscore - //
			(s.at(index) == 32) || // Space //
			(s.at(index) == 46))   // Period //
		{
			// Continue checking //
		}
		else
		{
			return false;
		}
	}

	return true;
}

//////////////////////////////
// Is it an alphabet number //
//////////////////////////////
bool CValidate::is_alphanum(const std::string& s)
{
	int length = (int)s.size();
	if (length == 0)
		return false;

	int index;
	for (index=0; index < length; index++)
	{
		std::string test;
		test += s.at(index);

		// Check alpha ranges A-z and a-z //
		if (((s.at(index) >= 65) && (s.at(index) <= 90)) ||
			((s.at(index) >= 97) && (s.at(index) <= 122)) || 
			((s.at(index) >= 48) && (s.at(index) <= 57)) || // Numbers //
			(s.at(index) == '-') || // Hyphen - //	
			(s.at(index) == '_') || // Underscore //
			(s.at(index) == '#') || // Pound // Needed for address
			(s.at(index) == ',') || // Comma // Needed for address
			(s.at(index) == '/') || // Forward slash // Needed for address
			(s.at(index) == '*') || // Asterisk //
			(s.at(index) == '+') || // Plus // TZIN
			(s.at(index) == 32) || // Space //
			(s.at(index) == 46))   // Period //
		{
			// Continue checking //
		}
		else
		{
			return false;
		}
	}

	return true;
}

////////////////////////////
// Is it an email address //
////////////////////////////
bool CValidate::is_email(const std::string& s)
{
	int length = (int)s.size();
	if (length == 0)
		return false;
	if (length > 128)
		return false;

	bool flag_at = false;
	bool flag_period = false;
	int index;
	for (index=0; index < length; index++)
	{
		if ((s.at(index) == '@') && (index == 0))
			return false;
		if ((s.at(index) == '.') && (index == 0))
			return false;
		if ((s.at(index) == '@') && (index > length-2))
			return false;
		if ((s.at(index) == '.') && (index == length-1))
			return false;

		// Verify valid characters //
		// Check alpha ranges A-z and a-z //
		if (((s.at(index) >= 65) && (s.at(index) <= 90)) || // A-Z //
			((s.at(index) >= 97) && (s.at(index) <= 122)) || // a-z //
			((s.at(index) >= 48) && (s.at(index) <= 57)) || // Numbers //	
			(s.at(index) == '.') || // Period //
			(s.at(index) == '-') || // Hyphen - //
			(s.at(index) == '+') || // + //
			(s.at(index) == '_') || // Underscore _ //
			(s.at(index) == '@'))   
		{

		}
		else
		{
			return false;
		}

		// Make sure there is an @ and a . //
		if (s.at(index) == '@')
			flag_at = true;
		if (s.at(index) == '.')
			flag_period = true;
	}

	if ((flag_at == false) || (flag_period == false))
		return false;

	return true;
}

//////////////////////////
// Is it a valid userid //
//////////////////////////
bool CValidate::is_userid(const std::string& s)
{
	int length = (int)s.size();
	if (length == 0)
		return false;

	int index;
	for (index=0; index < length; index++)
	{
		std::string test;
		test += s.at(index);

		if (((s.at(index) >= 65) && (s.at(index) <= 90)) || // A-z
			((s.at(index) >= 97) && (s.at(index) <= 122)) || // a-z //
			((s.at(index) >= 48) && (s.at(index) <= 57)) || // Numbers //	
			(s.at(index) == '-') || // Dash/Minus //
			(s.at(index) == '.'))   // Period //
		{
			// Continue checking //
		}
		else
		{
			return false;
		}
	}

	return true;
}

///////////////////////////////////////
// Handle zipcode slightly different //
///////////////////////////////////////
bool CValidate::is_zipcode(const std::string& s)
{
	int length = (int)s.size();
	if (length == 0)
		return false;

	int index;
	for (index=0; index < length; index++)
	{
		std::string test;
		test += s.at(index);

		if (((s.at(index) >= 48) && (s.at(index) <= 57)) || // Numbers //	
			(s.at(index) == '-'))  // Dash/Minus //
		{
			// Continue checking //
		}
		else
		{
			return false;
		}
	}

	return true;
}

///////////////////////////////////////////////////////
// Uppercase, lowercase, numeric, special characters //
///////////////////////////////////////////////////////
bool CValidate::is_password(const std::string& s)
{
	int length = (int)s.size();
	if (length == 0)
		return false;

	int index;
	for (index=0; index < length; index++)
	{
		std::string test;
		test += s.at(index);

		if (((s.at(index) >= 32) && (s.at(index) <= 126)) &&
			(s.at(index) != 34) &&
			(s.at(index) != 39) &&
			(s.at(index) != 44))
		{
			// Continue checking //
			
		}
		else
		{
			return false;
		}
	}

	return true;
}

/////////////////////////////////
// Is the value true or false? //
/////////////////////////////////
bool CValidate::is_boolean(const std::string& s)
{
	if ((s != "true") && (s != "false"))
		return false;
	else
		return true;
}

////////////////////////////////
// Is the search string valid //
////////////////////////////////
bool CValidate::is_qstring(const std::string& s)
{
	int length = (int)s.size();
	if (length == 0)
		return false;

	int index;
	for (index=0; index < length; index++)
	{
		std::string test;
		test += s.at(index);

		if (((s.at(index) >= 65) && (s.at(index) <= 90)) || // A-z
			((s.at(index) >= 97) && (s.at(index) <= 122)) || // a-z //
			((s.at(index) >= 48) && (s.at(index) <= 57)) || // Numbers //	
			(s.at(index) == '*') || // Wild Card //
			(s.at(index) == '=') || // Equals //
			(s.at(index) == '@') || // Needed for email addresses //
			(s.at(index) == '-') || // Needed for dates //
			(s.at(index) == '&') || // And appending together //
			(s.at(index) == '_')) // underscore //
		{
			// Continue checking //
		}
		else
		{
			printf("CValidate::is_qstring = (%s)", test.c_str());
			return false;
		}
	}

	return true;
}

////////////////////////////////
// Is the search string valid //
////////////////////////////////
bool CValidate::is_json(const std::string& s)
{
	int length = (int)s.size();
	if (length == 0)
		return false;

	int index;
	for (index=0; index < length; index++)
	{
		std::string test;
		test += s.at(index);

		if (((s.at(index) >= 65) && (s.at(index) <= 90)) || // A-z
			((s.at(index) >= 97) && (s.at(index) <= 122)) || // a-z //
			((s.at(index) >= 48) && (s.at(index) <= 57)) || // Numbers //	
			(s.at(index) == ':') || 
			(s.at(index) == '"') || 
			(s.at(index) == '{') || 
			(s.at(index) == '}') || 
			(s.at(index) == '(') || 
			(s.at(index) == ')') ||
			(s.at(index) == '[') || 
			(s.at(index) == ']') ||
			(s.at(index) == '.') ||
			(s.at(index) == '-') ||
			(s.at(index) == ' ') ||
			(s.at(index) == '?') ||
			(s.at(index) == '=') ||
			(s.at(index) == '/') ||
			(s.at(index) == '_') ||
			(s.at(index) == ',')) 
		{
			// Continue checking //
		}
		else
		{
			printf("CValidate::is_json = (%s)", test.c_str());
			return false;
		}
	}

	return true;
}

///////////////////////////
// Verify if it's a date //
///////////////////////////
bool CValidate::is_date(const std::string& s)
{
	if (s.size() == 0)
		return false;
	if (s.size() < 8) // Date needs to be at least 8 chars long //
		return false;
	if (is_number(s) == true)
		return false;

	if (s.at(4) == '-')
		return is_date_database(s);
	else
		return is_date_human(s);
}

/*
/////////////////////////////
// Verify it's a timestamp //
/////////////////////////////
bool CValidate::is_timestamp(const std::string& s)
{
	CDebug debug;

	//debug.Debug(DEBUG_TRACE, "CValidate::is_timestamp - s", s);

	int length = (int)s.size();
	if (length > MAX_TIMESTAMP_SIZE)
		return false;
	if (length == 0)
		return false;

	char tmpstr[30];
	memset(tmpstr, 0, 30);
	sprintf(tmpstr, "%s", s.c_str());

	char datestr[15];
	memset(datestr, 0, 15);
	memcpy(datestr, tmpstr, 10);

	char timestr[20]; //17
	memset(timestr, 0, 20);
	memcpy(timestr, &tmpstr[11], 18);

	string first = datestr;
	if (is_date_database(first) == false)
	{
		debug.Debug(DEBUG_TRACE, "CValidate::is_timestamp - is_date_database(first) == false");
		return false;
	}

	string second = timestr;
	int timelength = second.size();
	int index;
	for (index=0; index < timelength; index++)
	{
		string test_char;
		test_char += second.at(index);
		
		// Make sure it's a number or '/' //
		if ((index == 0) || 
			(index == 1) ||
			(index == 3) ||
			(index == 4) ||
			(index == 6) ||
			(index == 7))
		{
			if (is_number(test_char) == false)
			{
				debug.Debug(DEBUG_TRACE, "CValidate::is_timestamp - is_number(test_char) == false, index", index);
				return false;
			}
		}
		else if ((index == 2) || (index == 5))
		{
			if (second[index] != ':')
			{
				debug.Debug(DEBUG_TRACE, "CValidate::is_timestamp - second[index] != ':'");
				return false;
			}
		}
		else if (index == 8)
		{
			if (second[index] != '.')
			{
				debug.Debug(DEBUG_TRACE, "CValidate::is_timestamp - second[index] != '.'");
				return false;
			}
		}
		else if ((index >= 9) && (index <= 12))
		{
			if (is_number(test_char) == false)
			{
				debug.Debug(DEBUG_TRACE, "CValidate::is_timestamp - is_number(test_char) == false");
				return false;
			}
		}
		else if ((second[index] != '-') && (is_number(test_char) == false))
		{
			debug.Debug(DEBUG_TRACE, "CValidate::is_timestamp - (second[index] != '-') || (is_number(test_char) == false)");
			return false;
		}

	}

	return true;
}
*/
/////////////////////////////
// Verify it's a timestamp //
/////////////////////////////
bool CValidate::is_timestamp(const std::string& s)
{
	CDebug debug; 

	debug.Debug(DEBUG_TRACE, "CValidate::is_timestamp - s", s);

	if (is_date(s)) // Allow a date for a timestamp //
		return true;

	int length = (int)s.size();
	if (length < 14) // Bare minimum single digit // 2018-7-1 7:0:7 //
		return false;
	if (length > MAX_TIMESTAMP_SIZE)
		return false;
	if (length == 0)
		return false;

	if (is_date(s) == true)
		return true;

	CezTok tok(s.c_str(), ' ');
	if (tok.GetMax() != 1)
	{
		//debug.Debug(DEBUG_TRACE, "CValidate::is_timestamp - tok.GetMax()", tok.GetMax());
		return false;
	}

	/*
	char tmpstr[30];
	memset(tmpstr, 0, 30);
	sprintf(tmpstr, "%s", s.c_str());

	char datestr[15];
	memset(datestr, 0, 15);
	memcpy(datestr, tmpstr, 10);

	char timestr[20]; //17
	memset(timestr, 0, 20);
	memcpy(timestr, &tmpstr[11], 18);
	*/

	//string first = trim(datestr);
	//if (is_date_database(first) == false)
	if (is_date_database(tok.GetValue(0)) == false)
	{
		debug.Debug(DEBUG_TRACE, "CValidate::is_timestamp - is_date_database(first) == false");
		return false;
	}

	//string second = timestr;
	string second = tok.GetValue(1);
	int timelength = second.size();
	int index;
	for (index=0; index < timelength; index++)
	{
		string test_char;
		test_char += second.at(index);
		
		// Make sure it's a number or '/' //
		if ((index == 0) || 
			(index == 1) ||
			(index == 3) ||
			(index == 4) ||
			(index == 6) ||
			(index == 7))
		{
			if (is_number(test_char) == false)
			{
				debug.Debug(DEBUG_TRACE, "CValidate::is_timestamp - test_char", test_char.c_str());

				debug.Debug(DEBUG_TRACE, "CValidate::is_timestamp - is_number(test_char) == false, index", index);
				return false;
			}
		}
		else if ((index == 2) || (index == 5))
		{
			if (second[index] != ':')
			{
				debug.Debug(DEBUG_TRACE, "CValidate::is_timestamp - second[index] != ':'");
				return false;
			}
		}
		else if (index == 8)
		{
			if (second[index] != '.')
			{
				debug.Debug(DEBUG_TRACE, "CValidate::is_timestamp - second[index] != '.'");
				return false;
			}
		}
		else if ((index >= 9) && (index <= 12))
		{
			if (is_number(test_char) == false)
			{
				debug.Debug(DEBUG_TRACE, "CValidate::is_timestamp - is_number(test_char) == false");
				return false;
			}
		}
		else if ((second[index] != '-') && (is_number(test_char) == false))
		{
			debug.Debug(DEBUG_TRACE, "CValidate::is_timestamp - (second[index] != '-') || (is_number(test_char) == false)");
			return false;
		}

	}

	return true;
}


/////////////////////////////
// Is it normal 12/25/2016 //
/////////////////////////////
bool CValidate::is_date_human(const std::string& s)
{
	int length = (int)s.size();
	if (length > MAX_DATE_SIZE) // It can never be longer than 10 //
		return false;
	if (length < 8) // It can never be less than 8 in length //
		return false;
	if (length == 0)
		return false;

	int index;
	int slashcount = 0;
	for (index=0; index < length; index++)
	{
		std::string test_char;
		test_char += s.at(index);
		
		// Make sure it's a number or '/' //
		if ((is_number(test_char) == false) && (s.at(index) != '/'))
			return false;

		if (s.at(index) == '/')
		{
			if ((index == 0) || (index > 5))
				return false;
			if (index == length-1) // Slash can't be at the very end //
				return false;
			
			slashcount++;
		}
	}

	// Make sure there are always two dashes //
	if (slashcount != 2)
		return false;

	return true;
}

//////////////////////////
// Is it 2017-1-1 style //
//////////////////////////
bool CValidate::is_date_database(const std::string& s)
{
	int length = (int)s.size();
	if (length > MAX_DATE_SIZE) // It can never be longer than 10 //
		return false;
	if (length < 8) // It can never be less than 8 in length //
		return false;
	if (length == 0)
		return false;

	int index;
	int dashcount = 0;
	for (index=0; index < length; index++)
	{
		std::string test_char;
		test_char += s.at(index);

		if ((index == 4) && (s.at(index) != '-')) // This position will always need to be a - //
			return false;

		// Make sure it's a number or - //
		if ((is_number(test_char) == false) && (s.at(index) != '-'))
			return false;

		if (s.at(index) == '-')
		{
			if (index == 5) // Dashes can't be next to each other //
				return false;
			if (index == length-1) // Dash can't be at the very end //
				return false;
			
			dashcount++;
		}
	}

	// Make sure there are always two dashes //
	if (dashcount != 2)
		return false;

	return true;
}