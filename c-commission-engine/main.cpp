/////////////////////////////////////////////////////////////
// Written by West Anderson								   //
// WARNING!!!											   //
// The following needs to be installed to connect to MySQL //
// apt-get install libmysqlclient-dev			
/////////////////////////////////////////////////////////////

//#include <stdio.h>
//#include <unistd.h>
//#include <stdlib.h>

#include <string>
#include <iostream>

#include "Compile.h"
#include "CommissionEngine.h"

#include "ezJson.h"
#include "ezRecv.h"

///////////////////////////
// Handle enable testing //
///////////////////////////
#ifdef COMPILE_TESTS
#include <gtest/gtest.h>
#include "Tests.h"
int main(int argc, char *argv[])
{
	//CCommissionEngine CommEng;
	//CommEng.Test();

	testing::InitGoogleTest(&argc, argv);
    return RUN_ALL_TESTS();
}
#else

///////////////////
// Do actual API //
///////////////////
int main(int argc, char *argv[])
{
	CCommissionEngine CommEng;

	// Check the command passed in //
	if (argc == 1)
		return CommEng.Debug(DEBUG_ERROR, "main - An ini file needs to be supplied");
	
	string tmphelp = argv[1];
	if (tmphelp == "help")
	{
		std::cout << "\n";
		CommEng.Help(ANSI_COLOR_MAGENTA, "Help for Commission Engine API", "", "");
		CommEng.Help(ANSI_COLOR_YELLOW, "ini.file", ANSI_COLOR_MAGENTA, "Example: \"./ceapi live.ce{.ini} (command)\". This is required for all commands\n");
		CommEng.Help(ANSI_COLOR_MAGENTA, "commands:", ANSI_COLOR_MAGENTA, "Define an ini file then run a command");
		CommEng.Help(ANSI_COLOR_YELLOW, "api", ANSI_COLOR_MAGENTA, "startup commission engine in api mode");
		CommEng.Help(ANSI_COLOR_YELLOW, "commrun", ANSI_COLOR_MAGENTA, "(systemid) (startdate) (enddate) - Do a commission run");
		CommEng.Help(ANSI_COLOR_YELLOW, "checkloop", ANSI_COLOR_MAGENTA, "(systemid) - check for recursion loops in given system");
		CommEng.Help(ANSI_COLOR_YELLOW, "checkloopall", ANSI_COLOR_MAGENTA, "check for recursion loops on all systems");
		CommEng.Help(ANSI_COLOR_YELLOW, "clearbatch", ANSI_COLOR_MAGENTA, "(batchid) clearout a given batch to allow batch to be rerun");
		CommEng.Help(ANSI_COLOR_YELLOW, "init", ANSI_COLOR_MAGENTA, "create database tables");
		CommEng.Help(ANSI_COLOR_YELLOW, "initunited", ANSI_COLOR_MAGENTA, "initialized united database files");
		CommEng.Help(ANSI_COLOR_YELLOW, "migrate", ANSI_COLOR_MAGENTA, "migrate database tables");
		CommEng.Help(ANSI_COLOR_YELLOW, "rebuildlevel", ANSI_COLOR_MAGENTA, "(systemid) - rebuild ce_levels table with in ref to one system passed in");
		CommEng.Help(ANSI_COLOR_YELLOW, "rebuildlevels", ANSI_COLOR_MAGENTA, "(startdate) (enddate) - rebuild the levels table for all systems");
		CommEng.Help(ANSI_COLOR_YELLOW, "rebuildtotals", ANSI_COLOR_MAGENTA, "(system_id) (batch_id)");
		CommEng.Help(ANSI_COLOR_YELLOW, "rebuildupline", ANSI_COLOR_MAGENTA, "(system_id)");
		CommEng.Help(ANSI_COLOR_YELLOW, "resetapikey", ANSI_COLOR_MAGENTA, "(systemuserid) - reset the apikey for given 'system' userid");
		CommEng.Help(ANSI_COLOR_YELLOW, "resetsyspass", ANSI_COLOR_MAGENTA, "(systemuserid) (password) - reset the password for given sysuserid");
		CommEng.Help(ANSI_COLOR_YELLOW, "resetuserpass", ANSI_COLOR_MAGENTA, "(systemid) (userid) (password) - reset the password for given userid inside given systemid");
		CommEng.Help(ANSI_COLOR_YELLOW, "resume", ANSI_COLOR_MAGENTA, "(systemid) - resume calculating commissions");
		CommEng.Help(ANSI_COLOR_YELLOW, "rollback", ANSI_COLOR_MAGENTA, "rollback a database migration");
		CommEng.Help(ANSI_COLOR_YELLOW, "seedsystem", ANSI_COLOR_MAGENTA, "(sysuserid) - seed system with rank and comm rules");
		CommEng.Help(ANSI_COLOR_YELLOW, "seeddata", ANSI_COLOR_MAGENTA, "(systemid) (recordcount) (startdate) (enddate) - Seed user and receipt data");
		CommEng.Help(ANSI_COLOR_YELLOW, "seedfromlive", ANSI_COLOR_MAGENTA, "(systemid) (startdate) (enddate) - Seed users from live");
		CommEng.Help(ANSI_COLOR_YELLOW, "runsim", ANSI_COLOR_MAGENTA, "(systemid) (startdate) (enddate) - Run a simulaton on sim database with copied live data");
		CommEng.Help(ANSI_COLOR_YELLOW, "united", ANSI_COLOR_MAGENTA, "(startdate) (enddate) - run commissions for the start and end date passed in on United");
		return 0;
	}

	if (argv[1] != NULL)
	{
		string command;
		string arg1;
		string arg2;
		string arg3;
		string arg4;

		if (argc == 1)
			return CommEng.Debug(DEBUG_ERROR, "main - An ini file needs to be supplied");
		if (argc == 2)
			return CommEng.Debug(DEBUG_ERROR, "main - A command needs to be supplied");

	    // Start the commission engine //
	    if (CommEng.Startup(argv[1]) == false)
	    	return CommEng.Debug(DEBUG_ERROR, "main - CommEng.Startup == false");
		
		// Handle arguments //
		if (argc >= 4)
			arg1 = argv[3];
		if (argc >= 5)
			arg2 = argv[4];
		if (argc >= 6)
			arg3 = argv[5];
		if (argc >= 7)
			arg3 = argv[6];

		// Handle command //
		command = argv[2];
		if (command == "api") // Initialize the database //
			CommEng.API();
		else if (command == "init") // Initialize the database //
			CommEng.CreateMaster();
		else if (command == "migrate") // Handle migrations //
			CommEng.Migrate();
		else if (command == "rollback") // Handle Rollback //
			CommEng.Rollback();
		else if (command == "rebuildlevel") 
			CommEng.RebuildLevel(atoi(arg1.c_str()));
		else if (command == "rebuildlevels") 
			CommEng.RebuildAllLevels(arg1.c_str(), arg2.c_str());
		else if (command == "rebuildupline") 
			CommEng.RebuildUpline(arg1.c_str());
		else if (command == "runsim") 
			CommEng.RunSim(atoi(arg1.c_str()), arg2, arg3);
		else if (command == "initunited") 
			CommEng.InitUnited();
		else if (command == "united") 
			CommEng.United(arg1.c_str(), arg2.c_str());
		else if (command == "checkloop") 
			CommEng.CheckLoop(atoi(arg1.c_str()));
		else if (command == "checkloopall")
			CommEng.CheckLoopAll();		
		else if (command == "commrun")
		{
			CommEng.CommRun(atoi(arg1.c_str()), arg2.c_str(), arg3.c_str());		
		}
		else if (command == "clearbatch") 
		{
			if (arg1.length() == 0)
			{
				CommEng.Debug(DEBUG_ERROR, "main() - A batch_id needs to be given");
 				return 0;
			}
			CommEng.ClearBatch(atoi(arg1.c_str()));
		}
		else if (command == "rebuildtotals") 
		{
			if (arg1.length() == 0)
			{
				CommEng.Debug(DEBUG_ERROR, "main() - A system_id needs to be given");
				return 0;
			}
			if (arg2.length() == 0)
			{
				CommEng.Debug(DEBUG_ERROR, "main() - A batch_id needs to be given");
				return 0;
			}
			CommEng.RebuildTotals(atoi(arg1.c_str()), atoi(arg2.c_str()));
		}
		else if (command == "resetapikey") 
			CommEng.ResetApiKey(atoi(arg1.c_str()));
		else if (command == "resetsyspass") 
			CommEng.ResetSysUserPassword(arg1, arg2);
		else if (command == "resetuserpass") 
			CommEng.ResetUserPassword(atoi(arg1.c_str()), arg2, arg3);
		else if (command == "resume") 
			CommEng.Resume(atoi(arg1.c_str()));
		else if (command == "test") 
			CommEng.Test();
		else if (command == "seedsystem") 
			CommEng.SeedSystem(atoi(arg1.c_str()));
		else if (command == "seeddata") 
			CommEng.SeedData(atoi(arg1.c_str()), atoi(arg2.c_str()), arg3.c_str(), arg4.c_str());
		else if (command == "seedfromlive") 
			CommEng.SeedFromLive(atoi(arg1.c_str()), arg2.c_str(), arg3.c_str());
		else 
		{
			CommEng.Debug(DEBUG_ERROR, "main() - Unrecognized command. Try using ceapi help");
		}
		return 0; // Just exit out for now //
	}
	else // Run actual ceapi program //
	{
      //  CCommissionEngine CommEng;
	  //	CommEng.Startup(); // Run the commission engine //
	}

	return 0;
}
#endif