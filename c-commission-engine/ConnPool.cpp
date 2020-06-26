#include "ConnPool.h"
#include <unistd.h>
#include <stdlib.h>
#include <sys/mman.h>
#include <sys/time.h>
#include <list>
//#include <thread>
#include <pthread.h>
#include <vector>
#include "debug.h"
#include "ezNetwork.h"

extern string g_TimeZone;

// Keep connection pool globally //
list <CConn> g_Conn;
int g_SQLTime = CONNPOOL_INITALSLEEP;
int g_MaxThreads = 0;
int g_MaxSQLTime = 0;

int g_ConstructCount = 0;
bool g_ConnPoolEnabled = true;
int g_ResourceCount = 0;

// Manage Threads to release a connection one at a time //
//int g_ConnCount = 0;
//int g_ConnCycle = 0;
//bool g_ConnSocketRelease = true;
//extern bool g_ConnBank[MAX_CONN_LOOP+1];

bool g_ThreadConnStarted = false;


pthread_mutex_t g_mutex = PTHREAD_MUTEX_INITIALIZER;

/*
////////////////////////////////////////////////////
// Manage all the pool connections in this thread //
////////////////////////////////////////////////////
void *ThreadMangePoolConns(void *param)
{ 
	CDebug debug;

	// Loop til we get an exit request //
	while (g_exit_request == 0)
	{
		static int previous_time = debug.GetTimeSec();

		// Release the ConnPool Thread GetConn one at a time //
		if ((g_ConnSocketRelease == true) && (g_ConnCount > 0))
		{
			g_ConnSocketRelease = false;
			
			int failsafecount = 0;
			g_ConnCycle++;
			while (g_ConnBank[g_ConnCycle] == false)
			{
				g_ConnCycle++;
				if (g_ConnCycle > MAX_CONN_LOOP)
					g_ConnCycle = 0;

				failsafecount++;
				if (failsafecount > MAX_CONN_LOOP+1)
				{
					debug.Debug(DEBUG_DEBUG, "ThreadMangePoolConns - failsafecount > MAX_CONN_LOOP. failsafecount", failsafecount);
					break;
				}
			}

			// Release the thread to get a db pool connection //
			if (failsafecount <= MAX_CONN_LOOP)
			{
				g_ConnBank[g_ConnCycle] = false;
			}

			previous_time = debug.GetTimeSec();
		}
		else // Failsafe to prevent infinite locking threads //
		{
			int timediff = debug.GetTimeSec()-previous_time;
			//Debug(DEBUG_ERROR, "CezNetwork::pSelectFull - timediff", timediff);
			if (timediff > 1) // Wait for dormant threads for a full second before restarting //
			{
				int foundcount = 0;
				int index;
				for (index=0; index < MAX_CONN_LOOP; index++)
				{
					if (g_ConnBank[index] == true)
					{
						debug.Debug(DEBUG_ERROR, "ThreadMangePoolConns - if g_ConnBank[socket]", index);
						foundcount++;
						previous_time = debug.GetTimeSec();
						g_ConnSocketRelease = true;
					}
				}

				if ((g_ConnCount == 0) && (foundcount > 0))
					g_ConnCount = foundcount;
				else
					usleep(500000); // Sleep for 10 milliseconds if no connection requests found //

				//Debug(DEBUG_ERROR, "CezNetwork::pSelectFull - timediff", timediff);
				//Debug(DEBUG_WARN, "CezNetwork::pSelectFull - foundcount", foundcount);
				//Debug(DEBUG_WARN, "CezNetwork::pSelectFull - g_ConnCount", g_ConnCount);
				//Debug(DEBUG_DEBUG, "CezNetwork::pSelectFull - #2 g_ConnCycle", g_ConnCycle);
				//Debug(DEBUG_DEBUG, "CezNetwork::pSelectFull -------------------------");
			}
		}
	}

	return NULL;
}
*/

