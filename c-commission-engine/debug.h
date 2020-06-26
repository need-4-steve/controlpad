#ifndef _DEBUG_H
#define _DEBUG_H
/////////////
// debug.h //
/////////////

#include <string.h>
#include <stdio.h>

#include <string>
#include <sstream>

//#include <iostream>

using namespace std;

#define DEBUG_OFF           0
#define DEBUG_MESSAGE       1 // This message will always be displayed //
#define DEBUG_ERROR			2
#define DEBUG_WARN			3
#define DEBUG_INFO			4
#define DEBUG_SQL			5
#define DEBUG_NETWORK_IN	6
#define DEBUG_NETWORK_OUT	7
#define DEBUG_DEBUG         8
#define DEBUG_TRACE         9

// Options to the way we display the messages //
#define DEBUG_SCREEN		1
#define	DEBUG_FILE			2
#define DEBUG_BOTH			3

// Add some color //
#define ANSI_COLOR_RED     "\x1b[31m"
#define ANSI_COLOR_GREEN   "\x1b[32m"
#define ANSI_COLOR_YELLOW  "\x1b[33m"
#define ANSI_COLOR_BLUE    "\x1b[34m"
#define ANSI_COLOR_MAGENTA "\x1b[35m"
#define ANSI_COLOR_CYAN    "\x1b[36m"
#define ANSI_COLOR_RESET   "\x1b[0m"

#define ANSI_COLOR_BG_YELLOW  	"\x1b[43m"
#define ANSI_COLOR_BG_BLUE 		"\x1b[44m"
#define ANSI_COLOR_BG_MAGENTA 	"\x1b[45m"
#define ANSI_COLOR_BG_CYAN 		"\x1b[46m"

///////////////////////////////////////////
// A C-Style class right here would work //
///////////////////////////////////////////
std::string trim(std::string& str);
const char *trim(const char *str);

char *rtrim(char* string, char whitespace);

///////////////////////////////////
// Convert a string to uppercase //
///////////////////////////////////
std::string toupper(std::string s);

// This allows stringstream to be passed in as parameter //
// char *test(std::stringstream& str);

/*
template<typename DataType>
inline std::stringstream& operator<<(std::stringstream& ss, const DataType& data)
{
    static_cast<std::ostream&>(ss) << data; // This causes errors on new systems //
    //ss << data; // Segmentation fault //
    return ss;
}
*/

/*
template<typename DataType>
int operator<<(stringstream& ss, const DataType& data)
{
    static_cast<ostream&>(ss) << data;
    return atoi(ss.str().c_str());
}
*/

// For some reason this doesn't work //
// Compare function for std::map //
struct cmp_str
{
    bool operator()(char const *a, char const *b)
    {
    	return strcmp(a, b) < 0;
    }
};

/////////////////
// Debug class //
/////////////////
class CDebug
{
public:
    void SetLogFile(string logfile);
    void SetLevel(int level);
    void SetDisplay(int display);

	bool Debug(int err_level, const char *err_msg);
	bool Debug(int err_level, const char *err_msg, int err_num);
    bool Debug(int err_level, const char *err_msg, bool err_num);
	bool Debug(int err_level, const char *err_msg, float err_num);
    bool Debug(int err_level, const char *err_msg, double err_num);
    bool Debug(int err_level, const char *err_msg, unsigned long err_num);
	bool Debug(int err_level, const char *err_msg, const char *err_more);
    bool Debug(int err_level, const char *err_msg, string err_more);
    bool Debug(int err_level, const char *err_msg, basic_ostream<char> &err_more);

    bool Debug(int err_level, int socket, const char *err_msg);
    bool Debug(int err_level, int socket, const char *err_msg, int err_num);
    bool Debug(int err_level, int socket, const char *err_msg, bool err_num);
    bool Debug(int err_level, int socket, const char *err_msg, const char *err_more);

    bool Help(string color1, string command, string color2, string comments);

    // Time functions //
    bool TimeStart();
    bool TimeEnd();
    int GetTimeSec();
    
    struct timeval m_TimeStart;
    struct timeval m_TimeEnd;

    string m_LogFile;
};

/*
error: the system is in distress, customers are probably being affected (or will soon be) and the fix probably requires human intervention. The "2AM rule" applies here- if you're on call, do you want to be woken up at 2AM if this condition happens? If yes, then log it as "error".

warn: an unexpected technical or business event happened, customers may be affected, but probably no immediate human intervention is required. On call people won't be called immediately, but support personnel will want to review these issues asap to understand what the impact is. Basically any issue that needs to be tracked but may not require immediate intervention.

info: things we want to see at high volume in case we need to forensically analyze an issue. System lifecycle events (system start, stop) go here. "Session" lifecycle events (login, logout, etc.) go here. Significant boundary events should be considered as well (e.g. database calls, remote API calls). Typical business exceptions can go here (e.g. login failed due to bad credentials). Any other event you think you'll need to see in production at high volume goes here.

debug: just about everything that doesn't make the "info" cut... any message that is helpful in tracking the flow through the system and isolating issues, especially during the development and QA phases. We use "debug" level logs for entry/exit of most non-trivial methods and marking interesting events and decision points inside methods.

trace: for extremely detailed and potentially high volume logs that you don't typically want enabled even during normal development. Examples include dumping a full object hierarchy, logging some state during every iteration of a large loop, etc.
*/

#endif