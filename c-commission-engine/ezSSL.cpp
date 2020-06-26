#include "Compile.h"

#include <errno.h>
#include <unistd.h>
#include <string.h>
#include <arpa/inet.h>
#include <sys/socket.h>
#include <netinet/in.h>
#include <resolv.h>
#include <sys/types.h>

#include <sstream>

#include "ezSSL.h"
#include "ezJson.h"


CezSSL::CezSSL()
{
    m_pSettings = NULL;

    // Internal buffers //
    m_pCTX = NULL;
    m_pSSL = NULL;
}

////////////////////////////
// Handle initial startup //
////////////////////////////
bool CezSSL::Startup(CezSettings *psettings)
{
#ifdef COMPILE_SSL
    m_pSettings = psettings;

    SSL_library_init();
    m_pCTX = InitServerCTX(); // initialize SSL //
    if (LoadCertificates(m_pCTX, m_pSettings->m_CertFile.c_str(), m_pSettings->m_KeyFile.c_str()) == false)
        return false;
#endif
    return true;
}

///////////////////////////////
// Accept the SSL connection //
///////////////////////////////
SSL *CezSSL::AcceptSSL(int socket)
{
#ifdef COMPILE_SSL
    m_pSSL = SSL_new(m_pCTX); // get new SSL state with context //
    if (SSL_set_fd(m_pSSL, socket) == 0) // set connection socket to SSL state //
    {
         Debug(DEBUG_ERROR, "CezSSL::AcceptSSL - SSL_set_fd == 0");
         return NULL;
    }
    
    int retval = 0;
    if ((retval = SSL_accept(m_pSSL)) != 1) // do SSL-protocol accept //
    {
        //if (retval == -1)
        //    Debug(DEBUG_ERROR, "CezSSL::AcceptSSL - retval == -1. handshake was not successful because a fatal error occurred");

        //if (retval == 0)
        //    Debug(DEBUG_ERROR, "CezSSL::AcceptSSL - retval == 0");

        //ERR_print_errors_fp(stderr);
        //Debug(DEBUG_ERROR, "CezSSL::AcceptSSL - SSL_accept error");
        return NULL;
    }
#endif

    return m_pSSL;
}

///////////////////
// Read SSL data //
///////////////////
int CezSSL::ReadSSL(SSL *ssl, char *buffer, int buflen)
{
#ifdef COMPILE_SSL
    return SSL_read(ssl, buffer, buflen); // get request //
#else
    return 0;
#endif
}

////////////////////
// Write SSL data //
////////////////////
int CezSSL::WriteSSL(SSL *ssl, const char *buffer, int buflen)
{
#ifdef COMPILE_SSL
    return SSL_write(ssl, buffer, buflen); // send reply //
#else
    return 0;
#endif
}

////////////////////
// Close down SSL //
////////////////////
void CezSSL::CloseSSL(SSL *ssl)
{
#ifdef COMPILE_SSL
    SSL_free(ssl); // release SSL state //  
#endif
}

/*
/////////////////////////////
// Open port for listening //
/////////////////////////////
int CezSSL::OpenListener(int port)
{   int sd;
    struct sockaddr_in addr;

    sd = socket(PF_INET, SOCK_STREAM, 0);
    bzero(&addr, sizeof(addr));
    addr.sin_family = AF_INET;
    addr.sin_port = htons(port);
    addr.sin_addr.s_addr = INADDR_ANY;
    if ( bind(sd, (struct sockaddr*)&addr, sizeof(addr)) != 0 )
    {
        Debug(DEBUG_ERROR, "CezSSL::OpenListener - can't bind port");
        abort();
    }
    if ( listen(sd, 10) != 0 )
    {
    	Debug(DEBUG_ERROR, "CezSSL::OpenListener - Can't configure listening port");
        abort();
    }
    return sd;
}
*/