//////////////////////////////
// Handle thread processing //
//////////////////////////////
void *ThreadEntrySQL(void *param)
{
	CConn *conn = (CConn *)param;

	struct timeval TimeStart;
    struct timeval TimeEnd;
	gettimeofday(&TimeStart, NULL);

	CDebug debug;
	debug.SetLogFile(conn->m_pSettings->m_LogFile.c_str());
	//debug.Debug(DEBUG_DEBUG, "ThreadEntrySQL - conn->m_Resource", conn->m_Resource);
	//debug.Debug(DEBUG_DEBUG, "ThreadEntrySQL - query", conn->m_Query.c_str());
	if (ThreadExec(conn) == false)
	{	
		debug.Debug(DEBUG_WARN, conn->m_Query.c_str());
		conn->m_Error = "ThreadEntrySQL - ThreadExec == false";
		debug.Debug(DEBUG_ERROR, conn->m_Error.c_str());
	}
	if (ThreadReleaseConn(conn->m_Resource) == false)
	{
		conn->m_Error = "ThreadEntrySQL - Problems with ThreadReleaseConn";
		debug.Debug(DEBUG_ERROR, conn->m_Error.c_str());
	}

	// Solve for time it took to process SQL //
	struct timeval tval_result;
	gettimeofday(&TimeEnd, NULL);
	timersub(&TimeEnd, &TimeStart, &tval_result);
	//char timestr[128];
	//sprintf(timestr, "Time elapsed: %ld.%06ld", (long int)tval_result.tv_sec, (long int)tval_result.tv_usec);
	g_SQLTime = tval_result.tv_sec*1000000+tval_result.tv_usec;
	if (g_SQLTime > g_MaxSQLTime)
		g_MaxSQLTime = g_SQLTime;

	// Added failsafe to not sleep longer than 60 seconds //
	if (g_SQLTime > CONNPOOL_MAXSLEEP)
		g_SQLTime = CONNPOOL_MAXSLEEP;

	// Release the thread resources //
	pthread_detach(pthread_self());
   	pthread_exit(NULL);

   	return NULL;
}

///////////////////////
// Set inital values //
///////////////////////
CConn::CConn()
{	
  	m_Resource = 0;
	m_InUse = false;
	m_pgRowMax = 0;
	m_pgCurrentRow = 0;
	m_pgConn = NULL;
	m_pgResult = NULL;
	//m_ConnType = 0;
	m_pSettings = 0;
	m_Socket = 0;

//	int index = 0;
//	for (index=0; index > MAX_CONN_LOOP; index++)
//	{
//		g_ConnBank[index] = false;
//	}
}

/////////////////
// Constructor //
/////////////////
CConnPool::CConnPool()
{
	CDebug debug;
	debug.Debug(DEBUG_TRACE, "CConnPool::CConnPool - Constructor Called. Should be only called once");

	g_ConstructCount++;
	m_MaxSleepCount = 0;
	m_pSettings = NULL;

	if (pthread_mutex_init(&g_mutex, NULL) != 0)
        debug.Debug(DEBUG_ERROR, "CConnPool::CConnPool() - pthread_mutex_init fail");
}

///////////////////
// Deconstructor //
///////////////////
CConnPool::~CConnPool()
{
	g_ConstructCount--;

	int inuse = 0;
	std::list <CConn>::iterator i;
	for (i=g_Conn.begin(); i!=g_Conn.end(); ++i)
	{
		if ((*i).m_InUse == true)
		{
//			Debug(DEBUG_TRACE, "CConnPool::~CConnPool - Query = ", (*i).m_Query);
			inuse++;
		}
	}

	//if (inuse != 0)
	//	Debug(DEBUG_TRACE, "CConnPool::~CConnPool - If this messages hangs then ExecDB isn't releasing a connpool connection. inuse", inuse);

	if (g_ConstructCount == 0)
	{
		//Debug(DEBUG_TRACE, "CConnPool::~CConnPool - Release Loop");

		// Release all the connections //
		//std::list <CConn>::iterator i;
		for (i=g_Conn.begin(); i!=g_Conn.end(); ++i)
		{
			// Wait for threads to finish //
			while ((*i).m_InUse == true)
			{
				usleep(g_SQLTime); // Sleep estimated amount of time it takes for SQL to process //
			}
			PQclear((*i).m_pgResult);
			PQfinish((*i).m_pgConn);
		}

		// Empty out all elements //
		g_Conn.clear();

		// RELEASE the threads //
		// Good example in ezNetwork //
		//pthread_detach(pthread_self()); // This needs to be the specific thread id //
   		//pthread_exit(NULL);

		// Print out stats at end for fine tuning purposes //
		//Debug(DEBUG_TRACE, "CConnPool::~CConnPool() - g_MaxSQLTime", g_MaxSQLTime);
		//Debug(DEBUG_TRACE, "CConnPool::~CConnPool() - g_MaxThreads", g_MaxThreads);
		//Debug(DEBUG_TRACE, "CConnPool::~CConnPool() - m_MaxSleepCount", m_MaxSleepCount);
		//Debug(DEBUG_TRACE, "CConnPool::~CConnPool() - g_ConstructCount", g_ConstructCount);

//		Debug(DEBUG_TRACE, "CConnPool::~CConnPool - All DB connections released properly");
	}
}

