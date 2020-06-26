
#include "netuser.h"

/////////////////
// Constructor //
/////////////////
CNetUser::CNetUser()
{
	m_InUse = false;
	m_ReadyThreadSend = false;
	m_Socket = 0;
	m_pSSL =  NULL;

	m_pSettings = NULL;
	m_pSSLServ = NULL;
	m_preadfds = NULL;
	m_pNetUsersLL = NULL;
	m_pRecv = NULL;
	m_pDB = NULL;
	m_Disconnect = false;

	m_DisconnectCount = 0;

	memset(m_Buffer, 0, MAX_BUFFER_SIZE); // Empty before using //
}
