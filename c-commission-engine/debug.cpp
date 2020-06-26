#include "debug.h"

#include "CommissionEngine.h"
#include "Compile.h"
#include <sys/time.h>
#include <stdio.h>
#include <sstream>
#include <string>
#include <iostream>

#include <iomanip> // Precision //

#include <pthread.h>

// Global variable //
extern CCommissionEngine *g_pCommEng;

extern char g_DebugLevel;
extern char g_DebugDisplay;

///////////////////////////////////////////////////
// Just throw this stupid trim function in debug //
///////////////////////////////////////////////////
std::string trim(std::string& str)
{
	std::string retstr;
    int first = str.find_first_not_of(' ');
    int last = str.find_last_not_of(' ');
    if ((first >= 0) && (last >= 0))
    	retstr = str.substr(first, (last-first+1));

    first = retstr.find_first_not_of('\n');
    last = retstr.find_last_not_of('\n');
    if ((first >= 0) && (last >= 0))
    	retstr = retstr.substr(first, (last-first+1));

    first = retstr.find_first_not_of('\r');
    last = retstr.find_last_not_of('\r');
    if ((first >= 0) && (last >= 0))
    	retstr = retstr.substr(first, (last-first+1));

    // These below cause problems for storing json in ce_settings //

    // This needed for json parsing //
    first = retstr.find_first_not_of('"');
    last = retstr.find_last_not_of('"');
    if ((first >= 0) && (last >= 0))
    	retstr = retstr.substr(first, (last-first+1));

/*
    // This needed for json parsing //
    first = retstr.find_first_not_of('{');
    last = retstr.find_last_not_of('{');
    if ((first >= 0) && (last >= 0))
    	retstr = retstr.substr(first, (last-first+1));
    // This needed for json parsing //
    first = retstr.find_first_not_of('}');
    last = retstr.find_last_not_of('}');
    if ((first >= 0) && (last >= 0))
    	retstr = retstr.substr(first, (last-first+1));
*/

    return retstr;
}

const char *trim(const char *str)
{
	std::string tmpstr = str;
	tmpstr = trim(tmpstr);
	return tmpstr.c_str();
}

char *rtrim(char* string, char whitespace)
{
    char* original = string + strlen(string);
    while (*--original == whitespace)
    {
    	// Fix compile warning //
    }
    *(original + 1) = '\0';
    return string;
}

///////////////////////////////////
// Convert a string to uppercase //
///////////////////////////////////
std::string toupper(std::string s)
{
	std::string retstr;
	int index;
	int strsize = (int)s.size();
	for (index=0; index < strsize; index++)
	{
		char c = toupper(s.at(index));
		retstr += c;
	}

	return retstr;
}

////////////////////////////////////////////////////////////////
// Set the log filename. Needed to keep live and sim separate //
////////////////////////////////////////////////////////////////
void CDebug::SetLogFile(string logfile)
{
	m_LogFile = logfile;

	//printf("CDebug::debug - m_LogFile = %s\n", m_LogFile.c_str());
}

/////////////////////
// Set error level //
/////////////////////
void CDebug::SetLevel(int level)
{
	g_DebugLevel = level;
}

///////////////
// Set level //
///////////////
void CDebug::SetDisplay(int display)
{
	g_DebugDisplay = display;
}

