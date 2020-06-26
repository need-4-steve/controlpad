#ifndef _EZSERVER_H
#define _EZSERVER_H

////////////////
// ezServer.h //
////////////////

#include <sys/select.h>
#include <sys/types.h>
#include <sys/socket.h>
#include <sys/stat.h>
#include <fcntl.h>
#include <netinet/in.h>
#include <netinet/ip.h>
#include <arpa/inet.h>
#include <signal.h>
#include <stdio.h>
#include <string.h>
#include <stdlib.h>
#include <unistd.h>
#include <errno.h>
#include <list>

#include "debug.h"
#include "packets.h"
#include "netuser.h"
#include "payments.h"
#include "ezRecv.h"
#include "ezSSL.h"

// I think this limit is roughly 35000 due to file descriptor limitations. I think //
#define MAX_CONNECTIONS		1000 // 1024 allowed default by kernal, but can change if wanted //
#define NET_SOCKET_TIMEOUT	100000 // In Nano Seconds // 1 microsecond //

// Flag that tells the daemon to exit. //
static volatile int g_exit_request = 0;

//////////////////////
// Thread functions //
//////////////////////
void *ThreadSocketRecv(void *param);
void *ThreadParseData(void *param);
void *ThreadCoreParseData(void *param);
//bool ThreadParseData(CNetUser *iuser);
int ThreadSend(CNetUser *iuser, bool shownetworkout);
bool ThreadDisconnectUser(CNetUser *iuser);

///////////////////////////////////////////////////////////////////////
// Inhert this into your server class you want to process your data //
//////////////////////////////////////////////////////////////////////
class CezNetwork : CDebug
{
public:
	CezNetwork();
	~CezNetwork(); // Shutdown all the socket connections properly //
	bool Startup(CDb *pDB, CezSettings *psettings);
	//bool pSelect(); // Process the messages //
	bool pSelectFull(); // One added step needed //

//private:
	
	// Retain database login information to pass along //
	CezSettings *m_pSettings;
	CDb *m_pDB;
	CezRecv m_Recv;

	// These needed for setting up network communications //
	int m_ListenSocket; // Retain the socket we are listening on //
	sigset_t m_orig_mask;
	fd_set m_readfds;
	fd_set m_writefds;
	
		
	// Linked Lists //
	list <CNetUser> m_NetUsersLL; // Retain the linked list of clients //

	CezSSL m_SSLServ;
};

#endif