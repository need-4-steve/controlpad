#ifndef _DATE_H
#define _DATE_H

////////////
// date.h //
////////////
#include "debug.h"

////////////////////////
// Parse out the date //
////////////////////////
class CDate : CDebug
{
public:
	CDate(const char *date);
	void Print();

	int m_Year;
	int m_Month;
	int m_Day;
};

///////////////////////////////////
// Handle date window comparison //
///////////////////////////////////
class CDateCompare : CDebug
{
public:
	void CompareFix(char *startdate, char *enddate);
	CDateCompare(const char *startdate, const char *enddate);
	bool IsBetween(const char *date);
	long IsDaysCount();

//private:
	int m_sYear;
	int m_sMonth;
	int m_sDay;

	int m_eYear;
	int m_eMonth;
	int m_eDay;

	long ConvDateToSec(int year, int month, int day);
};

#endif