#include "ezDaemon.h"

#include <sys/stat.h>
#include <stdio.h>
#include <stdlib.h>
#include <fcntl.h>
#include <errno.h>
#include <unistd.h>
#include <syslog.h>
#include <string.h>

/////////////////////////
// Startup daemon code //
/////////////////////////
void CezDaemon::Startup()
{ 
    // Fork off the parent process //
    m_PID = fork();
    
    if (m_PID < 0)
    {
        exit(EXIT_FAILURE);
    }

    // If we got a good PID, then we can exit the parent process. //
    if (m_PID > 0)
    {
        exit(EXIT_SUCCESS);
    }

    // Change the file mode mask //
    umask(0);
                
    // Open any logs here //        
                
    // Create a new SID for the child process //
    m_SID = setsid();
    if (m_SID < 0)
    {
        exit(EXIT_FAILURE); // Log the failure //
    }
        
    // Change the current working directory //
    if ((chdir("/")) < 0)
    {
        // Log the failure //
        exit(EXIT_FAILURE);
    }   

    // Close out the standard file descriptors //
    close(STDIN_FILENO);

    //close(STDOUT_FILENO);
    //printf("TEST\n");

    //Debug(DEBUG_TRACE, "CezDaemon::Startup - TOWARDS END #4");

    close(STDERR_FILENO);
        
    // Daemon-specific initialization goes here //
    //Debug(DEBUG_DEBUG, "CezDaemon::Startup Completed");
        
    // The Big Loop //
    //while (1)
    //{
        // Do some task here ... //
           
    //    sleep(30); /* wait 30 seconds */
    //}
    //exit(EXIT_SUCCESS);
}