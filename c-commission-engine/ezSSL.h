#ifndef _EZSSL_H
#define _EZSSL_H

#include <openssl/ssl.h>
#include <openssl/err.h>

#include "ezRecv.h"
#include "debug.h"

////////////////////////////
// Handle SSL connecitons //
////////////////////////////
class CezSSL : public CDebug
{
public:
	CezSSL();
	//int Process(CezSettings *psettings);

	bool Startup(CezSettings *psettings);
	SSL *AcceptSSL(int socket);
	int ReadSSL(SSL *ssl, char *buffer, int buflen);
	int WriteSSL(SSL *ssl, const char *buffer, int buflen);
	void CloseSSL(SSL *ssl);

private:
	//int OpenListener(int port);
	SSL_CTX* InitServerCTX(void);
	bool LoadCertificates(SSL_CTX* ctx, const char *CertFile, const char *KeyFile);
	bool ShowCerts(SSL *ssl);
	//void Servlet(SSL* ssl);

	CezRecv m_Recv;
	CezSettings *m_pSettings;

	// Internal buffers //
	SSL_CTX *m_pCTX;
	SSL *m_pSSL;
};

#endif