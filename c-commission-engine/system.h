#ifndef _SYSTEM_H
#define _SYSTEM_H

#include "debug.h"
#include "packets.h"

#include <string>

////////////////////////
// System Information //
////////////////////////
class CSystem : CDebug
{
public:
	CSystem();
	//bool Parse(CPacket *pPkt);

	// Data needed for calculaitons //
	int m_SystemID;
	int m_CommType; // 1 of 3 Commission types //
	std::string m_SystemName;
};

#endif