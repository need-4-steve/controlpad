#include "date.h"
#include "ezTok.h"

#include <stdio.h>
#include <string.h>
#include <stdlib.h>

// Do date conversion //
CDate::CDate(const char *date)
{
	if (strstr(date, "/") != NULL)
	{
		char tmpstr[20];
		sprintf(tmpstr, "%s", date);

		const char *pyear, *pmonth, *pday;
		//pyear = strtok(tmpstr, "-");
		//pmonth = strtok(NULL, "-");
		//pday = strtok(NULL, "-");

		// Use the new tok class we made //
		CezTok tok(tmpstr, '/');
		pmonth = tok.GetValue(0).c_str();
		pday = tok.GetValue(1).c_str();
		pyear = tok.GetValue(2).c_str();
			
		m_Year = atoi(pyear);
		m_Month = atoi(pmonth);
		m_Day = atoi(pday);
	}
	else if (strstr(date, "-") != NULL)
	{
		char tmpstr[20];
		sprintf(tmpstr, "%s", date);

		const char *pyear, *pmonth, *pday;
		//pyear = strtok(tmpstr, "-");
		//pmonth = strtok(NULL, "-");
		//pday = strtok(NULL, "-");

		// Use the new tok class we made //
		CezTok tok(tmpstr, '-');
		pyear = tok.GetValue(0).c_str();
		pmonth = tok.GetValue(1).c_str();
		pday = tok.GetValue(2).c_str();
			
		m_Year = atoi(pyear);
		m_Month = atoi(pmonth);
		m_Day = atoi(pday);
	}
}

/////////////////////
// Disply the date //
// This will help with testing //
/////////////////////////////////
void CDate::Print()
{
	char tmpstr[128];
	sprintf(tmpstr, "year = %d, month = %d, day = %d\n", m_Year, m_Month, m_Day);
	Debug(DEBUG_DEBUG, tmpstr);
}

// Fix it easily //
CDateCompare::CDateCompare(const char *startdate, const char *enddate)
{
	CompareFix((char *)startdate, (char *)enddate);
}

///////////////////////////
// Simpify parsing dates //
///////////////////////////
void CDateCompare::CompareFix(char *startdate, char *enddate)
{
	CDate start(startdate);
	CDate end(enddate);

	m_sYear = start.m_Year;
	m_sMonth = start.m_Month;
	m_sDay = start.m_Day;

	m_eYear = end.m_Year;
	m_eMonth = end.m_Month;
	m_eDay = end.m_Day;
}

//////////////////////////////////////////////////////////
// Check to see if date is inbetween start and end date //
//////////////////////////////////////////////////////////
bool CDateCompare::IsBetween(const char *date)
{
	if (strlen(date) == 0)
	{
		//Debug(DEBUG_ERROR, "CDateCompare::IsBetween - strlen(date) == 0");
		return false;
	}

	long start = ConvDateToSec(m_sYear, m_sMonth, m_sDay);
	long end = ConvDateToSec(m_eYear, m_eMonth, m_eDay);

	CDate mdate(date);
	long middle = ConvDateToSec(mdate.m_Year, mdate.m_Month, mdate.m_Day);
/*
	stringstream ssTest;
	ssTest << "CDateCompare::IsBetween - start=" << start << ", end=" << end << ", middle=" << middle;
	Debug(DEBUG_ERROR, ssTest.str().c_str());

	stringstream ssTest2;
	ssTest2 << "CDateCompare::IsBetween - m_sYear=" << m_sYear << ", m_sMonth=" << m_sMonth << ", m_sDay=" << m_sDay;
	Debug(DEBUG_ERROR, ssTest2.str().c_str());

	stringstream ssTest3;
	ssTest3 << "CDateCompare::IsBetween - m_eYear=" << m_eYear << ", m_eMonth=" << m_eMonth << ", m_eDay=" << m_eDay;
	Debug(DEBUG_ERROR, ssTest3.str().c_str());

	stringstream ssTest4;
	ssTest4 << "CDateCompare::IsBetween - m_mYear=" << mdate.m_Year << ", m_mMonth=" << mdate.m_Month << ", m_mDay=" << mdate.m_Day;
	Debug(DEBUG_ERROR, ssTest4.str().c_str());
*/
	if ((middle >= start) && (middle <= end))
	{
		return true;
	}
	
	return false;
}

////////////////////////////////////////////
// Count the number of days between dates //
////////////////////////////////////////////
long CDateCompare::IsDaysCount()
{
	long startsec = ConvDateToSec(m_sYear, m_sMonth, m_sDay);
	long endsec = ConvDateToSec(m_eYear, m_eMonth, m_eDay);
	
	long calcsec = endsec - startsec;
	int calcdays = calcsec/(86400);

	return calcdays;

	//if (calcdays > day_count_limit)
	//	return false;
	//else
	//	return true;
}

///////////////////////////////
// Convert a date to seconds //
///////////////////////////////
long CDateCompare::ConvDateToSec(int year, int month, int day)
{
	struct tm thedate = {0};

	thedate.tm_hour = 0;   
	thedate.tm_min = 0; 
	thedate.tm_sec = 0;
 	thedate.tm_year = year - 1900; 
 	thedate.tm_mon = month; 
 	thedate.tm_mday = day;
 	thedate.tm_isdst = -1; // daylight savings variable //

 	long seconds = mktime(&thedate);
 	return seconds;
}