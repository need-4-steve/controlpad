#ifndef _EZRECV_H
#define _EZRECV_H

#include "ezJson.h"
#include "packets.h"
#include "debug.h"
#include "db.h"
#include "validate.h"
#include "ezVars.h"

#include <sstream>
#include <string>

// Server POST Messages //

// Auth //
#define POST_AUTHSESSIONUSER		"loginsystemuser"

// (Affiliate) LOGIN/PASSWORD functions //
#define POST_MYUSERVALIDCHECK		"myuservalidcheck"
#define POST_MYPASSHASHGEN			"mypasshashgen"
#define POST_MYPASSHASHVALID		"mypasshashvalid"
#define POST_MYPASSHASHUPDATE		"mypasshashupdate"
#define POST_MYPASSRESET			"mypassreset" 
#define POST_MYLOGOUTLOG			"mylogoutlog"

// End user (Affiliate) commands //
#define POST_MYJWTVERIFY			"myjwtverify"
#define POST_MYLOGIN				"mylogin"
#define POST_MYPROJECTIONS			"myprojections"
#define POST_MYCOMMISSIONS			"mycommissions"
#define POST_MYACHVBONUS			"myachvbonus"
#define POST_MYBONUS 				"mybonus"
#define POST_MYRANKGENBONUS 		"myrankgenbonus"
#define POST_MYLEDGER				"myledger"
#define POST_MYSTATS				"mystats"
#define POST_MYSTATS_LVL1			"mystatslvl1"
#define POST_MYDOWNSTATS			"mydownstats"
#define POST_MYDOWNSTATSLVL1		"mydownstatslvl1"
#define POST_MYDOWNSTATSFULL		"mydownstatsfull"
#define POST_MYSPONSOREDSTATS		"mysponsoredstats"
#define POST_MYSPONSOREDSTATSLVL1	"mysponsoredstatslvl1"
#define POST_MYBREAKDOWN			"mybreakdown"
#define POST_MYBREAKDOWNGEN			"mybreakdowngen"
#define POST_MYBREAKDOWNUSERS		"mybreakdownusers"
#define POST_MYBREAKDOWNORDERS		"mybreakdownorders"
#define POST_MYDOWNLINE_LVL1		"mydownlinelvlone"
#define POST_MYUPLINE				"myupline"
#define POST_MYTOPCLOSE				"mytopclose"
#define POST_MYRANKRULESMISSED		"myrankrulesmissed"
#define POST_MYDOWNRANKSUMLVL1		"mydownranksumlvl1"
#define POST_MYDOWNRANKSUM  		"mydownranksum"
#define POST_MYTITLE				"mytitle"
#define POST_MYRECEIPTSUM			"myreceiptsum"

// Settings //
#define POST_SETTINGS_GET			"settingsget"
#define POST_SETTINGS_QUERY			"settingsquery"
#define POST_SETTINGS_QUERYSYSTEM	"settingsquerysystem"
#define POST_SETTINGS_SET			"settingsset"
#define POST_SETTINGS_DISABLE		"settingsdisable"
#define POST_SETTINGS_ENABLE		"settingsenable"
#define POST_SETTINGS_SETSYSTEM		"settingssetsystem"
#define POST_SETTINGS_GET_TZ		"settingsgettz" // Retrieve all timezones //		

// Affiliate Downline commands //
#define POST_DOWNRANKRULESMISSED	"downrankrulesmissed"

// System User LOGIN/PASSWORD functions //
#define POST_SYSUSERVALIDCHECK		"validchecksysuser"
#define POST_PASSHASHSYSUSERGEN		"passhashsysusergen"
#define POST_PASSHASHSYSUSERVALID	"passhashsysuservalid"
#define POST_PASSHASHSYSUSERUPDATE	"passhashsysuserupdate"
#define POST_PASSRESETSYSUSER		"passresetsysuser"
#define POST_LOGOUTSYSUSERLOG		"logoutsysuserlog"

