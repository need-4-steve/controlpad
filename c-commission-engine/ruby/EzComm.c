//#include "../ezRecv.h"
#include "string"

//static CezRecv g_Recv;

///////////////////////////////////
// Startup the commisison engine //
///////////////////////////////////
int Startup()
{
    //printf("TEST - STARTUP\n");
    //g_Recv.Startup(CezSettings *psettings);

    return 1;
}

/////////////////////////////
// Set the variable values //
/////////////////////////////
int SetVar(const char *varname, const char *value)
{
	//return 0;
    //g_Recv.SetHeadVar(varname, value);
    return 0;
}

///////////////////////////////////
// Process the commission engine //
///////////////////////////////////
const char *Process()
{
	//return g_Recv.Process();
	return "Process Called in .so";
}