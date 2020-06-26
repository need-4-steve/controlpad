require './rice_CE'
require 'json'

ce = CeMain.new
puts ce.Startup("ce", "root", "53eqRpYtQPP94apf", "127.0.0.1");

#define DEBUG_SCREEN		1
#define	DEBUG_FILE			2
#define DEBUG_BOTH			3
ce.SetDebugDisplay(3);
ce.SetDebugLevel(1); # Only Errors. 6 will give everything up to SQL
commission = CeCommission.new
puts commission.FullCalcSpeed(24, "2016-11-1", "2016-11-30"); # Spawn 24 child processes to speed up calculations