/////////////////////////////////////////////////
// Easyily manage how to handle Debug messages //
/////////////////////////////////////////////////
bool CDebug::Debug(int err_level, const char *err_msg)
{
	
// Handle ruby-rice debug //
#ifdef COMPILE_RUBYRICE
	//printf("%d - %s\n", err_level, err_msg);
#endif

	// Do nothing //
	if ((g_DebugLevel == DEBUG_OFF) && (err_level == DEBUG_ERROR))
		return false;
	if (g_DebugLevel == DEBUG_OFF)
		return false;

	// Grab date/time //
	time_t now = time(0);
   	tm *ltm = localtime(&now);
   	std::stringstream timestamp;
   	timestamp << err_level << " - " << 1900 + ltm->tm_year << "-" << ltm->tm_mon << "-" << ltm->tm_mday << " " << ltm->tm_hour << ":" << ltm->tm_min << ":" << ltm->tm_sec;

   	// Build error string //
   	std::string errstr;
   	if (err_level != DEBUG_MESSAGE)
   	{
	   	errstr = timestamp.str();
	   	errstr += " - ";
	}
	errstr += err_msg;

	stringstream final;
	stringstream threadinfo;

	pthread_t self;
	self = pthread_self();
	stringstream ssSelf;
	ssSelf << self;

	if (g_pCommEng != NULL)
		threadinfo << "(t=.." << ssSelf.str().substr(ssSelf.str().length() - 3) << ")[" << g_pCommEng->m_Network.m_NetUsersLL.size() << "]";
	else
		threadinfo << "(t=.." << ssSelf.str().substr(ssSelf.str().length() - 3) << ")";

	/////////////////////////////////////////////////////
	// Add color to help sift though error logs easier //
	/////////////////////////////////////////////////////
	if (err_level == DEBUG_MESSAGE)
		final << threadinfo.str() << ANSI_COLOR_BG_CYAN << errstr << ANSI_COLOR_RESET << " \n";
	else if ((err_level == DEBUG_ERROR) && (err_level <= g_DebugLevel))
		final << threadinfo.str() << ANSI_COLOR_RED << errstr << ANSI_COLOR_RESET << " \n";
	else if ((err_level == DEBUG_WARN) && (err_level <= g_DebugLevel))
		final << threadinfo.str() << ANSI_COLOR_MAGENTA << errstr << ANSI_COLOR_RESET << " \n";
	else if ((err_level == DEBUG_INFO) && (err_level <= g_DebugLevel))
		final << threadinfo.str() << ANSI_COLOR_CYAN << errstr << ANSI_COLOR_RESET << " \n";
	else if ((err_level == DEBUG_DEBUG) && (err_level <= g_DebugLevel))
		final << threadinfo.str() << ANSI_COLOR_GREEN << errstr << ANSI_COLOR_RESET << " \n";
	else if ((err_level == DEBUG_TRACE) && (err_level <= g_DebugLevel))
		final << threadinfo.str() << ANSI_COLOR_BLUE << errstr << ANSI_COLOR_RESET << " \n";
	else if ((err_level == DEBUG_SQL) && (err_level <= g_DebugLevel))
		final << threadinfo.str() << ANSI_COLOR_YELLOW << errstr << ANSI_COLOR_RESET << " \n";
	else if ((err_level == DEBUG_NETWORK_IN) && (err_level <= g_DebugLevel))
		final << threadinfo.str() << ANSI_COLOR_BG_BLUE << errstr << ANSI_COLOR_RESET << " \n";
	else if ((err_level == DEBUG_NETWORK_OUT) && (err_level <= g_DebugLevel))
		final << threadinfo.str() << ANSI_COLOR_BG_YELLOW << errstr << ANSI_COLOR_RESET << " \n";

	string finalstr = final.str();
	if ((g_DebugDisplay == DEBUG_SCREEN) || (g_DebugDisplay == DEBUG_BOTH))
		printf("%s", finalstr.c_str());
	
	if ((g_DebugDisplay == DEBUG_FILE) || (g_DebugDisplay == DEBUG_BOTH))
	{
		//printf("CDebug::debug - m_LogFile = %s\n", m_LogFile.c_str());

		string logfile;
		if (m_LogFile.size() == 0)
			logfile = "/var/log/ce.unknown.log";
		else
			logfile = m_LogFile;

   		FILE *pFile;
		if ((pFile = fopen(logfile.c_str(), "a+")) == NULL)
		{
			string error = "Error opening " + logfile + " - If you want to log the errors change owner rights of file\n";
			printf("%s", error.c_str());
			return false;
		}	

		fwrite(finalstr.c_str(), sizeof(char), strlen(finalstr.c_str()), pFile);
		fclose(pFile);
	}

	// Only returning false can eliminate a bunch of code //
	return false;
}

//////////////////////////////////////////////////////
// Allow the error message to display a number also //
//////////////////////////////////////////////////////
bool CDebug::Debug(int err_level, const char *err_msg, int err_num)
{
	std::stringstream ss;
	ss << err_num;

	std::string message = err_msg;
	message += " = ";
	message += ss.str();
	return Debug(err_level, message.c_str());
}

bool CDebug::Debug(int err_level, const char *err_msg, bool err_num)
{
	std::stringstream ss;
	if (err_num == false)
		ss << "false";
	else if (err_num == true)
		ss << "true";

	std::string message = err_msg;
	message += " = ";
	message += ss.str();
	return Debug(err_level, message.c_str());
}

