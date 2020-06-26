//////////////////
// ezNetwork.cpp //
//////////////////

#include <time.h>
#include <math.h>
#include <errno.h>

#include "ezNetwork.h"
#include "ezNetError.h"
#include "commissions.h"
#include "payuser.h"
#include "CommissionEngine.h"
#include "ezEntry.h"
#include "ConnPool.h"

// TEST monitor changes //
// sudo strace -ff -s 1000 ./ceapi api

// Global variable //
extern CCommissionEngine *g_pCommEng;

// Manage Threads to release a connection one at a time //
//extern int g_ConnCount;
//extern int g_ConnCycle;
//extern bool g_ConnSocketRelease;
//extern bool g_ConnBank[MAX_CONN_LOOP+1];

extern list <CConn> g_Conn;

// Signal handler //
static void sig_io(int sig)
{
	g_exit_request = 1;
}

///////////////////////
// Set inital values //
///////////////////////
CezNetwork::CezNetwork()
{
	m_pSettings = NULL;
	m_pDB = 0;
	//Debug(DEBUG_ERROR, "CezNetwork::CezNetwork - m_NetUsersLL.size()", m_NetUsersLL.size());
}	

///////////////////////////////////////////////////////
// Start up the server and listen on specified ports //
///////////////////////////////////////////////////////
bool CezNetwork::Startup(CDb *pDB, CezSettings *psettings)
{
	CDebug::SetLogFile(psettings->m_LogFile);
	m_Recv.SetLogFile(psettings->m_LogFile);
	CDebug::Debug(DEBUG_TRACE, "CezNetwork::Startup");

	// Keep a pointer //
	m_pSettings = psettings;
	m_pDB = pDB;

	// Do the network stuff //
	struct sockaddr_in myaddr;
	int yes = 1;
	sigset_t mask;
	struct sigaction act;
	
	// Prepare for network listening //
	memset (&act, 0, sizeof(act));
	act.sa_handler = sig_io;
 
	// This server should shut down on SIGTERM. //
	if (sigaction(SIGTERM, &act, 0))
		return Debug(DEBUG_ERROR, "CezNetwork::Startup - error sigaction");
 
	sigemptyset (&mask);
	sigaddset (&mask, SIGTERM);
 
	if (sigprocmask(SIG_BLOCK, &mask, &m_orig_mask) < 0)
		return Debug(DEBUG_ERROR, "CezNetwork::Startup - error sigprocmask");
	
	// Create Listening Socket //
	m_ListenSocket = socket (AF_INET, SOCK_STREAM, 0);
	if (m_ListenSocket < 0)
	{
		Debug(DEBUG_ERROR, "CezNetwork::Startup - error ListenSocket socket");
		exit(1);
	}
	
	// Will this fix stupid connection problems? //
	//int nTimeout = 30; // 30 seconds
	//if (setsockopt(m_ListenSocket, SOL_SOCKET, SO_RCVTIMEO, &nTimeout, sizeof(int)) == -1)
	//	Debug(DEBUG_ERROR, "CezNetwork::Startup - error setsockopt SO_RCVTIMEO");

	//printf("socket() failed: %s\n", strerror(errno));

	// Allow multiple connections //
	if (setsockopt(m_ListenSocket, SOL_SOCKET, SO_REUSEADDR, &yes, sizeof(int)) == -1)
		return Debug(DEBUG_ERROR, "CezNetwork::Startup - error setsockopt SO_REUSEADDR");
	
	////////////////////////////////
	// Handle the extra SSL layer //
	////////////////////////////////
	if (m_pSettings->m_NetworkType == PROC_SSL)
	{
		if (m_SSLServ.Startup(psettings) == false)
			return Debug(DEBUG_ERROR, "CezNetwork::Startup - m_SSLServ.Startup == false");
	}
	
	// Prepare address and port to listen on //
	memset (&myaddr, 0, sizeof(myaddr));
	myaddr.sin_family = AF_INET;
	myaddr.sin_addr.s_addr = INADDR_ANY;
	myaddr.sin_port = htons(m_pSettings->m_ListenPort);
	Debug(DEBUG_INFO, "CezNetwork::Startup - Listening on port", m_pSettings->m_ListenPort);

	// Bind the listening socket //
	if (bind(m_ListenSocket, (struct sockaddr *)&myaddr, sizeof(myaddr)) < 0)
	{
		Debug(DEBUG_ERROR, "CezNetwork::Startup - error bind - Either the port is already in use or try running program as root");
		exit(1);
	}
	
	// Put socket in listen mode //
	if (listen(m_ListenSocket, MAX_CONNECTIONS) < 0)
		return Debug(DEBUG_ERROR, "CezNetwork::Startup - error listen");

	//Debug(DEBUG_ERROR, "CezNetwork::Startup - m_ListenSocket", m_ListenSocket);

	//FD_ZERO(&m_readfds); // Zero out socket tracking //
	//FD_SET(m_ListenSocket, &m_readfds); // Set the listen socket to trigger //

	return true;
}

