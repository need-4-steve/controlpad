#include "ezCrypt.h"
#include "ezTok.h"

#include "Compile.h"
#include "convert.h"
#ifndef COMPILE_RUBYRICE

#include <crypto++/aes.h>
#include <crypto++/sha.h>
#include <cryptopp/osrng.h> // Needed for OS_GenerateRandomBlock //
#include <cryptopp/hex.h> // Needed for HexEncoder //
#include <cryptopp/pwdbased.h> // Needed for PBKDF2 //

#include <openssl/hmac.h>
#include <jsoncpp/json/json.h>

//#include <jsoncpp/json/value.h>
//#include <fstream>
#include <time.h>

// Compile Ruby-Rice //
#endif

//#include <cryptopp/modes.h>

#include <stdlib.h>     /* srand, rand */
#include <time.h>       /* time */
#include <sstream>

#include <iostream>

//#include <stdio.h>
#define SIZE 1000

#ifndef COMPILE_RUBYRICE
// Handle compiling Ruby-Rice //

using namespace CryptoPP;
//http://stackoverflow.com/questions/12306956/example-of-aes-using-crypto

/*
static const std::string base64_chars = 
             "ABCDEFGHIJKLMNOPQRSTUVWXYZ"
             "abcdefghijklmnopqrstuvwxyz"
             "0123456789-_"; // + = -, / = _ ... Why? //
*/
CezCrypt::CezCrypt()
{
    base64_chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_";
}

//static inline bool is_base64(unsigned char c)
bool CezCrypt::is_base64(unsigned char c)
{   
    return (isalnum(c) || (c == '+') || (c == '/'));
}

////////////////////
// Needed for JWT //
////////////////////
string CezCrypt::HMAC_Generate(string secret, const char *data)
{   
    // Be careful of the length of string with the choosen hash engine. SHA1 needed 20 characters.
    // Change the length accordingly with your choosen hash engine.     
    // SHA256 needs 64 //
    unsigned char* result;
    unsigned int len = 64;
 
    result = (unsigned char*)malloc(sizeof(char) * len);
 
#ifdef COMPILE_CRYPT_VER1
    HMAC_CTX ctx;
    HMAC_CTX_init(&ctx);
 
    // Using sha256 hash engine here.
    // You may use other hash engines. e.g EVP_md5(), EVP_sha224, EVP_sha512, etc
    HMAC_Init_ex(&ctx, secret.c_str(), strlen(secret.c_str()), EVP_sha256(), NULL);
    HMAC_Update(&ctx, (unsigned char*)data, strlen(data));
    HMAC_Final(&ctx, result, &len);
    HMAC_CTX_cleanup(&ctx);
#endif

#ifdef COMPILE_CRYPT_VER2
    HMAC_CTX *h = HMAC_CTX_new();

    HMAC_Init_ex(h, secret.c_str(), strlen(secret.c_str()), EVP_sha256(), NULL);
    HMAC_Update(h, (unsigned char*)data, strlen(data));
    HMAC_Final(h, result, &len);

    HMAC_CTX_free(h);
#endif

    //for (int i = 0; i != len; i++)
    //    printf("%02x", (unsigned int)result[i]);

/*    char tmpstr[32];
    for (int i = 0; i != len; i++)
    {
        // This is base32 //
        //printf("%02x", (unsigned int)result[i]);
        //sprintf(tmpstr, "%02x", (unsigned int)result[i]);

        m_HmacSha256 += tmpstr;
    }
*/
    m_HmacSha256 = base64_encode(result, len);
    m_HmacSha256 = rtrim((char *)m_HmacSha256.c_str(), '='); // Why does theris not have an '='' sign at the end ?
 
    free((void *)result);
 
    return m_HmacSha256;
}