// System Users //
#define POST_ADDSYSTEMUSER			"addsystemuser"
#define POST_EDITSYSTEMUSER			"editsystemuser"
#define POST_QUERYSYSTEMUSERS		"querysystemuser"
#define POST_DISABLESYSTEMUSER		"disablesystemuser"
#define POST_ENABLESYSTEMUSER		"enablesystemuser"
#define POST_PASSRESETSYSTEMUSER	"passresetsystemuser"

// Systems //
#define POST_ADDSYSTEM				"addsystem"
#define POST_EDITSYSTEM				"editsystem"
#define POST_QUERYSYSTEMS			"querysystem"
#define POST_DISABLESYSTEM			"disablesystem"
#define POST_ENABLESYSTEM			"enablesystem"
#define POST_GETSYSTEM				"getsystem"
#define POST_COUNTSYSTEM			"countsystem"
#define POST_STATSSYSTEM			"statssystem"

// API keys //
// This is for only 1 apikey per user //
#define POST_REISSUEAPIKEY			"reissueapikey"
// Leave these in for future expansion //
#define POST_ADDAPIKEY				"addapikey"
#define POST_EDITAPIKEY				"editapikey"
#define POST_QUERYAPIKEY			"queryapikey"
#define POST_DISABLEAPIKEY			"disableapikey"
#define POST_ENABLEAPIKEY			"enableapikey"

// Users //
#define POST_ADDUSER				"adduser"
#define POST_EDITUSER				"edituser"
#define POST_UPDATEUSERADDR			"updateuseraddr"
#define POST_QUERYUSERS  			"queryuser"
#define POST_DISABLEUSER			"disableuser"
#define POST_ENABLEUSER				"enableuser"
#define POST_GETUSER				"getuser"

// Receipts //
#define POST_ADDRECEIPT					"addreceipt"
#define POST_EDITRECEIPT				"editreceipt"
#define POST_EDITRECEIPTWID				"editreceiptwid"
#define POST_QUERYRECEIPTS				"queryreceipt"
#define POST_QUERYRECEIPTSUM			"queryreceiptsum"
#define POST_DISABLERECEIPT				"disablereceipt"
#define POST_ENABLERECEIPT				"enablereceipt"
#define POST_GETRECEIPT					"getreceipt"
#define POST_QUERYBREAKDOWN				"querybreakdown"
#define POST_ADDRECEIPTBULK				"addreceiptbulk"
#define POST_UPDATERECEIPTBULK			"updatereceiptbulk"
#define POST_COMMISSIONABLERECEIPTBULK	"commissionablereceiptbulk"
#define POST_ORDERSUMRECEIPTWHOLE		"ordersumreceiptwhole" // Need for Receipt Orders Report in admin menu //
#define POST_CANCELRECEIPT				"cancelreceipt" // Handle cancel now. Later add returns, refunds, etc //

// Receipts Filter //
#define POST_ADDRECEIPTFILTER			"addreceiptfilter"
#define POST_EDITRECEIPTFILTER			"editreceiptfilter"
#define POST_QUERYRECEIPTFILTER			"queryreceiptfilter"
#define POST_DISABLERECEIPTFILTER		"disablereceiptfilter"
#define POST_ENABLERECEIPTFILTER		"enablereceiptfilter"
#define POST_GETRECEIPTFILTER			"getreceiptfilter"

// Rank Rules //
#define POST_ADDRANKRULE			"addrankrule"
#define POST_EDITRANKRULE			"editrankrule"
#define POST_QUERYRANKRULES			"queryrankrule"
#define POST_DISABLERANKRULE		"disablerankrule"
#define POST_ENABLERANKRULE			"enablerankrule"
#define POST_GETRANKRULE			"getrankrule"

// Rank Rules Missed //
#define POST_QUERYRANKRULESMISSED	"queryrankrulemissed"

// Handle an external qualify for rank //
#define POST_ADDEXTQUALIFY			"addextqualify"
#define POST_EDITEXTQUALIFY			"editextqualify"
#define POST_QUERYEXTQUALIFY		"queryextqualify"
#define POST_DISABLEEXTQUALIFY		"disableextqualify"
#define POST_ENABLEEXTQUALIFY		"enableextqualify"
#define POST_GETEXTQUALIFY			"getextqualify"