//////////////////////////////////////////////
// Make all the connections to the database //
//////////////////////////////////////////////
bool CConnPool::ConnectPool(string conntype, string conninfo, int maxconn, CezSettings *psettings)
{
	if (conntype.size() == 0)
		return Debug(DEBUG_ERROR, "CConnPool::ConnectPool - conntype.size() == 0");
	if (psettings == NULL)
		return Debug(DEBUG_ERROR, "CConnPool::ConnectPool - psettings == NULL");

	m_pSettings = psettings;
	CDebug::SetLogFile(m_pSettings->m_LogFile);

	// Allow the connection pool to be more dynamic //
	if (psettings->m_ApiConnPoolDynamic == true)
	{
		// Hold onto the conn info for dynamic creation later //
		m_ConnInfo = conninfo;
		return true;
	}

	// Loop through all connections //
	int index = 0;
	for (index=0; index < maxconn; index++)
	{
		CConn conn;
		conn.m_pSettings = psettings;
		conn.m_ConnType = conntype;
		conn.m_pgConn = PQconnectdb(conninfo.c_str());
		if (PQstatus(conn.m_pgConn) != CONNECTION_OK)
		{
			Debug(DEBUG_ERROR, "CConnPool::Connect - Postgresql Connection Failed:");
			return Debug(DEBUG_ERROR, PQerrorMessage(conn.m_pgConn));
		}
		g_ResourceCount++;
		conn.m_Resource = g_ResourceCount;		
		g_Conn.push_back(conn);

		//if (Exec(&conn, true, "SET timezone='UTC'") == false)
		//	return Debug(DEBUG_ERROR, "CConnPool::ConnectPool - Problems settings timezone to UTC");
	}

	// Start connection pool manager thread //
/*	if (g_ThreadConnStarted == false)
	{
		g_ThreadConnStarted = true;
		pthread_t t2;
		int retval;
	 	if ((retval = pthread_create(&t2, NULL, &ThreadMangePoolConns, NULL)) != 0)
		{
			Debug(DEBUG_ERROR, "CConnPool::ConnectPool pthread_create retval", retval);
			Debug(DEBUG_ERROR, "CConnPool::ConnectPool pthread_create err", strerror(retval));
			return NULL;
		}
		
	}
*/
	Debug(DEBUG_INFO, "CConnPool::ConnectPool - Successful connections", maxconn);

	return true;
}

/////////////////////////////////////////////////////////
// Release the database connections on dynamic release //
/////////////////////////////////////////////////////////
bool CConnPool::DynamicRelease()
{
	//Debug(DEBUG_TRACE, "CConnPool::DynamicRelease - (*i).g_Conn.size()", (int)g_Conn.size());

	int InUseCount = 0;
	std::list <CConn>::iterator i;
	for (i=g_Conn.begin(); i!=g_Conn.end(); ++i)
	{
		// Wait for threads to finish //
		if ((*i).m_InUse == true)
		{
			//Debug(DEBUG_TRACE, "CConnPool::DynamicRelease - (*i).m_InUse == true - resource", (int)(*i).m_Resource);
			InUseCount++;
		}
		else
		{
			if ((*i).m_pgResult != NULL)
			{
				PQclear((*i).m_pgResult);
				(*i).m_pgResult = NULL;
			}
			if ((*i).m_pgConn != NULL)
			{
				PQfinish((*i).m_pgConn);
				(*i).m_pgConn = NULL;
			}

			// Remove the connection //
			g_Conn.erase(i);
			return false; // Return false to process dynamic release again after removal //
		}
	}

	//Debug(DEBUG_TRACE, "CConnPool::DynamicRelease - InUseCount", InUseCount);
	return true;
}

