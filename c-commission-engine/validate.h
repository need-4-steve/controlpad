#ifndef _VALIDATE_H
#define _VALIDATE_H

#include <string>

#define MAX_NUMERIC_LEN		10
#define MAX_IP_LEN			15
#define MAX_DATE_SIZE		10
#define MAX_TIMESTAMP_SIZE	30

/////////////////////////////
// Retain User Information //
/////////////////////////////
class CValidate
{
public:
	CValidate();
	
	bool is_number(const std::string& s);
	bool is_date(const std::string& s);
	bool is_timestamp(const std::string& s);
	bool is_decimal(const std::string& s);
	bool is_ipaddress(const std::string& s);
	bool is_basicalpha(const std::string& s);
	bool is_alpha(const std::string& s);
	bool is_alphanum(const std::string& s);
	bool is_email(const std::string& s);
	bool is_userid(const std::string& s);
	bool is_zipcode(const std::string& s);
	bool is_password(const std::string& s);
	bool is_boolean(const std::string& s);
	bool is_qstring(const std::string& s);
	bool is_json(const std::string& s);

private:
	bool is_date_human(const std::string& s);
	bool is_date_database(const std::string& s);
};

#endif