///////////////////////////////////////////////
// Do initalizaton of the type of encryption //
///////////////////////////////////////////////
SSL_CTX *CezSSL::InitServerCTX(void)
{  
#ifdef COMPILE_SSL 
    OpenSSL_add_all_algorithms();  // load & register all cryptos, etc. //
    SSL_load_error_strings();   // load all error messages //
    //method = SSLv3_server_method();  // SSLv3 is insecure //
    const SSL_METHOD *method = TLSv1_2_method(); // More secure method //
    if (method == NULL)
    {
        Debug(DEBUG_ERROR, "CezSSL::InitServerCTX - method == NULL");
        return NULL;

    }
    m_pCTX = SSL_CTX_new(method);   // create new context from method //
    if (m_pCTX == NULL)
    {
    	Debug(DEBUG_ERROR, "CezSSL::InitServerCTX - m_pCTX == NULL");
        return NULL;
    }
#endif
    return m_pCTX;
}

////////////////////////////////////////////////////
// Load the certificates that we are going to use //
////////////////////////////////////////////////////
bool CezSSL::LoadCertificates(SSL_CTX* ctx, const char *CertFile, const char *KeyFile)
{
#ifdef COMPILE_SSL
    if (SSL_CTX_load_verify_locations(ctx, CertFile, KeyFile) != 1)
    {
    	Debug(DEBUG_ERROR, "CezSSL::LoadCertificates - verify_locations error");
        Debug(DEBUG_ERROR, "CezSSL::LoadCertificates - CertFile", CertFile);
        Debug(DEBUG_ERROR, "CezSSL::LoadCertificates - KeyFile", KeyFile);
        //ERR_print_errors_fp(stderr);
        return false;
    }
   
    if (SSL_CTX_set_default_verify_paths(ctx) != 1)
    {
    	Debug(DEBUG_ERROR, "CezSSL::LoadCertificates - verify_paths error");
        //ERR_print_errors_fp(stderr);
        return false;
    }

    // set the local certificate from CertFile //
    if (SSL_CTX_use_certificate_file(ctx, CertFile, SSL_FILETYPE_PEM) <= 0)
    {
        Debug(DEBUG_ERROR, "CezSSL::LoadCertificates - certificate error");
        //ERR_print_errors_fp(stderr);
        return false;
    }

    // set the private key from KeyFile (may be the same as CertFile) //
    if (SSL_CTX_use_PrivateKey_file(ctx, KeyFile, SSL_FILETYPE_PEM) <= 0)
    {
        Debug(DEBUG_ERROR, "CezSSL::LoadCertificates - PrivateKey error");
        //ERR_print_errors_fp(stderr);
        return false;
    }

    // verify private key //
    if (!SSL_CTX_check_private_key(ctx))
    {
        Debug(DEBUG_ERROR, "CezSSL::LoadCertificates - Private key does not match the public certificate");
        return false;
    }

    //New lines - Force the client-side have a certificate
//  SSL_CTX_set_verify(ctx, SSL_VERIFY_PEER | SSL_VERIFY_FAIL_IF_NO_PEER_CERT, NULL); // Forcing client to have cert prevents working  
    SSL_CTX_set_verify_depth(ctx, 4);
#endif
    return true;
}

///////////////////////////////////////
// Show any client side certificates //
///////////////////////////////////////
// This is only needed for troubleshooting //
bool CezSSL::ShowCerts(SSL *ssl)
{   
#ifdef COMPILE_SSL
    X509 *cert;
    char *line;

    cert = SSL_get_peer_certificate(ssl); // Get certificates (if available) //
    if ( cert != NULL )
    {
        Debug(DEBUG_TRACE, "CezSSL::ShowCerts - Server certificates:");
        line = X509_NAME_oneline(X509_get_subject_name(cert), 0, 0);
        Debug(DEBUG_TRACE, "CezSSL::ShowCerts - Subject:", line);
        free(line);
        line = X509_NAME_oneline(X509_get_issuer_name(cert), 0, 0);
        Debug(DEBUG_TRACE, "CezSSL::ShowCerts - Issuer:", line);
        free(line);
        X509_free(cert);
        return true;
    }
    else
    {
        Debug(DEBUG_TRACE, "CezSSL::ShowCerts - No certificates:");
        return false;
    }
#else
    return false;
#endif
}