////////////////////////////////////////////////////
// Handle pselect processing without exit request //
////////////////////////////////////////////////////
bool CezNetwork::pSelectFull()
{
	// Exit back out if Setup not run yet //
	if (m_pDB == NULL)
		return false;
	if (m_pSettings == NULL)
		return false;

	// Close flagged sockets //
	if (m_NetUsersLL.size() > 0)
	{
		std::list<CNetUser>::iterator i;
		for (i=m_NetUsersLL.begin(); i != m_NetUsersLL.end(); ++i) 
		{
			//Debug(DEBUG_DEBUG, "CezNetwork::pSelectFull - m_NetUsersLL.size()", m_NetUsersLL.size());
			if (((*i).m_Disconnect == true) && ((*i).m_InUse == false)) // SERVER INIATES a socket CLOSE //
			{
				//Debug(DEBUG_WARN, (*i).m_Socket, "CezNetwork::pSelectFull - socket m_NetUsersLL.erase() - END");

				if ((*i).m_Socket != 0)
				{
					int retval = close((*i).m_Socket);
					(*i).m_Socket = 0;
					if (retval != 0)
					{
						//Debug(DEBUG_TRACE, (*i).m_Socket, "CezNetwork::pSelectFull - m_Disconnect == true - retval", retval);
					}
					//Debug(DEBUG_TRACE, (*i).m_Socket, "CezNetwork::pSelectFull - socket DISCONNECTED");
				}
				//(*i).m_Socket = 0;				
				m_NetUsersLL.erase(i);

				return true;
			}
		}
	}

	int retval;

	// Copy the file descriptors for pselect //
	//fd_set fds;
	//memcpy(&m_readfds, &fds, sizeof(fd_set));
	//FD_SET(m_readfds, &fds);

	FD_ZERO(&m_readfds); // Zero out socket tracking //
	FD_SET(m_ListenSocket, &m_readfds); // Set the listen socket to trigger //
	FD_ZERO(&m_writefds); // Zero out socket tracking //

	// I can't figure out how to quickly copy, so we rebuild it everytime for now //
	int maxsocket = m_ListenSocket;
	std::list<CNetUser>::iterator j;
	for (j=m_NetUsersLL.begin(); j != m_NetUsersLL.end(); ++j) 
	{
		// Always be ready to receive //
		FD_SET((*j).m_Socket, &m_readfds);

		//if ((*j).m_ReadyThreadSend == true)
			FD_SET((*j).m_Socket, &m_writefds);

		// Make sure to get correct limit for pselect param //
		if ((*j).m_Socket > maxsocket)
			maxsocket = (*j).m_Socket;
	}

    // Enable timeout //
    struct timespec timeout;
    timeout.tv_sec = 1; //1; // seconds //
    timeout.tv_nsec = 0; //NET_SOCKET_TIMEOUT; // nanoseconds //

    // Wait for a network event to happen //
	if (m_NetUsersLL.size() > 0) // No timeout //
		retval = pselect(maxsocket+1, &m_readfds, &m_writefds, NULL, NULL, &m_orig_mask); // No timeout //
	else
		retval = pselect(maxsocket+1, &m_readfds, &m_writefds, NULL, &timeout, &m_orig_mask); // Timeout for cleanup //

	// Handle dynamic database connection release //
	if (retval == 0)
	{
		// Make sure no users are connected //
		if (m_NetUsersLL.size() == 0)
		{
			//Debug(DEBUG_TRACE, "CezNetwork::pSelectFull - m_ConnPool.DynamicRelease");
			m_pDB->m_ConnPool.DynamicRelease();
		}
	}

	//if (retval < 0)
	//	Debug(DEBUG_WARN, "CezNetwork::pSelectFull - #2 retval = ", retval);
	//if ((retval != 1) && (errno != EINTR) && (strcmp(strerror(errno), "Success") != 0))
	//	Debug(DEBUG_ERROR, "CezNetwork::pSelpSelectFullect - errno = ", strerror(errno));
	if (g_exit_request)
	{
		// Gracefully wait for all thread to finish here? //
		Debug(DEBUG_ERROR, "CezNetwork::pSelectFull - Gracefully wait for all thread to finish needs to be finished");	
		Debug(DEBUG_WARN, "CezNetwork::pSelectFull - g_exit_request triggered through sig_io");	
		exit(0);
	}

	if (retval < 0) // pselect Error // Disconnect from client side? //
	{
		Debug(DEBUG_WARN, "CezNetwork::pSelectFull - retval = ", retval);
		string errorstr = strerror(errno); //neterror.GetErrorInfo(errno);
		Debug(DEBUG_WARN, "CezNetwork::pSelectFull errno#", errno);
		Debug(DEBUG_WARN, "CezNetwork::pSelectFull error", errorstr.c_str());

		if (errno == 9) // Bad File Descriptor //
		{
			std::list<CNetUser>::iterator i;
			for (i=m_NetUsersLL.begin(); i != m_NetUsersLL.end(); ++i) 
			{
				if (FD_ISSET((*i).m_Socket, &m_readfds))
				{
					Debug(DEBUG_WARN, (*i).m_Socket, "CezNetwork::pSelectFull - Bad file descriptor - close()");
					(*i).m_Disconnect = true;
					//if ((*i).m_Socket > 0)
					//	close((*i).m_Socket);
				}
			}
		}

		Debug(DEBUG_WARN, "CezNetwork::pSelectFull Before Exit");
	}
	else if (FD_ISSET(m_ListenSocket, &m_readfds)) // Accept the connection //
	{	
		CNetUser NewSocket;
		struct sockaddr_in client_addr;
		socklen_t clilen;
		NewSocket.m_Socket = accept(m_ListenSocket, (struct sockaddr *)&client_addr, &clilen);
		
		//Debug(DEBUG_MESSAGE, NewSocket.m_Socket, "CezNetwork::pSelectFull - Accept Socket");
		
		if (NewSocket.m_Socket < 0)
		{
			Debug(DEBUG_ERROR, NewSocket.m_Socket, "CezNetwork::pSelectFull - NewSocket.m_Socket", NewSocket.m_Socket);
			string errorstr = strerror(errno); 
			Debug(DEBUG_WARN, NewSocket.m_Socket, "CezNetwork::pSelectFull errno#", errno);
			Debug(DEBUG_WARN, NewSocket.m_Socket, "CezNetwork::pSelectFull error", errorstr.c_str());
		}

		// Grab/Convert the ipaddress //
		char ipaddress[100];
		sprintf(ipaddress, "%d.%d.%d.%d",
		int(client_addr.sin_addr.s_addr&0xFF),
		int((client_addr.sin_addr.s_addr&0xFF00)>>8),
		int((client_addr.sin_addr.s_addr&0xFF0000)>>16),
		int((client_addr.sin_addr.s_addr&0xFF000000)>>24));

		if (clilen != 16)
		{
			int testclilen = clilen;
			Debug(DEBUG_ERROR, NewSocket.m_Socket, "CezNetwork::pSelectFull - clilen != 16");
			Debug(DEBUG_ERROR, NewSocket.m_Socket, "CezNetwork::pSelectFull - clilen", testclilen);
			Debug(DEBUG_ERROR, NewSocket.m_Socket, "CezNetwork::pSelectFull - ipaddress", ipaddress);
		}

		// Store in string format //
		NewSocket.m_IPAddress = ipaddress;

		if (NewSocket.m_Socket < 0)
		{
			return Debug(DEBUG_ERROR, NewSocket.m_Socket, "CezNetwork::pSelectFull - error NewSocket.m_Socket < 0. Problems with accept()");
		}
		else
		{
			if (m_pSettings->m_NetworkType == PROC_SSL)
			{	
				NewSocket.m_pSSL = m_SSLServ.AcceptSSL(NewSocket.m_Socket);
				if (NewSocket.m_pSSL == NULL)
				{
					m_SSLServ.CloseSSL(NewSocket.m_pSSL);
					close(NewSocket.m_Socket);
					return false; //Debug(DEBUG_ERROR, "CezNetwork::pSelect - NewSocket.m_pSSL == NULL");
				}
			}

			NewSocket.m_pSettings = m_pSettings;
			NewSocket.m_pSSLServ = &m_SSLServ;
			m_NetUsersLL.push_back(NewSocket);	
			return true;					
		}
	}
	else if (retval > 0) // Read //
	{	
		std::list<CNetUser>::iterator i;
		for (i=m_NetUsersLL.begin(); i != m_NetUsersLL.end(); ++i) 
		{
			CNetUser *iuser = (CNetUser *)&(*i);

			// Handle reading //
			if (FD_ISSET((*i).m_Socket, &m_readfds))
			{
				//Debug(DEBUG_MESSAGE, "READ - (*i).m_Socket", (*i).m_Socket);

				// Set pointers before starting thread //
				(*i).m_pSettings = m_pSettings;
				(*i).m_pSSLServ = &m_SSLServ;
				(*i).m_preadfds = &m_readfds;
				(*i).m_pNetUsersLL = &m_NetUsersLL;
				(*i).m_pRecv = &m_Recv;
				(*i).m_pDB = m_pDB;

				// Test non-thread with function below //
				ThreadSocketRecv((void *)iuser);
				//return true;
			}

			// Handle writing //
			if (FD_ISSET((*i).m_Socket, &m_writefds))
			{
				if (((*i).m_Disconnect != true) && ((*i).m_ReadyThreadSend == true)) //((*i).m_Reply.length() > 0))
				{
					//Debug(DEBUG_MESSAGE, "WRITE - (*i).m_Socket", (*i).m_Socket);

					int datalen = strlen((*i).m_Reply.c_str());
				   	int sentlen = ThreadSend(iuser, true); // Yay!! Send it immediately on this thread //
				   	if (datalen != sentlen)
					{
						stringstream ss;
						ss << "CezNetwork::pSelectFull - ThreadSend - datalen=" << datalen << ", sentlen=" << sentlen;
						Debug(DEBUG_WARN, ss.str().c_str());
					}	

					ThreadDisconnectUser(iuser);
				}
			}
		}
	}
	else if (retval == 0) // pselect timeout //
	{
		//Debug(DEBUG_ERROR, "CezNetwork::pSelectFull retval == 0");
	}

	return false;
}
	