// Basic Commission Rules //
#define POST_ADDBASICCOMMRULE		"addbasiccommrule"
#define POST_EDITBASICCOMMRULE		"editbasiccommrule"
#define POST_QUERYBASICCOMMRULES	"querybasiccommrule"
#define POST_DISABLEBASICCOMMRULE	"disablebasiccommrule"
#define POST_ENABLEBASICCOMMRULE	"enablebasiccommrule"
#define POST_GETBASICCOMMRULE		"getbasiccommrule"

// Commission Rules //
#define POST_ADDCOMMRULE			"addcommrule"
#define POST_EDITCOMMRULE			"editcommrule"
#define POST_QUERYCOMMRULES			"querycommrule"
#define POST_DISABLECOMMRULE		"disablecommrule"
#define POST_ENABLECOMMRULE			"enablecommrule"
#define POST_GETCOMMRULE			"getcommrule"

// Check Match Rank Rules //
#define POST_ADDCMRANKRULE			"addcmrankrule"
#define POST_EDITCMRANKRULE			"editcmrankrule"
#define POST_QUERYCMRANKRULES		"querycmrankrule"
#define POST_DISABLECMRANKRULE		"disablecmrankrule"
#define POST_ENABLECMRANKRULE		"enablecmrankrule"
#define POST_GETCMRANKRULE			"getcmrankrule"

// Check Match Commission Rules //
#define POST_ADDCMCOMMRULE			"addcmcommrule"
#define POST_EDITCMCOMMRULE			"editcmcommrule"
#define POST_QUERYCMCOMMRULES		"querycmcommrule"
#define POST_DISABLECMCOMMRULE		"disablecmcommrule"
#define POST_ENABLECMCOMMRULE		"enablecmcommrule"

// Pools //
#define POST_ADDPOOL				"addpool"
#define POST_EDITPOOL				"editpool"
#define POST_QUERYPOOLS				"querypool"
#define POST_DISABLEPOOL			"disablepool"
#define POST_ENABLEPOOL 			"enablepool"
#define POST_GETPOOL	 			"getpool"

// Pool Rules //
#define POST_ADDPOOLRULE			"addpoolrule"
#define POST_EDITPOOLRULE			"editpoolrule"
#define POST_QUERYPOOLRULES			"querypoolrule"
#define POST_DISABLEPOOLRULE		"disablepoolrule"
#define POST_ENABLEPOOLRULE			"enablepoolrule"
#define POST_GETPOOLRULE			"getpoolrule"

// Bonus //
#define POST_ADDBONUS				"addbonus"
#define POST_EDITBONUS				"editbonus"
#define POST_QUERYBONUS				"querybonus"
#define POST_QUERYUSERBONUS			"queryuserbonus"
#define POST_DISABLEBONUS			"disablebonus"
#define POST_ENABLEBONUS			"enablebonus"
#define POST_GETBONUS				"getbonus"

// Fast Start //
#define POST_ADDFASTSTART			"addfaststart"
#define POST_EDITFASTSTART			"editfaststart"
#define POST_QUERYFASTSTART			"queryfaststart"
#define POST_QUERYUSERFASTSTART		"queryuserfaststart"
#define POST_DISABLEFASTSTART		"disablefaststart"
#define POST_ENABLEFASTSTART		"enablefaststart"
#define POST_GETFASTSTART			"getfaststart"

// Rank Bonus //
#define POST_ADDRANKBONUSRULE		"addrankbonusrule"
#define POST_EDITRANKBONUSRULE		"editrankbonusrule"
#define POST_QUERYRANKBONUSRULE		"queryrankbonusrule"
#define POST_DISABLERANKBONUSRULE	"disablerankbonusrule"
#define POST_ENABLERANKBONUSRULE	"enablerankbonusrule"
#define POST_GETRANKBONUSRULE		"getrankbonusrule"

