#ifndef _CESIMULATION_H
#define _CESIMULATION_H

#include "dbplus.h"
#include <string>

using namespace std;

class CceSimulation : public CDbPlus
{
public:
	CceSimulation();
	const char *CopySeed(int socket, string simini, int system_id, string copyseedoption, string seed_type, string users_max, 
				string receipts_max, string min_price, string max_price, string start_date, string end_date);
	const char *Run(int socket, string simini, int system_id, string start_date, string end_date);

private:
	string m_Json;
};

#endif