bool CDebug::Debug(int err_level, const char *err_msg, float err_num)
{
	std::stringstream ss;
	ss << std::setprecision(2) << std::fixed << err_num;
	
	std::string message = err_msg;
	message += " = ";
	message += ss.str();
	return Debug(err_level, message.c_str());
}

bool CDebug::Debug(int err_level, const char *err_msg, double err_num)
{
	std::stringstream ss;
	ss << std::setprecision(2) << std::fixed << err_num;
	
	std::string message = err_msg;
	message += " = ";
	message += ss.str();
	return Debug(err_level, message.c_str());
}

bool CDebug::Debug(int err_level, const char *err_msg, unsigned long err_num)
{
	std::stringstream ss;
	ss << err_num;

	std::string message = err_msg;
	message += " = ";
	message += ss.str();
	return Debug(err_level, message.c_str());
}

bool CDebug::Debug(int err_level, const char *err_msg, const char *err_more)
{
	std::stringstream ss;
	std::string tmpstr;

	ss << err_msg << " = " << err_more;
	tmpstr = ss.str();
	return Debug(err_level, tmpstr.c_str());
}

//////////////////
// Debug string //
//////////////////
bool CDebug::Debug(int err_level, const char *err_msg, string err_more)
{
	return Debug(err_level, err_msg, err_more.c_str());
}

////////////////////////
// Debug stringstream //
////////////////////////
bool CDebug::Debug(int err_level, const char *err_msg, basic_ostream<char> &err_more)
{
	stringstream tmpss;
	tmpss << err_more.rdbuf(); 
	return Debug(err_level, err_msg, tmpss.str());
}

//////////////////////////////////////
// Allow display of socket in debug //
//////////////////////////////////////
bool CDebug::Debug(int err_level, int socket, const char *err_msg)
{
	std::stringstream ss;
	std::string tmpstr;

	ss << "(" << socket << ") - " << err_msg;
	tmpstr = ss.str();
	return Debug(err_level, tmpstr.c_str());
}

/////////////////////////
// Socket with err_num //
/////////////////////////
bool CDebug::Debug(int err_level, int socket, const char *err_msg, int err_num)
{
	std::stringstream ss;
	std::string tmpstr;

	ss << "(" << socket << ") - " << err_msg << "=" << err_num;
	tmpstr = ss.str();
	return Debug(err_level, tmpstr.c_str());
}

/////////////////////////
// Socket with err_num bool //
/////////////////////////
bool CDebug::Debug(int err_level, int socket, const char *err_msg, bool err_num)
{
	std::stringstream ss;
	std::string tmpstr;

	if (err_num == true)
		ss << "(" << socket << ") - " << err_msg << "=true";
	else if (err_num == false)
		ss << "(" << socket << ") - " << err_msg << "=false";

	tmpstr = ss.str();
	return Debug(err_level, tmpstr.c_str());
}

///////////////////////////////////
// Socket with error_more string //
///////////////////////////////////
bool CDebug::Debug(int err_level, int socket, const char *err_msg, const char *err_more)
{
	std::stringstream ss;
	std::string tmpstr;

	ss << "(" << socket << ") - " << err_msg << " - " << err_more;
	tmpstr = ss.str();
	return Debug(err_level, tmpstr.c_str());
}

//////////////////
// Display Help //
//////////////////
bool CDebug::Help(string color1, string command, string color2, string comments)
{
	cout << color1;
	cout << command;
	cout << color2;
	cout << " - " << comments;
	cout << ANSI_COLOR_RESET << "\n";
	return true;
}

//////////////////////////
// Our time functons!!! //
//////////////////////////
bool CDebug::TimeStart()
{
	gettimeofday(&m_TimeStart, NULL);
	return true;
}

bool CDebug::TimeEnd()
{
	struct timeval tval_result;
	gettimeofday(&m_TimeEnd, NULL);
	timersub(&m_TimeEnd, &m_TimeStart, &tval_result);

	char timestr[256];
	sprintf(timestr, "CDebug::TimeEnd - Time elapsed: %ld.%06ld", (long int)tval_result.tv_sec, (long int)tval_result.tv_usec);
	Debug(DEBUG_WARN, (const char *)timestr);
	return true;
}

/////////////////////////////
// Get the time in seconds //
/////////////////////////////
int CDebug::GetTimeSec()
{
	struct timeval tval_result;
	gettimeofday(&tval_result, NULL);
	return (int)tval_result.tv_sec;
}