#define POST_QUERYRANKBONUS			"queryrankbonus"

// Rank Gen Bonus //
#define POST_ADDRANKGENBONUSRULE		"addrankgenbonusrule"
#define POST_EDITRANKGENBONUSRULE		"editrankgenbonusrule"
#define POST_QUERYRANKGENBONUSRULE		"queryrankgenbonusrule"
#define POST_DISABLERANKGENBONUSRULE	"disablerankgenbonusrule"
#define POST_ENABLERANKGENBONUSRULE		"enablerankgenbonusrule"
#define POST_GETRANKGENBONUSRULE		"getrankgenbonusrule"

#define POST_QUERYRANKGENBONUS			"queryrankgenbonus"

// Signup Bonus //
#define POST_QUERYSIGNUPBONUS		"querysignupbonus"

// Commission Calc //
#define POST_PREDICTCOMMISSIONS		"predictcommissions"
#define POST_PREDICTGRANDTOTAL		"predictgrandtotal"
#define POST_CALCCOMMISSIONS		"calccommissions"
#define POST_QUERYBATCHES			"querybatches"
#define POST_QUERYUSERCOMM			"queryusercomm"
#define POST_QUERYBATCHCOMM			"querybatchcomm"

// Grand Payout //
#define POST_QUERYGRANDPAYOUT		"querygrandpayout"
#define POST_AUTHGRANDPAYOUT		"authgrandpayout"
#define POST_AUTHGRANDBULK			"authgrandbulk"
#define POST_DISABLEGRANDPAYOUT		"disablegrandpayout"
#define POST_ENABLEGRANDPAYOUT		"enablegrandpayout"

// Reports //
#define POST_QUERYAUDITRANKS		"queryauditranks"
#define POST_QUERYAUDITUSERS		"queryauditusers"
#define POST_QUERYAUDITGEN			"queryauditgen"
#define POST_QUERYRANKS 			"queryranks"
#define POST_QUERYACHVBONUS			"queryachvbonus"
#define POST_QUERYCOMMISSIONS		"querycommissions"
#define POST_QUERYUSERSTATS			"queryuserstats"
#define POST_QUERYUSERSTATSLVL1		"queryuserstatslvl1"

// Bank Account //
#define POST_ADDBANKACCOUNT			"addbankaccount"
#define POST_QUERYBANKACCOUNTS		"querybankaccounts"
#define POST_EDITBANKACCOUNT		"editbankaccount"
#define POST_DISABLEBANKACCOUNT		"disablebankaccount"
#define POST_ENABLEBANKACCOUNT		"enablebankaccount"
#define POST_GETBANKACCOUNT			"getbankaccount"

// Validate Account //
#define POST_INITIATEVALIDATION		"initiatevalidation"
#define POST_VALIDATEACCOUNT		"validateaccount"

// Payments //
#define POST_PROCESSPAYMENTS		"processpayments"
#define POST_QUERYUSERPAYMENTS		"queryuserpayments"
#define POST_QUERYBATCHPAYMENTS		"querybatchpayments"
#define POST_QUERYNOPAYUSERS		"querynopayusers"
#define POST_QUERYPAYMENTSTOTAL		"querypaymentstotal"
#define	POST_QUERYPAYMENTS 			"querypayments"

// Ledger //
#define POST_ADDLEDGER				"addledger"
#define POST_EDITLEDGER				"editledger"
#define POST_GETLEDGER				"getledger"
#define POST_QUERYLEDGER 			"queryledger"
#define POST_QUERYLEDGERUSER		"queryledgeruser"
#define POST_QUERYLEDGERBATCH		"queryledgerbatch"
#define POST_QUERYLEDGERBALANCE		"queryledgerbalance"

// Simulations //
#define POST_SIM_COPYSEED			"copyseedsim"
#define POST_RUNSIM					"runsim"

// Exit - Only enable for testing purposes //
#define POST_EXIT					"exit"

