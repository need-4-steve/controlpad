#ifndef _CONNPOOL_H
#define _CONNPOOL_H

#include "debug.h"
#include "ezSettings.h"

#include <postgresql/libpq-fe.h>
#include <string>
#include <map>

#define MAX_CONN_LOOP		1000000	// 1 million seems to go fast and far exceeds any socket limit I've read //

#define CONNPOOL_INITALSLEEP	5000 // 5 milliseconds //
#define CONNPOOL_MAXSLEEP		30000000 // 30 seconds max sleep //
#define CONNPOOL_DEADWARN		10 // 10 cycles of searching for a connection //
#define CONNPOOL_DEADLOCK		120 // 120 cycles stuck then there is something seriously wrong :( //

//#define CONNTYPE_LIVE			1
//#define CONNTYPE_SIM			2

using namespace std;

///////////////////////////////////
// Handle individual connections //
///////////////////////////////////
class CConn 
{
public:
	CConn();

	string m_Query;
	string m_Error;
  	unsigned int m_Resource;
	bool m_InUse;
	int m_pgRowMax;
	int m_pgCurrentRow;
	map <int, string> m_RowMap;
	PGconn *m_pgConn;
	PGresult *m_pgResult;
	string m_ConnType;

	// Socket to track group of threads //
	int m_Socket;

	CezSettings *m_pSettings;
};

/////////////////////////////////
// Seperate thread c functions //
/////////////////////////////////
void *ThreadMangePoolConns(void *param);
void *ThreadEntrySQL(void *param);

bool ThreadExec(CConn *conn);
bool ThreadReleaseConn(int resource);
void ThreadReleaseSocketConn(int socket);

/////////////////////////////////////////////
// Manange a bunch of database connections //
/////////////////////////////////////////////
class CConnPool : private CDebug
{
public:
	CConnPool();
	~CConnPool();
	bool ConnectPool(string conntype, string conninfo, int maxconn, CezSettings *psettings);
	bool DynamicRelease();
	CConn *Exec(bool autorelease, int socket, string conntype, const char *sql);
	bool Exec(CConn *conn, bool autorelease, const char *sql); // Allow reuse of connection //
	int SocketThreadCount(int socket);
	bool WaitForThreads(int socket);
	int GetConnCount();

	// Allow turning connection pool on/off //
	void Enable();
	void Disable();
	bool IsEnabled();

	CConn *GetConn(int socket, string conntype);
	void GetConnQueue(int socket);

	//bool ReleaseConn(int resource);

private:
	int m_MaxSleepCount;

	int m_ResourceCount;

	string m_ConnInfo;

	CezSettings *m_pSettings;
};

#endif