//////////////////////////////////////////////////
// Shutdown all the socket connections properly //
//////////////////////////////////////////////////
CezNetwork::~CezNetwork()
{
	// Iterator through linked list //
	std::list<CNetUser>::iterator i;
	for (i=m_NetUsersLL.begin(); i != m_NetUsersLL.end(); ++i) 
	{
		close((*i).m_Socket); // Close all sockets down. One by one.
		m_NetUsersLL.erase(i);	
	}
}

/////////////////////////////////////////////
// Do actual reading of socket information //
/////////////////////////////////////////////
void *ThreadSocketRecv(void *param)
{
	// Set Pointers //
	CNetUser *iuser = (CNetUser *)param;

	CDebug debug;
	debug.SetLogFile(iuser->m_pSettings->m_LogFile.c_str());
	debug.Debug(DEBUG_TRACE, "void *ThreadSocketRecv - TOP");

	// Prep //
	memset(iuser->m_Buffer, 0, MAX_BUFFER_SIZE); // Empty before using //
	if ((iuser->m_pSSL == NULL) && (iuser->m_pSettings->m_NetworkType == PROC_SSL))
	{
		//debug.Debug(DEBUG_ERROR, "CezNetwork::pSelect - m_pSSL = NULL");
		return NULL;
	}
	
	// Read normal or SSL data from socket //
	int sizelen = 0;
	if (iuser->m_pSettings->m_NetworkType == PROC_SSL)
		sizelen = iuser->m_pSSLServ->ReadSSL((*iuser).m_pSSL, iuser->m_Buffer, MAX_BUFFER_SIZE);
	else
		sizelen = recv(iuser->m_Socket, iuser->m_Buffer, MAX_BUFFER_SIZE, 0);

	debug.Debug(DEBUG_TRACE, "ThreadSocketRecv - sizelen", sizelen);

	// Process Data //
	if (sizelen > 0) 
	{
		// Prevent healthcheck from filling up logs //
		string useragent = iuser->m_Vars.Get("user-agent");
		if (useragent.compare("ELB-HealthChecker/2.0") == 0)
		{
			iuser->m_Reply = "HTTP/1.1 401 Unauthorized\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"errors\":{\"status\":\"401\",\"source\":\"API\",\"title\":\"apikey is missing\",\"detail\":\"The apikey needs to be defined so authenication can be performed\"}}"; // Process the request //
  			iuser->m_Vars.Clear(); // Reset for next loop //
   			ThreadSend(iuser, false);
			return NULL;
		}

		debug.Debug(DEBUG_TRACE, "ThreadSocketRecv - Before m_Vars.Parse");

		debug.Debug(DEBUG_TRACE, "(*iuser).m_Buffer", (*iuser).m_Buffer);

		iuser->m_Vars.Parse((*iuser).m_Socket, (*iuser).m_Buffer); // Parse HEADER vars //

		debug.Debug(DEBUG_TRACE, "ThreadSocketRecv - After m_Vars.Parse");

		// Non-Thread parse of data //
		ThreadParseData(param);

		debug.Debug(DEBUG_TRACE, "ThreadSocketRecv - After ThreadParseData");

		return NULL;
	}
	else // Close Socket //
	{
		//FD_CLR(iuser->m_Socket, iuser->m_preadfds);
		if (iuser->m_pSettings->m_NetworkType == PROC_SSL)
			iuser->m_pSSLServ->CloseSSL(iuser->m_pSSL);
		
		// CLIENT INITIATES a socket CLOSE //
		iuser->m_Disconnect = true; // This will complete in thread above. Takes thread InUse into account //
		return NULL;
	}

	// It should never get here //
	return NULL;
}