///////////////////////
// Execute sql query //
///////////////////////
CConn *CConnPool::Exec(bool autorelease, int socket, string conntype, const char *query)
{	
	Debug(DEBUG_TRACE, socket, "CConnPool::ExecDB - TOP");

	if (conntype.size() == 0)
	{	
		Debug(DEBUG_ERROR, "CConnPool::Exec - conntype.size() == 0");
		return NULL;
	}
	else if (strlen(query) == 0)
	{
		Debug(DEBUG_ERROR, "CConnPool::Exec - query is empty");
		return NULL;
	}
	else if (strlen(query) < 5)
	{
		Debug(DEBUG_ERROR, "CConnPool::Exec - query", query);
		Debug(DEBUG_ERROR, "CConnPool::Exec - query is invalid");
		return NULL;
	}

	// Prepare/handle first 6 BYTES //
	char command[7];
	memset(command, 0, 7);
	memcpy(command, query, 6);

	//Debug(DEBUG_ERROR, socket, "CConnPool::ExecDB - command", command);
	//if (autorelease == false)
	//	Debug(DEBUG_ERROR, socket, "CConnPool::ExecDB - autorelease = false");
	//else if (autorelease == true)
	//	Debug(DEBUG_ERROR, socket, "CConnPool::ExecDB - autorelease = true");

	// SET timezone='America/Denver';

	Debug(DEBUG_TRACE, socket, "CConnPool::ExecDB - Before Statement Test");

	// These commands we don't really care about the response //
	if (((strcmp(command, "INSERT") == 0) ||
		(strcmp(command, "UPDATE") == 0) ||
		//(strcmp(command, "ALTER") == 0) ||
		//(strcmp(command, "CREATE") == 0) ||
		(strcmp(command, "DELETE ") == 0)) &&
		(autorelease == false))
	{
		Debug(DEBUG_TRACE, socket, "CConnPool::Exec - Before GetConn");

		CConn *conn = GetConn(socket, conntype); // Don't spawn anymore threads until after we find an open connection //
		if (conn == NULL)
		{
			Debug(DEBUG_ERROR, socket, "CConnPool::Exec - conn == NULL #1");
			return NULL;
		}

		conn->m_Query = query; // Make copy so memory isn't shared //
		//thread(ThreadEntry, conn).detach(); // Start thread and detach //
		pthread_t t1;
		int retval;
		Debug(DEBUG_TRACE, socket, "CConnPool::Exec - Right before pthread_create");
		if ((retval = pthread_create(&t1, NULL, &ThreadEntrySQL, (void *)conn)) != 0)
		{
			Debug(DEBUG_ERROR, socket, "CConnPool::Exec pthread_create retval", retval);
			Debug(DEBUG_ERROR, socket, "CConnPool::Exec pthread_create err", strerror(retval));
			return NULL;
			// Not hitting here, but we need to come back and handle this someday //
			// Maybe check error and retry a couple times? //
		}

		Debug(DEBUG_TRACE, socket, "CConnPool::Exec - After pthread_create");

		return (CConn *)1; // Return 1 is not NULL, but we will never use this pointer //
	}
	else //if (strcmp(command, "SELECT") == 0) // Did I miss a command? //
	{
		Debug(DEBUG_TRACE, socket, "CConnPool::ExecDB - Before WaitForThreads");

		// This is needed just in case a SELECT id is immediately after an insert //
		if (g_ConnPoolEnabled == true)
			WaitForThreads(socket);

		Debug(DEBUG_TRACE, socket, "CConnPool::ExecDB - After WaitForThreads");

		CConn *conn = GetConn(socket, conntype); 

		Debug(DEBUG_TRACE, socket, "CConnPool::ExecDB - After GetConn");

		if (conn == NULL)
		{
			Debug(DEBUG_ERROR, socket, "CConnPool::Exec - conn == NULL #2");
			return NULL;
		}
		conn->m_Query = query; // Make copy so memory isn't shared //
		if (ThreadExec(conn) == false)
		{
			Debug(DEBUG_ERROR, socket, "CConnPool::Exec - ThreadExec == false");
			Debug(DEBUG_ERROR, socket, "CConnPool::Exec - query", query);
			if (ThreadReleaseConn(conn->m_Resource) == false)
			{
				Debug(DEBUG_ERROR, socket, "CConnPool::Exec - ThreadReleaseConn Error #1. conn->m_Resource", (int)conn->m_Resource);
				return NULL;
			}
			return NULL;
		}

		Debug(DEBUG_TRACE, socket, "CConnPool::Exec AFTER ThreadExec");

		if (autorelease == true)
		{
			if (ThreadReleaseConn(conn->m_Resource) == false)
			{
				Debug(DEBUG_ERROR, socket, "CConnPool::Exec - ThreadReleaseConn Error #2. conn->m_Resource", (int)conn->m_Resource);
				return NULL;
			}
		}

		return conn; // Allow user to release database connection //
	}

	// return properly if not orphan //
	Debug(DEBUG_ERROR, "CConnPool::Exec - End of function reached. It should never get here");
	return NULL;
}

