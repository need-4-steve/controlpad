#ifndef _EZCURL_H
#define _EZCURL_H

#include "Compile.h"
#include "debug.h"
#include <string>

#include <curl/curl.h>

///////////////////////////////////////////////
// Handle all of our encryption and API keys //
///////////////////////////////////////////////
class CezCurl : CDebug
{
public:
	CezCurl();
	bool SetHeader(const char *value);
	const char *SendRaw(const char *url, const char *body);
	const char *SendJson(const char *url, const char *body);

	struct curl_slist *m_Headers;
};

// For _EZCURL_H
#endif