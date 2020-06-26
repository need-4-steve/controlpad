#ifndef _EZVARS_H
#define _EZVARS_H

//////////////
// ezVars.h //
//////////////

#include "ezJson.h"
#include "debug.h"

#include <string>
#include <map>

using namespace std;

/////////////////////////////////////
// Manage incoming variable values //
/////////////////////////////////////
class CezVars : public CezJson, CDebug
{
public:
	void Clear();
	bool Parse(int socket, const char *data); // Parse the incoming data //
	bool SetPostVar(const char *key, const char *value); // Set map hash values for all post variables //
	bool SetHeadVar(int socket, const char *key, const char *value); // Set map hash values for all head variables //
	bool SetVar(int socket, string key, string value);
	bool DumpVars(int socket); // Display all vars set earlier //
	const char *Get(const char *key); // Grab from out map hash values //

	bool ClearHeadVar(const char *key);
	bool SetOrigin(string origin);

	void SetKeyVal(string line, string *key, string *val);

	string m_RemoteAddr;
	string m_Origin;

private:
	const char *GetHead(const char *key); // Grab from out map hash values //
	const char *GetPost(const char *key); // Grab from out map hash values //

	map <string, string> m_HeadVarsMap; // This is where we keep our enviroment variables //
	map <string, string> m_PostVarsMap; // This is where we keep our enviroment variables //
	string m_Json;
};

#endif