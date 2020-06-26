#ifndef _NETUSER_H
#define _NETUSER_H

#include <openssl/ssl.h>
#include "ezRecv.h"
#include "ezVars.h"
#include "db.h"
#include "ezSSL.h"

#define MAX_BUFFER_SIZE		4096	// Only allow 2048 bytes for now //

/////////////////////////////
// Retain User Information //
/////////////////////////////
class CNetUser
{
public:
	CNetUser();

	bool m_InUse;
	bool m_ReadyThreadSend;

	std::string m_IPAddress; // Currently handle IPV4, but prepared for IPV6 //
	int m_Socket;
	SSL *m_pSSL;

	string m_Reply; // The reply buffer //
	//CezRecv m_Recv;

	CezVars m_Vars;

	char m_Buffer[MAX_BUFFER_SIZE];

	CezSettings *m_pSettings;
	CezSSL *m_pSSLServ;
	fd_set *m_preadfds;
	list <CNetUser> *m_pNetUsersLL;
	CezRecv *m_pRecv;
	CDb *m_pDB; // Allow to point to Live or Sim database //

	bool m_Disconnect; // Allow flagging close from another thread //
	int m_DisconnectCount;
};

#endif