////////////////////////
// Recv Incoming Data //
////////////////////////
void *ThreadParseData(void *param)
{
	// Set pointer //
	CNetUser *iuser = (CNetUser *)param;

	CDebug debug;
	debug.SetLogFile(iuser->m_pSettings->m_LogFile.c_str());
	debug.Debug(DEBUG_NETWORK_IN, iuser->m_Socket, iuser->m_Buffer);
	debug.Debug(DEBUG_TRACE, iuser->m_Socket, "ThreadParseData - iuser->m_Socket", iuser->m_Socket);

	// Disconnect if length is > MAX_BUFFER_SIZE //
	if (strlen((*iuser).m_Buffer) > MAX_BUFFER_SIZE)
	{
		debug.Debug(DEBUG_WARN, iuser->m_Socket, "ThreadParseData - m_Buffer > MAX_BUFFER_SIZE");
		close((*iuser).m_Socket); 
		return NULL;
	}

	// Disconnect asap if not POST //
	//if (memcmp(iuser->m_Buffer, (const void *)"POST", 4) != 0)
	//	close(iuser->m_Socket);

	// Set the IPaddress var //
	//debug.Debug(DEBUG_DEBUG, iuser->m_Socket, "ThreadParseData - remote_addr", (*iuser).m_IPAddress.c_str());
	iuser->m_Vars.m_RemoteAddr = (*iuser).m_IPAddress.c_str();

	// Handle CORS access control method(s) //
    string request_methods = iuser->m_Vars.Get("access-control-request-methods"); 
    if (request_methods.size() == 0)
    	request_methods = iuser->m_Vars.Get("access-control-request-method");
    
    string contenttype = iuser->m_Vars.Get("content-type"); 
	string origin = iuser->m_Vars.Get("origin");
	(*iuser).m_Vars.SetOrigin(origin); 

	debug.Debug(DEBUG_TRACE, iuser->m_Socket, "ThreadParseData - Before CORS");
	debug.Debug(DEBUG_TRACE, iuser->m_Socket, "ThreadParseData - request_methods", request_methods.c_str());

	// Give CORS access //
	if (request_methods.size() != 0)
    {
    	debug.Debug(DEBUG_TRACE, "ThreadParseData - compare(post) == 0");
    	iuser->m_Vars.ClearHeadVar("access-control-request-method");
        string access_headers = iuser->m_Vars.Get("access-control-request-headers"); 
        string request_origin = iuser->m_Vars.Get("access-control-request-origin");             	
	    CezJson json;
        iuser->m_Reply = json.SetCORSResp(request_origin.c_str(), request_methods.c_str(), access_headers.c_str());

        //Debug(DEBUG_DEBUG, "CezNetwork::RecvData - reply", reply.c_str());
        if (ThreadSend(iuser, true) == -1)
        {
        	string errorstr = strerror(errno); 
			debug.Debug(DEBUG_WARN, "CezNetwork::pSelectFull errno#", errno);
			debug.Debug(DEBUG_WARN, "CezNetwork::pSelectFull error", errorstr.c_str());
        }

        ThreadDisconnectUser(iuser);
        return NULL;
	}
	else if (contenttype.compare("multipart/form-data; boundary=") == 40)
	{	
		//debug.Debug(DEBUG_TRACE, "ThreadParseData - compare(multipart/form-data) == 0");
		iuser->m_Vars.ClearHeadVar("content-type"); // Prevent coming back here //
		iuser->m_Reply = "HTTP/1.1 100 Continue\r\n\r\n"; // Appropriate response //
		if (ThreadSend(iuser, true) == -1)
        {
        	string errorstr = strerror(errno); 
			debug.Debug(DEBUG_WARN, "CezNetwork::pSelectFull errno#", errno);
			debug.Debug(DEBUG_WARN, "CezNetwork::pSelectFull error", errorstr.c_str());
        }
	}
    else // Handle regular //
    {
    	iuser->m_InUse = true; // Avoid segmentation fault //

    	debug.Debug(DEBUG_TRACE, iuser->m_Socket, "ThreadParseData - Before pthread_create");

    	// Create a thead for processing data //
		pthread_t t1;
		int retval;
		if ((retval = pthread_create(&t1, NULL, &ThreadCoreParseData, param)) != 0)
			debug.Debug(DEBUG_ERROR, "ThreadParseData - pthread_create - retval", retval);
	}

	/*
	7 - 2017-4-8 19:24:41 - GET / HTTP/1.1
	Host: 172.31.23.180:8080
	Connection: close
	User-Agent: ELB-HealthChecker/2.0
	*/

	return NULL; //true;
}

