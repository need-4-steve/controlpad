#ifndef _CECOMMISSIONS_H
#define _CECOMMISSIONS_H

#include "ezJson.h"
#include "debug.h"
#include "validate.h"
#include "convert.h"
#include "db.h"
#include <string>

using namespace std;

class CceCommissions : CezJson, CValidate, CConvert
{
public:
	CceCommissions(CDb *pDB, string origin);
	const char *Predict(int socket, int system_id, string startdate, string enddate);
	const char *PredictGrandTotal(int socket, int system_id, string startdate, string enddate);
	const char *Calc(int socket, int system_id, string startdate, string enddate);
	//const char *GetUserStats(int system_id);
	const char *QueryBatches(int socket, int system_id);
	const char *QueryBatchesAlt(int socket, int system_id, string search, string orderby, string orderdir, string offset, string limit);
	const char *QueryUser(int socket, int system_id, string user_id);
	const char *QueryComm(int socket, int system_id, string batch_id);

	const char *FullPredict(int socket, string startdate, string enddate);
	const char *FullCalc(int socket, string startdate, string enddate);
	
	const char *FullCalcSpeed(int socket, int proc_count, string startdate, string enddate);
	const char *SetRankOverride(int socket, int rank); // Allow United to force lowest rank level //

private:
	string m_Json;
	CDb *m_pDB;
};

#endif