////////////////////
// Needed for JWT //
////////////////////
string CezCrypt::HMAC_Verify(string secret, string token, string claimsToVerify)
{
    if (secret.size() == 0)
    {
        Debug(DEBUG_INFO, "CezCrypt::HMAC_Verify - secret.size() == 0");
        return "";
    }
    if ((token.size() == 0) || (token.size() >= 2048))
    {
        Debug(DEBUG_INFO, "CezCrypt::HMAC_Verify - token.size() == 0 OR >= 2018");
        return "";
    }

    // Handle token segmentation //
    string tokseg;
    if (strstr(token.c_str(), ".") != NULL)
        tokseg = ".";
    else if (strstr(token.c_str(), "-") != NULL)
        tokseg = "-";
    else
    {
        Debug(DEBUG_INFO, "CezCrypt::HMAC_Verify - token does not have . or - as seperator");
        return "";
    }

    //char strToken[2048];
    //sprintf(strToken, "%s", token.c_str());
    //string header = strtok(strToken, tokseg.c_str());
    
    // Use new token class //
    const char *seg = tokseg.c_str();
    CezTok tok(token.c_str(), seg[0]);
    string header = tok.GetValue(0);

    CConvert conv;
    conv.StrReplace(header, "Bearer ", "");

    if (header.size() == 0)
    {
        Debug(DEBUG_INFO, "CezCrypt::HMAC_Verify - header.size() == 0");
        return "";
    }
    //string payload = strtok(NULL, tokseg.c_str());
    string payload = tok.GetValue(1);
    if (payload.size() == 0)
    {
        Debug(DEBUG_INFO, "CezCrypt::HMAC_Verify - payload.size() == 0");
        return "";
    }
    //string signature = strtok(NULL, tokseg.c_str());
    string signature = tok.GetValue(2);
    if (signature.size() == 0)
    {
        Debug(DEBUG_INFO, "CezCrypt::HMAC_Verify - signature.size() == 0");
        return "";
    }

    //Debug(DEBUG_ERROR, "secret", secret);
    //Debug(DEBUG_DEBUG, "header", header);
    //Debug(DEBUG_DEBUG, "payload", payload);
    
    // Verify the signature //
    string combined = header+"."+payload;
    string verifiedSignature = HMAC_Generate(secret, combined.c_str());

    //Debug(DEBUG_DEBUG, "signature", signature);
    //Debug(DEBUG_DEBUG, "verified ", verifiedSignature);

    if (signature != verifiedSignature)
    {
        Debug(DEBUG_INFO, "CezCrypt::HMAC_Verify - signature != verifiedSignature");
        return ""; // Do we want to log false authentication? //
    }

    // Decode base 64 //
    string payload_decoded = base64_decode(payload);
    Debug(DEBUG_DEBUG, "payload_decoded", payload_decoded.c_str());

    // It's such a pain working with this json library //
    // I should find that other one that's easier //
    Json::Value root;
    Json::Reader reader;
    bool parsingSuccessful = reader.parse(payload_decoded, root);
    if (!parsingSuccessful)
    {
        Debug(DEBUG_INFO, "CezCrypt::HMAC_Verify - Error parsing the json string");
        return "";
    }
    const Json::Value expval = root["exp"];
    const Json::Value iatval = root["iat"];
    const Json::Value jtival = root["jti"];
    const Json::Value nbfval = root["nbf"];
    const Json::Value subval = root["sub"];
    const Json::Value roleval = root["role"];
    Json::FastWriter fastWriter;
    std::string expstr = fastWriter.write(expval);
    expstr = trim(expstr);
    Debug(DEBUG_DEBUG, "expstr ", expstr);
    if (expstr.size() == 0)
    {
        Debug(DEBUG_INFO, "CezCrypt::HMAC_Verify - expstr.size() == 0");
        return "";
    }

    // Check for expired token //  
    stringstream ssCurrTime;
    ssCurrTime << time(0);
    Debug(DEBUG_DEBUG, "nowtime", ssCurrTime.str().c_str());
    if (atoi(expstr.c_str()) < atoi(ssCurrTime.str().c_str()))
    {
        Debug(DEBUG_INFO, "CezCrypt::HMAC_Verify - Time Expired");
        return "";
    }

    //Debug(DEBUG_ERROR, "CezCrypt::HMAC_Verify - After Time Check");

    if (claimsToVerify.size() == 0)
    {
        Debug(DEBUG_INFO, "CezCrypt::HMAC_Verify - claimsToVerify empty");
        return "";
    }
    else if (claimsToVerify.size() != 0)
    {
        std::string iatstr = fastWriter.write(iatval);
        std::string jtistr = fastWriter.write(jtival);
        std::string nbfstr = fastWriter.write(nbfval);
        std::string rolestr = fastWriter.write(roleval);
        rolestr = trim(rolestr);

        // Compare the claims? //

        if (rolestr != "Rep")
        {
            Debug(DEBUG_INFO, "CezCrypt::HMAC_Verify - role != Rep");
            return "";
        }
    }
/*
        $claims = json_decode(self::decode($payload), true);
        if (!empty($claimsToVerify)) {
            foreach ($claimsToVerify as $key => $value) {
                if ($value != $claims[$key]) {
                    throw new \Exception("Invalid token claim '$key'. Expected '$value', actual '$claims[$key]'.");
                }
            }
        }
        return $claims;
*/
    m_Result = fastWriter.write(subval);
    m_Result = trim(m_Result);

    return m_Result;
}