#define CMD_AUTHSESSIONUSER			1
#define CMD_MYUSERVALIDCHECK		2
#define CMD_MYPASSHASHGEN			3
#define CMD_MYPASSHASHVALID			4
#define CMD_MYPASSHASHUPDATE		5
#define CMD_MYPASSRESET				6 
#define CMD_MYLOGOUTLOG				7
#define CMD_MYJWTVERIFY				8
#define CMD_MYLOGIN					9
#define CMD_MYPROJECTIONS			10
#define CMD_MYCOMMISSIONS			11
#define CMD_MYACHVBONUS				12
#define CMD_MYBONUS 				13
#define CMD_MYRANKGENBONUS			14
#define CMD_MYLEDGER				15
#define CMD_MYSTATS					16
#define CMD_MYSTATS_LVL1			17
#define CMD_MYDOWNSTATS				18
#define CMD_MYDOWNSTATSLVL1			19
#define CMD_MYDOWNSTATSFULL			20
#define CMD_MYSPONSOREDSTATS		21
#define CMD_MYSPONSOREDSTATSLVL1	22
#define CMD_MYBREAKDOWN				23
#define CMD_MYBREAKDOWNGEN			24
#define CMD_MYBREAKDOWNUSERS		25
#define CMD_MYBREAKDOWNORDERS		26

#define CMD_MYDOWNLINE_LVL1			27
#define CMD_MYUPLINE				28
#define CMD_MYTOPCLOSE				29
#define CMD_MYRANKRULESMISSED		30
#define CMD_MYDOWNRANKSUMLVL1		31
#define CMD_MYDOWNRANKSUM  			32
#define CMD_MYTITLE					33
#define CMD_MYRECEIPTSUM			34

#define CMD_SETTINGS_GET			35
#define CMD_SETTINGS_QUERY			36
#define CMD_SETTINGS_QUERYSYSTEM	37
#define CMD_SETTINGS_SET			38
#define CMD_SETTINGS_DISABLE		39
#define CMD_SETTINGS_ENABLE			40
#define CMD_SETTINGS_SETSYSTEM		41
#define CMD_SETTINGS_GET_TZ			42
#define CMD_DOWNRANKRULESMISSED		43

#define CMD_SYSUSERVALIDCHECK		50
#define CMD_PASSHASHSYSUSERGEN		51
#define CMD_PASSHASHSYSUSERVALID	52
#define CMD_PASSHASHSYSUSERUPDATE	53
#define CMD_PASSRESETSYSUSER		54
#define CMD_LOGOUTSYSUSERLOG		55
#define CMD_ADDSYSTEMUSER			56
#define CMD_EDITSYSTEMUSER			57
#define CMD_QUERYSYSTEMUSERS		58
#define CMD_DISABLESYSTEMUSER		59
#define CMD_ENABLESYSTEMUSER		60
#define CMD_PASSRESETSYSTEMUSER		61
#define CMD_ADDSYSTEM				62
#define CMD_EDITSYSTEM				63
#define CMD_QUERYSYSTEMS			64
#define CMD_QUERYSYSTEMSALT			65
#define CMD_DISABLESYSTEM			66
#define CMD_ENABLESYSTEM    		67
#define CMD_GETSYSTEM				68
#define CMD_COUNTSYSTEM				69
#define CMD_STATSSYSTEM				70
#define CMD_REISSUEAPIKEY			71
#define CMD_ADDAPIKEY				72
#define CMD_EDITAPIKEY				73
#define CMD_QUERYAPIKEYS			74
#define CMD_DISABLEAPIKEY			75
#define CMD_ENABLEAPIKEY			76
#define CMD_ADDUSER					77
#define CMD_EDITUSER				78
#define CMD_UPDATEUSERADDR			79
#define CMD_QUERYUSERS 				80
#define CMD_DISABLEUSER				81
#define CMD_ENABLEUSER				82
#define CMD_GETUSER					83

