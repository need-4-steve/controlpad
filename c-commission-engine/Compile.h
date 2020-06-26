// This file need to distinguish compiling 
// Between standard linux and ruby-rice //
// Comment out when not using Ruby-Rice //
//#define COMPILE_RUBYRICE
//#define COMPILE_UNITED

//#define COMPILE_OSX
#define COMPILE_UBUNTU
//#define COMPILE_SSL

#define COMPILE_POSTGRES
//#define COMPILE_MYSQL

#define COMPILE_HTTP_HEADERS

//#define COMPILE_TESTS 		// Enable google tests //
//#define COMPILE_TESTDATA 	// Populate database with test data //

//#define TESTING_SPEEDUP
//#define COMPILE_LOCAL // Allows createdb command for speed testing //

#define COMPILE_CRYPT_VER1 // Old HMAC library type //
//#define COMPILE_CRYPT_VER2 // New HMAC library type //