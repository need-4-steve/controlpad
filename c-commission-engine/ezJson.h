#ifndef _EZJSON_H
#define _EZJSON_H

//#include "ezRecv.h"

#include <string>
#include <map>

using namespace std;

/////////////////
// Handle Json //
/////////////////
class CezJson
{
public:
    CezJson(); // Fill Map Hash Error reference //
    const char *SetJson(int status, const char *json);
    //const char *SetJson(int status, std::stringstream& json_str);
	  const char *SetJson(int status, basic_ostream<char> &json_str);
    const char *SetError(int status, const char *source, const char *title, const char *detail);
    const char *SetCORSResp(const char *origin, const char *request_methods, const char *access_headers);

    bool SetOrigin(string origin);

	  std::string m_Origin;

private:
	  std::string m_Json; // The internal Json buffer //

	  std::map <int, const char *> m_StatusMap;
};

#endif

/*

//http://jsonapi.org/examples/

// single error //

HTTP/1.1 422 Unprocessable Entity
Content-Type: application/vnd.api+json

{
  "errors": [
    {
      "status": "422",
      "source": { "pointer": "/data/attributes/first-name" },
      "title":  "Invalid Attribute",
      "detail": "First name must contain at least three characters."
    }
  ]
}

// Multiple errors //

HTTP/1.1 400 Bad Request
Content-Type: application/vnd.api+json

{
  "errors": [
    {
      "status": "403",
      "source": { "pointer": "/data/attributes/secret-powers" },
      "detail": "Editing secret powers is not authorized on Sundays."
    },
    {
      "status": "422",
      "source": { "pointer": "/data/attributes/volume" },
      "detail": "Volume does not, in fact, go to 11."
    },
    {
      "status": "500",
      "source": { "pointer": "/data/attributes/reputation" },
      "title": "The backend responded with an error",
      "detail": "Reputation service not responding after three requests."
    }
  ]
}
*/