/*
/////////////////////////////////
// Process reading and writing //
/////////////////////////////////
void CezSSL::Servlet(SSL* ssl) // Serve the connection -- threadable //
{   
    char buf[1024];
    char reply[1024];
    int sd, bytes;
    const char* HTMLecho="<html><body><pre>%s</pre></body></html>\n\n";

    if (SSL_accept(ssl) == FAIL)     // do SSL-protocol accept //
    {
        //ERR_print_errors_fp(stderr);
        Debug(DEBUG_ERROR, "CezSSL::Servlet - SSL_accept error");
    }
    else
    {
        //ShowCerts(ssl); // get any certificates //
        bytes = SSL_read(ssl, buf, sizeof(buf)); // get request //
        if (bytes > 0)
        {
            buf[bytes] = 0;
            Debug(DEBUG_NETWORK_IN, buf);

            m_Recv.SocketParse(buf);

            // Handle AngularJS cross site script problem //
            std::string reply;
            std::string access_control = m_Recv.GetHeadVar("access-control-request-method"); 
	        if (access_control.compare("post") == 0)
            {
                std::string access_headers = m_Recv.GetHeadVar("access-control-request-headers"); 
                std::string origin = m_Recv.GetHeadVar("origin");             	
		        CezJson json;
                reply = json.SetAngResp(access_headers.c_str(), origin.c_str());
                Debug(DEBUG_DEBUG, "Servlet - reply", reply.c_str());
	        }
            else
            {
                // Parse the vars and do blackbox processing of recv'd packlet //
    		    reply = m_Recv.Process();
    		    m_Recv.Clear(); // Reset for next loop //
            }

            // Prepare for debugging just in case //
            Debug(DEBUG_NETWORK_OUT, reply.c_str());

	        // Write the json response //
            SSL_write(ssl, reply.c_str(), strlen(reply.c_str())); // send reply //
        }
        else
        {
            Debug(DEBUG_ERROR, "CezSSL::Servlet - print_errors_fp");
            //ERR_print_errors_fp(stderr);
        }
    }
    sd = SSL_get_fd(ssl); // get socket connection //
    SSL_free(ssl); // release SSL state //
    close(sd); // close connection //
}

///////////////////////////////////////////
// The actual processing of packets loop //
///////////////////////////////////////////
int CezSSL::Process(CezSettings *psettings)
{   
    m_pSettings = psettings;

    SSL_CTX *ctx;
    int server;

    SSL_library_init();
    ctx = InitServerCTX(); // initialize SSL //
    LoadCertificates(ctx, m_pSettings->m_CertFile.c_str(), m_pSettings->m_KeyFile.c_str()); // load certs //
    server = OpenListener(m_pSettings->m_ListenPort); // create server socket //

    // Startup Database for Recv'ing // 
    m_Recv.Startup(psettings);

    while (1)
    {   
        struct sockaddr_in addr;
        socklen_t len = sizeof(addr);
        SSL *ssl;

        int client = accept(server, (struct sockaddr*)&addr, &len);  // accept connection as usual //

        // Display the connection //
        std::stringstream connection;
        connection << "Connection: " << inet_ntoa(addr.sin_addr) << ":" << ntohs(addr.sin_port);
        std::string conn = connection.str();
        Debug(DEBUG_DEBUG, "CezSSL::Process - accept", conn.c_str());

        ssl = SSL_new(ctx); // get new SSL state with context //
        SSL_set_fd(ssl, client); // set connection socket to SSL state //
        Servlet(ssl); // service connection //
    }
    close(server); // close server socket //
    SSL_CTX_free(ctx); // release context //
}
*/