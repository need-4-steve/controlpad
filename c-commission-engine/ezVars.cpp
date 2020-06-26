#include "ezVars.h"
#include "convert.h"

#include "ezTok.h"
#include "validate.h"

#include <string.h>
#include <sstream>
#include <pthread.h>

////////////////////////////
// Clear for next process //
////////////////////////////
void CezVars::Clear()
{
	m_Json.clear();
	m_HeadVarsMap.clear(); // This is where we keep our enviroment variables //
	m_PostVarsMap.clear();
	m_Origin.clear(); // Json origin header
}

/////////////////////////////
// Parse the incoming data //
/////////////////////////////
bool CezVars::Parse(int socket, const char *data)
{
	//printf("%s\n", data);

	std::stringstream ss(data);
    std::string line;        		

	while(std::getline(ss, line, '\n'))
    {
        if ((strstr(line.c_str(), "--------------------------") == NULL) && 
        	(strstr(line.c_str(), "Content-Disposition: form-data; name=") == NULL) && 
            (line.size() > 2))
        {
            std::string str2 = ":";
            std::size_t found = line.find(str2);
			if (found!=std::string::npos)
			{

	            CezTok tok(line.c_str(), ": ");
				string key = tok.GetValue(0);
				string val = tok.GetValue(1);

			    SetHeadVar(socket, key.c_str(), val.c_str());
			}
        }
    }

    //DumpVars(socket);

    return true;
}

////////////////////////////////////////////////
// Set map hash values for all head variables //
////////////////////////////////////////////////
bool CezVars::SetHeadVar(int socket, const char *key, const char *value)
{
	// Filter out invalid variables //
	if (value == NULL)
		return false;

	//int keylen = strlen(key);
	//int valuelen = strlen(value);
	//stringstream ss;
	//ss << "CezVars::SetHeadVar - key=" << key << ", value=" << value;
	//Debug(DEBUG_ERROR, socket, ss.str().c_str());

	const char *pKey = NULL;

	//if (strlen(key) < 5)
	//	return false;
	if (memcmp(key, "HTTP_", 5) == 0) // Remove http_ from front //
		pKey = &key[5];
	else if (memcmp(key, "http_", 5) == 0) // Remove http_ from front //
		pKey = &key[5];
	else
		pKey = &key[0];

	CConvert convert;
	string convKey = convert.ToLower(pKey);
	//string convValue = convert.ToLower(value);
	string convValue = value;

	//int keylen = strlen(key);
	//int valuelen = strlen(value);
	//Debug(DEBUG_ERROR, convKey.c_str(), convValue.c_str());

	m_HeadVarsMap.insert(std::pair<std::string, std::string>(convKey, convValue));

	return true;	
}

//////////////////////////////
// Set value in our maphash //
//////////////////////////////
bool CezVars::SetPostVar(const char *key, const char *value)
{
	// Filter out invalid variables //
	if (value == NULL)
		return false;
	
	//Debug(DEBUG_DEBUG, key, value);

	CConvert convert;
	std::string convKey = convert.ToLower(key);
	std::string convValue = convert.ToLower(value);
	//m_PostVarsMap.insert(std::pair<std::string, std::string>(convKey, convValue));
	m_HeadVarsMap.insert(std::pair<std::string, std::string>(convKey, convValue));

	return true;
}

/////////////////////////////////////////
// Set the head var for ruby interface //
/////////////////////////////////////////
bool CezVars::SetVar(int socket, std::string key, std::string value)
{
	return SetHeadVar(socket, key.c_str(), value.c_str());
}

//////////////////////////////////
// Display all vars set earlier //
//////////////////////////////////
bool CezVars::DumpVars(int socket)
{
	Debug(DEBUG_DEBUG, "CezRecv::DumpVars");

	Debug(DEBUG_DEBUG, socket, "---- Head Vars ----");
	std::map <std::string, std::string>::iterator j;
	for (j=m_HeadVarsMap.begin(); j != m_HeadVarsMap.end(); ++j) 
	{
		std::string Key = j->first;
		std::string Val = m_HeadVarsMap[j->first];

		Debug(DEBUG_DEBUG, socket, Key.c_str(), Val.c_str());
		//printf("%s = %s\n", pKey, pVal);
	}
	Debug(DEBUG_DEBUG, socket, "---- End Head ----");

	//printf("Content-type: text/html\r\n");
	Debug(DEBUG_DEBUG, socket, "---- Post Vars ----");
	std::map <std::string, std::string>::iterator k;
	for (k=m_PostVarsMap.begin(); k != m_PostVarsMap.end(); ++k) 
	{
		std::string Key = k->first;
		std::string Val = m_PostVarsMap[k->first];

		Debug(DEBUG_DEBUG, socket, Key.c_str(), Val.c_str());
		//printf("%s = %s\n", pKey, pVal);
	}
	Debug(DEBUG_DEBUG, socket, "---- End Post ----");

	//exit(0);

	return true;
}

///////////////////////////////////
// Grab from out map hash values //
///////////////////////////////////
const char *CezVars::Get(const char *key)
{
	return GetHead(key);
} 

///////////////////////////////////
// Grab from out map hash values //
///////////////////////////////////
const char *CezVars::GetHead(const char *key)
{
	//Debug(DEBUG_DEBUG, "key", key);
	return m_HeadVarsMap[key].c_str(); // This is where we keep our enviroment variables //
}

///////////////////////////////////
// Grab from out map hash values //
///////////////////////////////////
const char *CezVars::GetPost(const char *key)
{
	//Debug(DEBUG_DEBUG, "key", key);
	return m_HeadVarsMap[key].c_str(); // This is where we keep our enviroment variables //
	//return m_PostVarsMap[key].c_str(); // This is where we keep our enviroment variables //
}

////////////////////////
// Clear the head var //
////////////////////////
bool CezVars::ClearHeadVar(const char *key)
{
	m_HeadVarsMap[key].clear();
	return true;
}

////////////////////
// Set the origin //
////////////////////
bool CezVars::SetOrigin(string origin)
{
	m_Origin = origin;
	return true;
}

///////////////////////////////////////////////////////
// Set the keyval cause strtok breaks on multithread //
///////////////////////////////////////////////////////
void CezVars::SetKeyVal(string line, string *key, string *val)
{
	const char *pline = line.c_str();

	bool flag = false;
	int index;
	for (index=0; index < line.length(); index++)
	{
		if (pline[index] == ':')
		{
			flag = true;
		}
		else
		{
			if (flag == false)
				*key += pline[index];

			if (flag == true)
				*val += pline[index];
		}
	}

	*key = trim(*key);
	*val = trim(*val);
}