///////////////////////////////
// Allow reuse of connection //
///////////////////////////////
bool CConnPool::Exec(CConn *conn, bool autorelease, const char *sql)
{
	if (conn == NULL)
	{
		Debug(DEBUG_ERROR, "CConnPool::Exec - conn == NULL #2");
		return false;
	}
	conn->m_Query = sql; // Make copy so memory isn't shared //
	if (ThreadExec(conn) == false)
	{
		Debug(DEBUG_ERROR, "CConnPool::Exec - ThreadExec == false");
		if (ThreadReleaseConn(conn->m_Resource) == false)
			return Debug(DEBUG_ERROR, "CConnPool::Exec - Reuse conn ThreadReleaseConn #1 Error. conn->m_Resource", (int)conn->m_Resource);
		return false;
	}

	if (autorelease == true)
	{
		if (ThreadReleaseConn(conn->m_Resource) == false)
			return Debug(DEBUG_ERROR, "CConnPool::Exec - Reuse conn ThreadReleaseConn #2 Error. conn->m_Resource", (int)conn->m_Resource);
	}

	return true;
}

////////////////////////////////////
// Wait for all threads to finish //
////////////////////////////////////
int CConnPool::SocketThreadCount(int socket)
{
	int threadcount = 0;
	std::list <CConn>::iterator i;
	for (i=g_Conn.begin(); i!=g_Conn.end(); ++i)
	{
		// Wait for threads to finish //
		if (((*i).m_InUse == true) && ((*i).m_Socket == socket))
		{
			threadcount++;
		}
	}

	return threadcount;
}

////////////////////////////////////
// Wait for all threads to finish //
////////////////////////////////////
bool CConnPool::WaitForThreads(int socket)
{
	Debug(DEBUG_TRACE, "CConnPool::WaitForThreads - g_SQLTime", g_SQLTime);

	int threadcount = g_Conn.size();
	Debug(DEBUG_TRACE, "CConnPool::WaitForThreads - threadcount", threadcount);

	std::list <CConn>::iterator i;
	for (i=g_Conn.begin(); i!=g_Conn.end(); ++i)
	{
		//Debug(DEBUG_TRACE, "CConnPool::WaitForThreads - (*i).m_Resource", (*i).m_Resource);
		//if ((*i).m_Socket != 0)
		//	Debug(DEBUG_TRACE, "CConnPool::WaitForThreads - (*i).m_Socket", (*i).m_Socket);

		// Wait for threads to finish //
		while (((*i).m_InUse == true) && ((*i).m_Socket == socket))
		{
			Debug(DEBUG_TRACE, "CConnPool::WaitForThreads - (*i).m_Resource", (int)(*i).m_Resource);
			
			//Debug(DEBUG_TRACE, "CConnPool::WaitForThreads - g_Conn.size()", g_Conn.size());
			Debug(DEBUG_TRACE, "CConnPool::WaitForThreads - g_SQLTime", g_SQLTime);

			threadcount = g_Conn.size();
			char test[81];
			memset(test, 0, 81);
			memcpy(test, (*i).m_Query.c_str(), 80);
			Debug(DEBUG_TRACE, "CConnPool::WaitForThreads - m_Query", test);

			usleep(g_SQLTime); // Sleep estimated amount of time it takes for SQL to process //
		}
	}

	Debug(DEBUG_TRACE, "CConnPool::WaitForThreads - BOTTOM");
	return true;
}

//////////////////////////////////////////////////////////
// Get a count of current number of database conections //
//////////////////////////////////////////////////////////
int CConnPool::GetConnCount()
{
	return g_Conn.size();
}

/////////////////////////////
// Enable connection pools //
/////////////////////////////
void CConnPool::Enable()
{
	g_ConnPoolEnabled = true;
}

//////////////////////////////
// Disable connection pools //
//////////////////////////////
void CConnPool::Disable()
{
	g_ConnPoolEnabled = false;
}

