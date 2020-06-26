#ifndef _CONVERT_H
#define _CONVERT_H

#include <string>

using namespace std;

///////////////////////////////////
// Handle date window comparison //
///////////////////////////////////
class CConvert
{
public:
	string IntToStr(int value);
	string DoubleToStr(double value);
	int StrToInt(const char *string);
	double StrToFloat(const char *string);
	bool StrToBool(const char *string);
	string FixDate(string s);

	string StrReplace(std::string& str, const std::string& oldStr, const std::string& newStr);
	const char *Mask(const char *number, int backlen); // Mask an account number or routing number //
	const char *ToLower(const char *string);

	string bin2hex(const string& input);
};

#endif