#define CMD_ADDRECEIPT				84
#define CMD_EDITRECEIPT				85
#define CMD_EDITRECEIPTWID			86
#define CMD_QUERYRECEIPTS			87
#define CMD_QUERYRECEIPTSUM			88
#define CMD_DISABLERECEIPT			89
#define CMD_ENABLERECEIPT			90
#define CMD_GETRECEIPT				91

#define CMD_ADDRECEIPTFILTER		92
#define CMD_EDITRECEIPTFILTER		93
#define CMD_QUERYRECEIPTFILTER		94
#define CMD_DISABLERECEIPTFILTER	95
#define CMD_ENABLERECEIPTFILTER		96
#define CMD_GETRECEIPTFILTER		97

#define CMD_QUERYBREAKDOWN				98
#define CMD_ADDRECEIPTBULK				99
#define CMD_UPDATERECEIPTBULK			100
#define CMD_COMMISSIONABLERECEIPTBULK	101
#define CMD_ORDERSUMRECEIPTWHOLE		102	
#define CMD_CANCELRECEIPT				103

#define CMD_ADDRANKRULE				104
#define CMD_EDITRANKRULE			105
#define CMD_QUERYRANKRULES			106
#define CMD_QUERYRANKRULESMISSED	107
#define CMD_GETRANKRULE				108
#define CMD_DISABLERANKRULE			109
#define CMD_ENABLERANKRULE			110

#define CMD_ADDEXTQUALIFY			111
#define CMD_EDITEXTQUALIFY			112
#define CMD_QUERYEXTQUALIFY			113
#define CMD_DISABLEEXTQUALIFY		114
#define CMD_ENABLEEXTQUALIFY		115
#define CMD_GETEXTQUALIFY			116

#define CMD_ADDBASICCOMMRULE		150
#define CMD_EDITBASICCOMMRULE		151
#define CMD_QUERYBASICCOMMRULES		152
#define CMD_DISABLEBASICCOMMRULE	153
#define CMD_ENABLEBASICCOMMRULE	    154
#define CMD_GETBASICCOMMRULE		155

#define CMD_ADDCOMMRULE				156
#define CMD_EDITCOMMRULE			157
#define CMD_QUERYCOMMRULES			158
#define CMD_DISABLECOMMRULE			159
#define CMD_ENABLECOMMRULE			160
#define CMD_GETCOMMRULE				161

#define CMD_ADDCMRANKRULE			162
#define CMD_EDITCMRANKRULE			163
#define CMD_QUERYCMRANKRULES		164
#define CMD_DISABLECMRANKRULE		165
#define CMD_ENABLECMRANKRULE		166
#define CMD_GETCMRANKRULE			167

#define CMD_ADDCMCOMMRULE			168
#define CMD_EDITCMCOMMRULE			169
#define CMD_QUERYCMCOMMRULES		170
#define CMD_DISABLECMCOMMRULE		171
#define CMD_ENABLECMCOMMRULE		172

#define CMD_ADDPOOL 				173
#define CMD_EDITPOOL				174
#define CMD_QUERYPOOLS  			175
#define CMD_DISABLEPOOL 			176
#define CMD_ENABLEPOOL  			177
#define CMD_GETPOOL  				178
#define CMD_ADDPOOLRULE				179
#define CMD_EDITPOOLRULE			180
#define CMD_QUERYPOOLRULES			181
#define CMD_DISABLEPOOLRULE			182
#define CMD_ENABLEPOOLRULE			183
#define CMD_GETPOOLRULE				184

#define CMD_ADDBONUS				200
#define CMD_EDITBONUS				201
#define CMD_QUERYBONUS				202
#define CMD_QUERYUSERBONUS			203
#define CMD_DISABLEBONUS			204
#define CMD_ENABLEBONUS				205
#define CMD_GETBONUS				206

#define CMD_ADDFASTSTART			207
#define CMD_EDITFASTSTART			208
#define CMD_QUERYFASTSTART			209
#define CMD_DISABLEFASTSTART		210
#define CMD_ENABLEFASTSTART			211
#define CMD_GETFASTSTART			212

