#ifndef _EZACH_H
#define _EZACH_H

// The CezNacha class uses ACH //

////////////////////////////////////////////////
// Manage ACH transacitons through this class //
////////////////////////////////////////////////
class CezACH
{
public:
	const char *getDestinationRoute();
	const char *getOriginRoute();
	const char *getDestinationName();
	const char *getOriginName();
	const char *getCompanyName();
	const char *getCompanyId();
	const char *getODFI();
	const char *getType();
	const char *getRouting();
	const char *getName();
};

#endif