#include "convert.h"
#include "debug.h"
#include "ezTok.h"
#include <sstream>
#include <stdlib.h>

/////////////////////////////////
// Convert Int To String value //
/////////////////////////////////
string CConvert::IntToStr(int value)
{
	stringstream ss;
	ss << value;
	return ss.str();
}

////////////////////////////////////////
// Convert a double to a string value //
////////////////////////////////////////
string CConvert::DoubleToStr(double value)
{
	stringstream ss;
	ss << value;
	return ss.str();
}

///////////////////////////
// Convert string to int //
///////////////////////////
int CConvert::StrToInt(const char *string)
{
	if (string != NULL)
		return atoi(string);
	return -1;
}

//////////////////////////////
// Convert string to double //
//////////////////////////////
double CConvert::StrToFloat(const char *string)
{
	if (string != NULL)
		return atof(string);
	return -1;
}

////////////////////////////
// Convert string to bool //
////////////////////////////
bool CConvert::StrToBool(const char *string)
{
	if (string == NULL)
	{	
		CDebug debug;
		return debug.Debug(DEBUG_ERROR, "CConvert::StrToBool - string == NULL");
	}

	if (strcmp(string, "f") == 0)
		return false;
	if (strcmp(string, "t") == 0)
		return true;
	if (strcmp(string, "F") == 0)
		return false;
	if (strcmp(string, "T") == 0)
		return true;
	if (strcmp(string, "false") == 0)
		return false;
	if (strcmp(string, "true") == 0)
		return true;
	if (strcmp(string, "FALSE") == 0)
		return false;
	if (strcmp(string, "TRUE") == 0)
		return true;

	CDebug debug;
	return debug.Debug(DEBUG_ERROR, "CConvert::StrToBool - End of function should never be reached");
}

/////////////////////////////////////////////
// Convert from / to - type date structure //
/////////////////////////////////////////////
string CConvert::FixDate(string s)
{
	if (s.length() == 0)
		return s;

	int length = strlen(s.c_str());
	int index;
	for (index=0; index < length; index++)
	{
		if (s.at(index) == '-')
			return s; // It's already the correct format //
	}

	// Identify month, year, day //
	char thedate[50];
	sprintf(thedate, "%s", s.c_str());
	const char *pyear, *pmonth, *pday;
	//pmonth = strtok(thedate, "/");
	//pday = strtok(NULL, "/");
	//pyear = strtok(NULL, "/");

	// Use the new tok class we made //
	CezTok tok(thedate, '/');
	pmonth = tok.GetValue(0).c_str();
	pday = tok.GetValue(1).c_str();
	pyear = tok.GetValue(2).c_str();

	stringstream ss;
	ss << pyear << "-" << pmonth << "-" << pday;
	return ss.str();
}

///////////////////////////////
// Search and replace string //
///////////////////////////////
string CConvert::StrReplace(std::string& str, const std::string& oldStr, const std::string& newStr)
{
	std::string::size_type pos = 0u;
	while((pos = str.find(oldStr, pos)) != std::string::npos)
	{
    	str.replace(pos, oldStr.length(), newStr);
    	pos += newStr.length();
	}

	return str;
}

//////////////////////////////////////////////
// Mask an account number or routing number //
//////////////////////////////////////////////
const char *CConvert::Mask(const char *number, int backlen)
{
	std::string numbermask;
	int length = strlen(number);
	int index;
	for (index=0; index < length-backlen; index++)
	{
		numbermask += "X";
	}

	for (index=length-backlen; index < length; index++)
	{
		numbermask += number[index];
	}

	return numbermask.c_str();
}

///////////////////////////////
// Convert to all lower case //
///////////////////////////////
const char *CConvert::ToLower(const char *string)
{
	static std::string data;
	data = "";

	int index;
	int maxindex = strlen(string);

	for (index=0; index < maxindex; index++)
	{
		data += tolower(string[index]);
	}

	//static std::string data = "";string;
	//std::transform(data.begin(), data.end(), data.begin(), ::tolower);
	return data.c_str();
}

// Convert Binary to Hex //
string CConvert::bin2hex(const string& input)
{
    string res;
    const char hex[] = "0123456789ABCDEF";
    //for (auto sc : input)
    unsigned int index;
    for (index=0; index < input.size(); index++)
    {
        unsigned char c = static_cast<unsigned char>(input.at(index));
        res += hex[c >> 4];
        res += hex[c & 0xf];
    }

    return res;
}