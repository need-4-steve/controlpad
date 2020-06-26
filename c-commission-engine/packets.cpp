#include "packets.h"

#include <stdio.h>
#include <string.h>
#include <stdlib.h>

#include <iostream>
#include <sstream>
#include <map>
#include <istream>

////////////////////////////////////////
// Parse immedately on initialization //
////////////////////////////////////////
CPacket::CPacket(const char *post_string)
{
	std::string type, command, line = post_string;
	std::stringstream ss(line);
	ss >> type;
	ss >> command;
	if (type == "POST")
	{
		//command.erase(0, 1); // Erase the / character
		//m_Command = CheckCommands(command.c_str()); // Store the command //
	}

	// Retain the header to parse for var values later //
	sprintf(m_Header, "%s", post_string);
}

//////////////////////////////
// Return the found command //
//////////////////////////////
int CPacket::Command()
{
	return m_Command;
} 


/////////////////////////////////
// Get the value of a variable //
/////////////////////////////////
//char *CPacket::Value(const char *varname)
//{
//	std::istringstream resp(m_Header);
//	std::string line;
//	while (std::getline(resp, line) && line != "\r")
//	{
//		char needle[MAX_PACKET_SIZE];
//		sprintf(needle, "%s:", varname);
//		std::size_t found = line.find(needle);
//		if (found!=std::string::npos)
//		{
//			char *pch;
//			char *pstr = (char *)line.c_str();			
//			pch = strtok(pstr, ":");
//			while (pch != NULL)
//			{
//				pch = strtok(NULL, ":");
//				pch = &pch[1]; // Skip past the space //
//				sprintf(m_ValBuff, "%s", pch);
//				rtrim(m_ValBuff, ' '); // Get rid of the whitespace //
//				return m_ValBuff;
//			}
//		}
//	}
//
//	// Return an empty value //
//	memset(m_ValBuff, 0, MAX_PACKET_SIZE);
//	return m_ValBuff;
//}


////////////////////
// Trim whitepace //
////////////////////
char *CPacket::rtrim(char* string, char whitespace)
{
    char* original = string + strlen(string);
    while (*--original == whitespace)
    {
    	// Fix compile warning //
    }
    	*(original + 1) = '\0';
    return string;
}