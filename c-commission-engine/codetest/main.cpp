
#include <sstream>
#include <stdio.h>

using namespace std;

void TestFunc(basic_ostream<char> &sspass);

int main(int argc, char** argv)
{

	stringstream ssTest;

	//ssTest << "Basic Test";
	//printf("TEST = %s\n", ssTest.str().c_str());
	
	int testnum = 99;

	TestFunc(ssTest << "Basic Test = " << testnum);


}

void TestFunc(basic_ostream<char> &sspass)
{

	stringstream ssTest2;

	ssTest2 << sspass.rdbuf();

	printf("TestFunc = %s\n", ssTest2.str().c_str());

}