//////////////////////////////////////////
// Generate salt from cryptopp function //
//////////////////////////////////////////
const char *CezCrypt::GenSalt()
{
    m_Salt.clear();
    SecByteBlock key(AES::DEFAULT_KEYLENGTH);
    OS_GenerateRandomBlock(false, key, key.size()); // Switching to "false" sped it up //

    // Use /dev/random
    HexEncoder hex(new StringSink(m_Salt));

    hex.Put(key, key.size());
    hex.MessageEnd();
    return m_Salt.c_str();
}

////////////////////////////////////////////
// This is supposed to be the most secure //
////////////////////////////////////////////
const char *CezCrypt::GenPBKDF2(const char *hashpass, const char *salt, const char *password)
{	
    m_Result.clear();
	//Debug(DEBUG_DEBUG, "CezCrypt::GenPBKDF2 - TOP");
    //Debug(DEBUG_DEBUG, "----------------------------");
    //Debug(DEBUG_DEBUG, "CezCrypt::GenPBKDF2 - hashpass", hashpass);
    //Debug(DEBUG_DEBUG, "CezCrypt::GenPBKDF2 - salt", salt);
    //Debug(DEBUG_DEBUG, "CezCrypt::GenPBKDF2 - password", password);

    std::string saltplus;
    saltplus = salt;
    saltplus += hashpass;
    size_t passwordlen = strlen(password);
    size_t saltlen = strlen(saltplus.c_str());

    //  A minimum iteration count of 1,000 is recommended. For especially 
    // critical keys, or for very powerful systems or systems where 
    // user-perceived performance is not critical, an 
    // iteration count of 10,000,000 may be appropriate. 
    int iterations = 10000; // This seems quick enough? //
    byte derived[40];

    //Debug(DEBUG_DEBUG, "----------------------------");
    //Debug(DEBUG_DEBUG, "CezCrypt::GenPBKDF2 - hashpass", hashpass);
    //Debug(DEBUG_DEBUG, "CezCrypt::GenPBKDF2 - saltplus", saltplus.c_str());

    CryptoPP::PKCS5_PBKDF2_HMAC<SHA1> pbkdf2;
    pbkdf2.DeriveKey(derived, sizeof(derived), 0, (const byte *)password, passwordlen, (const byte *)saltplus.c_str(), saltlen, iterations);

    //std::string result;
    HexEncoder encoder(new StringSink(m_Result));
    encoder.Put(derived, sizeof(derived));
    encoder.MessageEnd();

    //Debug(DEBUG_DEBUG, "CezCrypt::GenPBKDF2 - result", m_Result.c_str());
 
    return m_Result.c_str();
}

////////////////////////////////////
// Hash for API ID authentication //
////////////////////////////////////
const char *CezCrypt::GenSha256()
{
	//Debug(DEBUG_DEBUG, "CezCrypt::GenSha256 - TOP");

    m_Result.clear();

	std::stringstream datastream;
	std::string data;

	// Build the data for the hash //
	datastream << GenSalt();
	data = datastream.str();

    //Debug(DEBUG_DEBUG, "CezCrypt::GenSha256 - SALT =", data.c_str());

	// Do actual SHA256 //
	byte const* pbData = (byte*)data.c_str();
    //unsigned int nDataLen = data.size();
    unsigned long nDataLen = data.size();
    byte abDigest[CryptoPP::SHA256::DIGESTSIZE];
    CryptoPP::SHA256().CalculateDigest(abDigest, pbData, nDataLen);
    //std::string readable;
    std::stringstream ss;
    for (int i=0; i < CryptoPP::SHA256::DIGESTSIZE; i++)
    {
    	ss << std::hex << (int)abDigest[i];
    }

    m_Result = ss.str();

    //Debug(DEBUG_DEBUG, "CezCrypt::GenSha256 - readable =", readable.c_str());

    return m_Result.c_str();
}

////////////////////////////////////
// Hash for API ID authentication //
/////////////////////////////////////
// Exceeds 95 limit for header var //
/////////////////////////////////////
const char *CezCrypt::GenSha512()
{
    std::stringstream datastream;
    std::string data;

    // Build the data for the hash //
    datastream << GenSalt();
    data = datastream.str();

    // Do actual SHA256 //
    byte const* pbData = (byte*)data.c_str();
    //unsigned int nDataLen = data.size();
    unsigned long nDataLen = data.size();
    byte abDigest[CryptoPP::SHA512::DIGESTSIZE];
    CryptoPP::SHA512().CalculateDigest(abDigest, pbData, nDataLen);
    std::string readable;
    std::stringstream ss;
    for (int i=0; i < CryptoPP::SHA512::DIGESTSIZE; i++)
    {
        ss << std::hex << (int)abDigest[i];
    }

    readable = ss.str();
    return readable.c_str();
}

