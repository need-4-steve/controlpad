#include "system.h"
#include "commissions.h"

#include <memory.h>
#include <stdio.h>

///////////////////////
// Set inital values //
///////////////////////
CSystem::CSystem()
{
	m_SystemID = 0;
	m_CommType = 0; 
}

/*
////////////////////////////////////
// Parse packet and assign values //
////////////////////////////////////
bool CSystem::Parse(CPacket *pPkt)
{
	// Handle variables //
	m_SystemName = pPkt->Value("system");
	m_SystemName = trim(m_SystemName);
	std::string commtype = pPkt->Value("commtype");
	commtype = trim(commtype);

	if (commtype == "breakaway")
		m_CommType = COMMRULE_BREAKAWAY;
	else if (commtype == "hybriduni")
		m_CommType = COMMRULE_HYBRIDUNI;
	else if (commtype == "binary")
		m_CommType = COMMRULE_BINARY;
	
	return true;
}
*/