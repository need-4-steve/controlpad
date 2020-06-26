#ifndef _EZCRYPT_H
#define _EZCRYPT_H

#include "debug.h"

#include <string>

//#define CRYPT_SALT		"$~6Hm^@2f $3&*~eT4({ELVIS_LIVES}56GG5 vn89jKLl;"
//#define CRYPT_SALT			unsigned char salt_value[] = {'$','~','6','H','m','^','@','2','f',' ','$','3','&','*','~','e','T','4','(','{','E','L','V','I','S','','L','I','V','E','S','}','5','6','G','k','', ' ','v','n','8','9','j','K','u',';','>','+'};
//#define CRYPT_SALT_LEN		48

using namespace std;

//static inline bool is_base64(unsigned char c);
//bool is_base64(unsigned char c);

/////////////////////////////////////////////
// Package pair together for return values //
/////////////////////////////////////////////
class CezCryptPair
{
public:
	string m_Public;
	string m_Private;
};

///////////////////////////////////////////////
// Handle all of our encryption and API keys //
///////////////////////////////////////////////
class CezCrypt : CDebug
{
public:
	CezCrypt();
	// Needed for JWT //
	bool is_base64(unsigned char c);
	string HMAC_Generate(string secret, const char *data);
	string HMAC_Verify(string secret, string token, string claimsToVerify);

	const char *GenSalt();
	const char *GenPBKDF2(const char *hashpass, const char *salt, const char *password);

	// Leave these in for reference //
	const char *GenSha512();
	const char *GenSha256();
 	void Reset();

 	string base64_encode(unsigned char const* bytes_to_encode, unsigned int in_len);
 	string base64_decode(string const& encoded_string);


	string m_Salt;

	string m_Result;

	string m_HmacSha256;


 	string base64_chars; // = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_"; // + = -, / = _ ... Why? //
};

#endif