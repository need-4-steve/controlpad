#ifndef _PACKETS_H
#define _PACKETS_H

///////////////
// packets.h //
///////////////

#include <string>
	
// This should go away after std::string to prevent buffer overflows //
#define MAX_PACKET_SIZE			2048	// Only allow 2048 bytes for now //

////////////////////////////////
// Class for handling packets //
////////////////////////////////
class CPacket
{
public:
	//CPacket(); // Used in FastCGI type //
	CPacket(const char *post_string);
	int Command(); // 
	//char *Value(const char *varname);

private:
	char m_Header[MAX_PACKET_SIZE];
	char m_ValBuff[MAX_PACKET_SIZE];
	int m_Command;

	char *rtrim(char *string, char whitespace);
};

#endif