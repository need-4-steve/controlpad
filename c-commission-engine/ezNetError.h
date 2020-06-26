#ifndef _EZNETERROR_H
#define _EZNETERROR_H

#include <string>

using namespace std;

//////////////////
// ezNetError.h //
//////////////////
class CezNetError
{
public:
	string GetErrorInfo(int errornum);
};

#endif