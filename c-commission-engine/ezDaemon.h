#ifndef _EZDAEMON_H
#define _EZDAEMON_H

#include <sys/types.h>
#include "debug.h"

////////////////////////////
// Handle SSL connecitons //
////////////////////////////
class CezDaemon : public CDebug
{
public:
	void Startup(); // Startup daemon code //

	pid_t m_PID; // Process ID //
	pid_t m_SID; // Session ID //
};

#endif