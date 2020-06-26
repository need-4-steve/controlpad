#ifndef _EZTOK_H
#define _EZTOK_H

/////////////
// ezTok.h //
/////////////

#include "debug.h"

#include <string>
#include <map>

using namespace std;

/////////////////////////////////////
// Manage incoming variable values //
/////////////////////////////////////
class CezTok : public CDebug
{
public:
	CezTok(const char *data, char delimiter);
	CezTok(const char *data, const char *delimiter);
	int GetMax();
	string GetValue(int index);

private:
	map <string, string> m_MapStr;
	map <int, string> m_MapCount;

	int m_Max;
};

#endif