//////////////////////////////////////
// Are the connection pools enabled //
//////////////////////////////////////
bool CConnPool::IsEnabled()
{
	return g_ConnPoolEnabled;
}

///////////////////////////////////////////////
// Get a connection from the connection pool //
///////////////////////////////////////////////
CConn *CConnPool::GetConn(int socket, string conntype)
{
	Debug(DEBUG_TRACE, "CConnPool::GetConn - conntype", conntype);

	if (conntype.size() == 0)
	{
		return NULL;
		Debug(DEBUG_ERROR, socket, "CConnPool::ConnectPool - conntype.size() == 0");
	}

	// Prevent assigning same db pool connection to two different threads // 
	// Main thread will release one at a time //
/*	if (g_ConnPoolEnabled == true)
	{
		g_ConnCount++;
		g_ConnBank[socket] = true;
		int index=0;
		while (g_ConnBank[socket] == true)
		{
			usleep(1);
			if (index > 20000) // If stuck in here for more than 2ish seconds //
			{
				//Debug(DEBUG_TRACE, socket, "CConnPool::GetConn - WHILE LOOP STUCK - socket", socket);
				index = 0;
			}
			index++;
		}
	}

	Debug(DEBUG_TRACE, "CConnPool::GetConn - Middle #1");
*/
	Debug(DEBUG_TRACE, socket, "CConnPool::GetConn - Before pthread_mutex_lock");

	// Lock all other threads from going forward //
	pthread_mutex_lock(&g_mutex);

	Debug(DEBUG_TRACE, socket, "CConnPool::GetConn - After pthread_mutex_lock");

	// Loop though all connections until we find a connection //
	int count = 0;
	while (1)
	{
		Debug(DEBUG_TRACE, socket, "CConnPool::GetConn - Before g_Conn.begin LOOP");

		std::list <CConn>::iterator i;
		for (i=g_Conn.begin(); i!=g_Conn.end(); ++i)
		{
			if (i->m_ConnType == conntype)
			{
				if (i->m_InUse == false)
				{
					i->m_InUse = true;

					Debug(DEBUG_TRACE, socket, "CConnPool::GetConn - m_Resource", (int)i->m_Resource);
					//Debug(DEBUG_TRACE, socket, "CConnPool::GetConn - m_InUse", i->m_InUse);
					
					i->m_Socket = socket;
					//Debug(DEBUG_TRACE, socket, "CConnPool::GetConn - (*i).m_Resource", i->m_Resource);
					//g_ConnCount--;
					//g_ConnSocketRelease = true;

					Debug(DEBUG_TRACE, socket, "CConnPool::GetConn - After g_ConnSocketRelease = true");

					pthread_mutex_unlock(&g_mutex); // unlock the mutex so other threads have access to connections //

					return &(*i);
				}
			}
		}

		// Allow the connection pool to be more dynamic //
		if ((m_pSettings->m_ApiConnPoolDynamic == true) &&    // Dynamic enabled //
			(g_Conn.size() < m_pSettings->m_ConnPoolCount)) // Make sure we haven't maxed out the connections //
		{
			Debug(DEBUG_TRACE, socket, "CConnPool::ConnectPool - Before conn creation");

			// Allow a connection to be made to the database //
			CConn conn;
			conn.m_pSettings = m_pSettings;
			conn.m_ConnType = conntype;
			conn.m_pgConn = PQconnectdb(m_ConnInfo.c_str());
			if (PQstatus(conn.m_pgConn) != CONNECTION_OK)
			{
				Debug(DEBUG_ERROR, "CConnPool::GetConn - Postgresql Connection Failed:");
				Debug(DEBUG_ERROR, PQerrorMessage(conn.m_pgConn));
			}
			g_ResourceCount++;
			conn.m_Resource = g_ResourceCount;		
			g_Conn.push_back(conn);

			Debug(DEBUG_TRACE, socket, "CConnPool::ConnectPool - After g_Conn.push_back");

			// Then loop back around and give the connections //
		}
		else
		{
			Debug(DEBUG_TRACE, socket, "CConnPool::ConnectPool - Before usleep");

			// No connections found, then wait and loop until one finishes //
			count++;
			usleep(100000); // Sleep to allow another connection //

			Debug(DEBUG_TRACE, socket, "CConnPool::ConnectPool - After usleep");
		}
	}

		// How do we handle maxed out connections? //

/*		Debug(DEBUG_TRACE, socket, "CConnPool::GetConn - Before usleep - g_SQLTime", g_SQLTime);
		
		usleep(g_SQLTime+10);
		count++;

		Debug(DEBUG_TRACE, socket, "CConnPool::GetConn - No available connection");

		// Retain maxsleepcount for testing purposes //
		if (count > m_MaxSleepCount)
			m_MaxSleepCount = count;

		// Are we exceeding wait time? //
		if (count >= CONNPOOL_DEADWARN)
			usleep(g_SQLTime*2); // *2 max is 60 seconds //
		else if (count >= CONNPOOL_DEADLOCK)
		{
			Debug(DEBUG_ERROR, socket, "CConnPool::GetConn - CONNPOOL_DEADLOCK", count);
			g_ConnCount--;
			g_ConnSocketRelease = true;
			exit(1);
		}

	}
*/
//	Debug(DEBUG_ERROR, socket, "CConnPool::GetConn - It should never reach here");
	//g_ConnCount--;
	//g_ConnSocketRelease = true;
	return NULL;
}