#define CMD_ADDRANKBONUSRULE		214
#define CMD_EDITRANKBONUSRULE		215
#define CMD_QUERYRANKBONUSRULE		216
#define CMD_DISABLERANKBONUSRULE	217
#define CMD_ENABLERANKBONUSRULE		218
#define CMD_GETRANKBONUSRULE		219
#define CMD_QUERYRANKBONUS			220


#define CMD_ADDRANKGENBONUSRULE			221
#define CMD_EDITRANKGENBONUSRULE		222
#define CMD_QUERYRANKGENBONUSRULE		223
#define CMD_DISABLERANKGENBONUSRULE		224
#define CMD_ENABLERANKGENBONUSRULE		225
#define CMD_GETRANKGENBONUSRULE			226
#define CMD_QUERYRANKGENBONUS			227

#define CMD_QUERYSIGNUPBONUS		228
#define CMD_PREDICTCOMMISSIONS		229
#define CMD_PREDICTGRANDTOTAL		230
#define CMD_CALCCOMMISSIONS			231
#define CMD_QUERYBATCHES			232
#define CMD_QUERYUSERCOMM			233
#define CMD_QUERYBATCHCOMM			234
#define CMD_QUERYGRANDPAYOUT		235
#define CMD_AUTHGRANDPAYOUT			236
#define CMD_AUTHGRANDBULK			237
#define CMD_DISABLEGRANDPAYOUT		238
#define CMD_ENABLEGRANDPAYOUT		239
#define CMD_QUERYAUDITRANKS			240
#define CMD_QUERYAUDITUSERS			241
#define CMD_QUERYAUDITGEN			242
#define CMD_QUERYRANKS 				243
#define CMD_QUERYACHVBONUS			244
#define CMD_QUERYCOMMISSIONS		245
#define CMD_QUERYUSERSTATS			246
#define CMD_QUERYUSERSTATSLVL1		247
#define CMD_ADDBANKACCOUNT			248
#define CMD_QUERYBANKACCOUNTS		249
#define CMD_EDITBANKACCOUNT			250
#define CMD_DISABLEBANKACCOUNT		251
#define CMD_ENABLEBANKACCOUNT		252
#define CMD_GETBANKACCOUNT			253
#define CMD_INITIATEVALIDATION		254
#define CMD_VALIDATEACCOUNT			255
#define CMD_PROCESSPAYMENTS			256	
#define CMD_QUERYUSERPAYMENTS		257
#define CMD_QUERYBATCHPAYMENTS		258
#define CMD_QUERYNOPAYUSERS			259
#define CMD_QUERYPAYMENTSTOTAL		260
#define CMD_QUERYPAYMENTS 			261
#define CMD_ADDLEDGER				262
#define CMD_EDITLEDGER				263
#define CMD_GETLEDGER				264
#define CMD_QUERYLEDGER 			265
#define CMD_QUERYLEDGERUSER			266
#define CMD_QUERYLEDGERBATCH		267
#define CMD_QUERYLEDGERBALANCE		268
#define CMD_SIM_COPYSEED			269
#define CMD_RUNSIM					270

#define CMD_EXIT					271 // Default disabled //

////////////////////////////////////////////
// Handle predefined lengths for limiting //
////////////////////////////////////////////
#define API_EMAIL_LENGTH	320	// Max length of an email address //
#define API_KEY_LENGTH		128 // Max length of SHA512 //
#define API_PASS_LENGTH		64 // Max limit of password string //

using namespace std;

//////////////////////////////////
// Process incoming information //
//////////////////////////////////
class CezRecv : public CezJson, CDebug, CValidate
{
public:
	CezRecv();
	void SetLogFile(string logfile);

	string Process(int socket, CDb *pDB, CezVars *pVars); // Process incoming communication //

//	CDb m_DB; // Our connection and database functions //

//private:

//	const char *GetPostVar(const char *key); // Grab from out map hash values //
	int CheckCommands(const char *string);

	//CezSettings *m_pSettings; // Pointer to commission engine settings //
};

#endif