////////////////////////////////////////////
// Handle the bulk thread of parsing data //
////////////////////////////////////////////
void *ThreadCoreParseData(void *param)
{
	// Set pointer //
	CNetUser *iuser = (CNetUser *)param;

	// Prepare debug //
	CDebug debug;
	debug.SetLogFile(iuser->m_pSettings->m_LogFile.c_str());

	//debug.Debug(DEBUG_TRACE, "ThreadParseData - Right Before Process");
    iuser->m_Reply = iuser->m_pRecv->Process(iuser->m_Socket, iuser->m_pDB, &iuser->m_Vars); // Process the request //
  	//debug.Debug(DEBUG_MESSAGE, iuser->m_Socket, "ThreadCoreParseData - After Process");
  	iuser->m_Vars.Clear(); // Reset for next loop //
  	
  	if (iuser->m_Disconnect == false)
  		iuser->m_ReadyThreadSend = true;
  	else
  		iuser->m_InUse = false;

   	pthread_detach(pthread_self());
   	pthread_exit(NULL);
   	return NULL;
}

///////////////
// Send data //
///////////////
int ThreadSend(CNetUser *iuser, bool shownetworkout) // Need for avoid EB health check //
{
	CDebug debug;
	debug.SetLogFile(iuser->m_pSettings->m_LogFile.c_str());
	debug.Debug(DEBUG_TRACE, (*iuser).m_Socket, "ThreadSend - TOP");

	if (iuser->m_Socket <= 0)
	{
		debug.Debug(DEBUG_ERROR, (*iuser).m_Socket, "ThreadSend - iuser->m_Socket <= 0");
		return -1; // -1 for error //
	}
	
	if (shownetworkout == true)
		debug.Debug(DEBUG_NETWORK_OUT, iuser->m_Socket, (*iuser).m_Reply.c_str());

	if ((*iuser).m_pSettings->m_NetworkType == PROC_SSL)
	{
		return (*iuser).m_pSSLServ->WriteSSL((*iuser).m_pSSL, (*iuser).m_Reply.c_str(), strlen((*iuser).m_Reply.c_str()));
	}
	else
	{	
		debug.Debug(DEBUG_DEBUG, "ThreadSend - Before - (*iuser).m_Socket", (*iuser).m_Socket);
		if ((*iuser).m_Socket != 0)
		{
			debug.Debug(DEBUG_DEBUG, "ThreadSend - Before - send()");
			int retval = send((*iuser).m_Socket, (*iuser).m_Reply.c_str(), strlen((*iuser).m_Reply.c_str()), 0);
			if (retval < 0)
			{
				string errorstr = strerror(errno); //neterror.GetErrorInfo(errno);
				debug.Debug(DEBUG_WARN, "ThreadSend errno#", errno);
				debug.Debug(DEBUG_WARN, "ThreadSend error", errorstr.c_str());
			}
			return retval;
		}
	}

	return 0;
}