////////////////////////////////
// Do actual execution of sql //
////////////////////////////////
bool ThreadExec(CConn *conn)
{
	CDebug debug;
	//debug.Debug(DEBUG_ERROR, "ThreadExec before commands");
	//debug.Debug(DEBUG_TRACE, query);

	// Run the sql //
	//debug.Debug(DEBUG_TRACE, conn->m_Socket, "ThreadExec - conn->m_Resource", conn->m_Resource);
	//debug.Debug(DEBUG_TRACE, conn->m_Socket, "ThreadExec - conn->m_Query", conn->m_Query.c_str());

	conn->m_pgCurrentRow = 0; // Reset to 0, cause we can reuse the connections //

	// Operate in UTC, but display in their selected timezone //
	if (g_TimeZone.size() != 0)
		conn->m_Query = "SET timezone='"+g_TimeZone+"'; "+conn->m_Query;

	conn->m_pgResult = PQexec(conn->m_pgConn, conn->m_Query.c_str());
	//debug.Debug(DEBUG_DEBUG, conn->m_Socket, "ThreadExec - Before PQresultStatus");
	ExecStatusType status = PQresultStatus(conn->m_pgResult);
	//debug.Debug(DEBUG_DEBUG, conn->m_Socket, "ThreadExec - After PQresultStatus");
	
	//conn->m_Query = ""; // Prevent unnecessary memory usage //

	if (status == PGRES_COMMAND_OK) // CREATE, INSERT, DELETE
		return true;
	else if (status == PGRES_TUPLES_OK) // SELECT //
	{
		conn->m_pgRowMax = PQntuples(conn->m_pgResult);
		return true;
	}
	else if (status == PGRES_EMPTY_QUERY)
	{
		PQclear(conn->m_pgResult);
		conn->m_pgResult = 0;
		conn->m_Error = "ThreadExec - Postgres Error: PGRES_EMPTY_QUERY"; // Store the error message //
		debug.Debug(DEBUG_ERROR, conn->m_Socket, conn->m_Error.c_str());
		conn->m_Error = PQerrorMessage(conn->m_pgConn); // Store the error message //
		return debug.Debug(DEBUG_ERROR, conn->m_Socket, conn->m_Error.c_str());
	}
	else if (status == PGRES_BAD_RESPONSE)
	{
		PQclear(conn->m_pgResult);
		conn->m_pgResult = 0;
		conn->m_Error = "ThreadExec - Postgres Error: PGRES_BAD_RESPONSE";
		debug.Debug(DEBUG_ERROR, conn->m_Socket, conn->m_Error.c_str());
		conn->m_Error = PQerrorMessage(conn->m_pgConn); // Store the error message //
		return debug.Debug(DEBUG_ERROR, conn->m_Socket, conn->m_Error.c_str());
	}
	else if (status == PGRES_NONFATAL_ERROR)
	{
		PQclear(conn->m_pgResult);
		conn->m_pgResult = 0;
		conn->m_Error = "ThreadExec - Postgres Error: PGRES_NONFATAL_ERROR";
		debug.Debug(DEBUG_ERROR, conn->m_Socket, conn->m_Error.c_str());
		conn->m_Error = PQerrorMessage(conn->m_pgConn); // Store the error message //
		return debug.Debug(DEBUG_ERROR, conn->m_Socket, conn->m_Error.c_str());
	}
	else if (status == PGRES_FATAL_ERROR)
	{
		PQclear(conn->m_pgResult);
		conn->m_pgResult = 0;
		conn->m_Error = "ThreadExec - Postgres Error: PGRES_FATAL_ERROR";
		debug.Debug(DEBUG_ERROR, conn->m_Socket, conn->m_Error.c_str());
		conn->m_Error = PQerrorMessage(conn->m_pgConn); // Store the error message //
		debug.Debug(DEBUG_ERROR, conn->m_Socket, conn->m_Error.c_str());
		
		char tmpstr[250];
		memcpy(tmpstr, conn->m_Query.c_str(), 200);
		debug.Debug(DEBUG_ERROR, conn->m_Socket, tmpstr);
		//debug.Debug(DEBUG_ERROR, conn->m_Socket, conn->m_Query.c_str());
		//exit(1);
		return false;
	}

	debug.Debug(DEBUG_ERROR, "ThreadExec the end. It should never reach here");
	return false;
}

