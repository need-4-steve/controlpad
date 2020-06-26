#include "ezACH.h"

////////////////////////////////
// Grab the destination route //
////////////////////////////////
const char *CezACH::getDestinationRoute()
{
	return "destroute";
}

///////////////////////////
// Grab the origin route //
///////////////////////////
const char *CezACH::getOriginRoute()
{
	return "originroute";
}

///////////////////////////////
// Grab the destination name //
///////////////////////////////
const char *CezACH::getDestinationName()
{
	return "destname";
}

//////////////////////////
// Grab the origin name //
//////////////////////////
const char *CezACH::getOriginName()
{
	return "originname";
}

///////////////////////////
// Grab the company name //
///////////////////////////
const char *CezACH::getCompanyName()
{
	return "companyname";
}

/////////////////////////
// Grab the company ID //
/////////////////////////
const char *CezACH::getCompanyId()
{
	return "companyid";
}

///////////////////
// Grab the ODFI //
///////////////////
const char *CezACH::getODFI()
{
	return "odfi";
}

///////////////////////////////////////
// 22 - credit | 32 - savings credit //
///////////////////////////////////////
const char *CezACH::getType()
{
	return "type";
}

////////////////////////////
// Get the routing number //
////////////////////////////
const char *CezACH::getRouting()
{
	return "routingnumber";
}

/////////////////////////////////
// Get the name of the account //
/////////////////////////////////
const char *CezACH::getName()
{
	return "name";
}
