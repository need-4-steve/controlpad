#include "ezCurl.h"

#include <iostream>
#include <string>

std::string g_data; //will hold the url's contents

size_t writeCallback(char* buf, size_t size, size_t nmemb, void* up)
{ //callback must have this declaration
    //buf is a pointer to the data that curl has for us
    //size*nmemb is the size of the buffer

    int limit = size*nmemb;

    for (int c = 0; c < limit; c++)
    {
        g_data.push_back(buf[c]);
    }
    return size*nmemb; //tell curl how many bytes we handled
}

/////////////////
// Constructor //
/////////////////
CezCurl::CezCurl()
{
	m_Headers = NULL; // This is important //
}

////////////////////////
// Set a header value //
////////////////////////
bool CezCurl::SetHeader(const char *value)
{
#ifndef COMPILE_UNITED
    //std::ostringstream oss;
    m_Headers = curl_slist_append(m_Headers, value);
#endif
    return true; 
}

/////////////////////////////
// Do a curl call to a URL //
/////////////////////////////
const char *CezCurl::SendRaw(const char *url, const char *body)
{
#ifndef COMPILE_UNITED
	CURL* curl; //our curl object

    //Debug(DEBUG_ERROR, "CezCurl::SendRaw - url =", url);
    //Debug(DEBUG_ERROR, "CezCurl::SendRaw - body =", body);

    curl_global_init(CURL_GLOBAL_ALL); //pretty obvious
    curl = curl_easy_init();

    curl_easy_setopt(curl, CURLOPT_URL, url);
    curl_easy_setopt(curl, CURLOPT_HTTPHEADER, m_Headers);
    curl_easy_setopt(curl, CURLOPT_POSTFIELDS, body);
    curl_easy_setopt(curl, CURLOPT_WRITEFUNCTION, &writeCallback);
    //curl_easy_setopt(curl, CURLOPT_POST, 1L);
    //curl_easy_setopt(curl, CURLOPT_HEADER, 1L);
    //curl_easy_setopt(curl, CURLOPT_VERBOSE, 1L); //tell curl to output its progress //

    curl_easy_perform(curl);
    curl_easy_cleanup(curl);
    curl_global_cleanup();
#endif
    return g_data.c_str();
}

/////////////////////////
// Handle sending json //
/////////////////////////
const char *CezCurl::SendJson(const char *url, const char *body)
{
    // This causes problems mixing header and post //
	//SetHeader("Content-Type: application/json");
	return SendRaw(url, body);
}