///////////////////////////////////////////////////
// Release a connection from the connection pool //
///////////////////////////////////////////////////
bool ThreadReleaseConn(int resource)
{
	CDebug debug;

	debug.Debug(DEBUG_TRACE, "CConnPool::ReleaseConn - TOP - resource", resource);
	debug.Debug(DEBUG_TRACE, "CConnPool::ReleaseConn - g_Conn.size()", (int)g_Conn.size());

	if (resource > g_MaxThreads)
		g_MaxThreads = resource;

	std::list <CConn>::iterator i;
	for (i=g_Conn.begin(); i!=g_Conn.end(); ++i)
	{
		if ((*i).m_Resource == resource)
		{
			PQclear((*i).m_pgResult);

			debug.Debug(DEBUG_TRACE, "CConnPool::ReleaseConn - InLoop - (*i).m_Resource", (int)(*i).m_Resource);

			if ((*i).m_InUse == false)
				debug.Debug(DEBUG_TRACE, "CConnPool::ReleaseConn - InLoop - (*i).m_InUse == false");
			else
				debug.Debug(DEBUG_TRACE, "CConnPool::ReleaseConn - InLoop - (*i).m_InUse == true");

			(*i).m_pgRowMax = 0;
			(*i).m_pgCurrentRow = 0;
			(*i).m_RowMap.clear(); // Empty out all previous values //
			(*i).m_Query = "";
			(*i).m_Socket = 0;
			(*i).m_pgResult = NULL;
			(*i).m_InUse = false;
			//(*i).m_Query.clear();
			//(*i).m_Error.clear();
			//debug.Debug(DEBUG_TRACE, "CConnPool::ThreadReleaseConn - Released - resource", resource);
			//usleep(1);
			return true;
		}
	}

	return debug.Debug(DEBUG_ERROR, "CConnPool::ThreadReleaseConn - Problems releasing resource", (int)resource);
}

///////////////////////////////////////////////////
// Release a connection from the connection pool //
///////////////////////////////////////////////////
void ThreadReleaseSocketConn(int socket)
{
	CDebug debug;

	debug.Debug(DEBUG_TRACE, "CConnPool::ThreadReleaseSocketConn - TOP - socket", socket);

	std::list <CConn>::iterator i;
	for (i=g_Conn.begin(); i!=g_Conn.end(); ++i)
	{
		if ((*i).m_Socket == socket)
		{
			PQclear((*i).m_pgResult);

			debug.Debug(DEBUG_TRACE, "CConnPool::ThreadReleaseSocketConn - LOOP - m_Resource", (int)(*i).m_Resource);
			
			(*i).m_pgRowMax = 0;
			(*i).m_pgCurrentRow = 0;
			(*i).m_RowMap.clear(); // Empty out all previous values //
			(*i).m_InUse = false;
			(*i).m_pgResult = NULL;
			//(*i).m_Query.clear();
			//(*i).m_Error.clear();
//			debug.Debug(DEBUG_WARN, "CConnPool::ThreadReleaseSocketConn - Released - socket", socket);
//			debug.Debug(DEBUG_WARN, "CConnPool::ThreadReleaseSocketConn - Released - resource", (*i).m_Resource);
//			debug.Debug(DEBUG_WARN, "CConnPool::ThreadReleaseSocketConn - Released - query", (*i).m_Query);
		}
	}
}