///////////////////////////////////////////////////
// close socket and remove user from linked list //
///////////////////////////////////////////////////
bool ThreadDisconnectUser(CNetUser *iuser)
{
	CDebug debug;
	debug.SetLogFile(iuser->m_pSettings->m_LogFile.c_str());

	iuser->m_Reply = ""; // Prevent unnecessary memory usage //
	iuser->m_ReadyThreadSend = false;
	iuser->m_Disconnect = true; //true;
	iuser->m_InUse = false; //true; //false;

	// Shutdown the connection quickly //
	if (g_pCommEng != NULL)
	{
		list<CNetUser>::iterator i;
		for (i=g_pCommEng->m_Network.m_NetUsersLL.begin(); i != g_pCommEng->m_Network.m_NetUsersLL.end(); ++i) 
		{
			//Debug(DEBUG_DEBUG, "CezNetwork::pSelectFull - m_NetUsersLL.size()", m_NetUsersLL.size());
			if ((*i).m_Socket == iuser->m_Socket) // SERVER INIATES a socket CLOSE //
			{
				int retval = close(iuser->m_Socket);
				debug.Debug(DEBUG_TRACE, iuser->m_Socket, "ThreadDisconnectUser - socket close()");
				iuser->m_Socket = 0;
				if (retval != 0)
					debug.Debug(DEBUG_ERROR, iuser->m_Socket, "ThreadDisconnectUser - m_Disconnect == true - retval", retval);
				
				//iuser->m_Socket = 0;				
//				g_pCommEng->m_Network.m_NetUsersLL.erase(i);
				//debug.Debug(DEBUG_MESSAGE, iuser->m_Socket, "ThreadDisconnectUser - THE END");
				return true;
			}
		}
	}

	//debug.Debug(DEBUG_ERROR, iuser->m_Socket, "ThreadDisconnectUser - iuser->m_InUse", iuser->m_InUse);
	return false;
}