// Linux Compile //
#endif

#ifdef COMPILE_RUBYRICE
// Handle Ruby-Rice compile //
const char *CezCrypt::GenSalt()
{
    return "RUBY_SALT_4NOW";
}

const char *CezCrypt::GenPBKDF2(const char *hashpass, const char *salt, const char *password)
{
    return "RUBY_GENPBKDF2_4NOW";
}

const char *CezCrypt::GenSha256()
{
    return "RUBY_GenSha256_4NOW";
}
#endif

///////////////////////////////
// Reset the internal buffer //
///////////////////////////////
void CezCrypt::Reset()
{
    m_Result.clear();
}

///////////////////
// Encode Base64 //
///////////////////
std::string CezCrypt::base64_encode(unsigned char const* bytes_to_encode, unsigned int in_len)
{
    std::string ret;
    int i = 0;
    int j = 0;
    unsigned char char_array_3[3];
    unsigned char char_array_4[4];

    while (in_len--)
    {
        char_array_3[i++] = *(bytes_to_encode++);
        if (i == 3)
        {
            char_array_4[0] = (char_array_3[0] & 0xfc) >> 2;
            char_array_4[1] = ((char_array_3[0] & 0x03) << 4) + ((char_array_3[1] & 0xf0) >> 4);
            char_array_4[2] = ((char_array_3[1] & 0x0f) << 2) + ((char_array_3[2] & 0xc0) >> 6);
            char_array_4[3] = char_array_3[2] & 0x3f;

            for(i = 0; (i <4) ; i++)
                ret += base64_chars[char_array_4[i]];
            i = 0;
        }
    }

    if (i)
    {
        for(j = i; j < 3; j++)
            char_array_3[j] = '\0';

        char_array_4[0] = (char_array_3[0] & 0xfc) >> 2;
        char_array_4[1] = ((char_array_3[0] & 0x03) << 4) + ((char_array_3[1] & 0xf0) >> 4);
        char_array_4[2] = ((char_array_3[1] & 0x0f) << 2) + ((char_array_3[2] & 0xc0) >> 6);
        char_array_4[3] = char_array_3[2] & 0x3f;

        for (j = 0; (j < i + 1); j++)
            ret += base64_chars[char_array_4[j]];

        while((i++ < 3))
            ret += '=';
    }

    return ret;
}

///////////////////
// Decode Base64 //
///////////////////
std::string CezCrypt::base64_decode(std::string const& encoded_string)
{
    size_t in_len = encoded_string.size();
    size_t i = 0;
    size_t j = 0;
    int in_ = 0;
    unsigned char char_array_4[4], char_array_3[3];
    std::string ret;

    while (in_len-- && ( encoded_string[in_] != '=') && is_base64(encoded_string[in_]))
    {
        char_array_4[i++] = encoded_string[in_]; in_++;
        if (i == 4)
        {
            for (i = 0; i <4; i++)
                char_array_4[i] = static_cast<unsigned char>(base64_chars.find(char_array_4[i]));

            char_array_3[0] = (char_array_4[0] << 2) + ((char_array_4[1] & 0x30) >> 4);
            char_array_3[1] = ((char_array_4[1] & 0xf) << 4) + ((char_array_4[2] & 0x3c) >> 2);
            char_array_3[2] = ((char_array_4[2] & 0x3) << 6) + char_array_4[3];

            for (i = 0; (i < 3); i++)
                ret += char_array_3[i];
            i = 0;
        }
    }

    if (i)
    {
        for (j = i; j <4; j++)
            char_array_4[j] = 0;

        for (j = 0; j <4; j++)
            char_array_4[j] = static_cast<unsigned char>(base64_chars.find(char_array_4[j]));

        char_array_3[0] = (char_array_4[0] << 2) + ((char_array_4[1] & 0x30) >> 4);
        char_array_3[1] = ((char_array_4[1] & 0xf) << 4) + ((char_array_4[2] & 0x3c) >> 2);
        char_array_3[2] = ((char_array_4[2] & 0x3) << 6) + char_array_4[3];

        for (j = 0; (j < i - 1); j++) ret += char_array_3[j];
    }

    return ret;
}
