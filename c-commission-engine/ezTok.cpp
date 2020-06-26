#include "ezTok.h"

/////////////////////////////
// Parse the inital string //
/////////////////////////////
CezTok::CezTok(const char *data, char delimiter)
{
	//printf("data = %s\n", data);
	//printf("strlen(data) = %d\n\n", (int)strlen(data));

	int count = 0;
	int start = 0;
	int end = 0;
	int index;
	for (index=0; index < strlen(data); index++)
	{
		string datastr = data;

		if (data[index] == delimiter)
		{
			// Define end of string //
			end = index; 
			
			// Add to map //
			m_MapCount[count] = datastr.substr(start, end-start);
			m_MapCount[count] = trim(m_MapCount[count]);

			// Go to next map element //
			count++;

			// Reset the start and end //
			start = index+1;
			end = index+1;
		}

		if (data[index+1] == 0)
		{
			// Add the last map element //
			m_MapCount[count] = datastr.substr(end, datastr.length());
			m_MapCount[count] = trim(m_MapCount[count]);
			break;
		}
	}

	m_Max = count;
}

/////////////////////////////
// Parse the inital string //
/////////////////////////////
CezTok::CezTok(const char *data, const char *delimiter)
{
	//printf("data = %s\n", data);
	//printf("strlen(data) = %d\n\n", (int)strlen(data));

	int count = 0;
	int start = 0;
	int end = 0;
	int index;
	for (index=0; index < strlen(data); index++)
	{
		string datastr = data;

		//if (data[index] == delimiter)
		if (strcmp(datastr.substr(index, strlen(delimiter)).c_str(), delimiter) == 0)
		{
			// Define end of string //
			end = index; 
			
			// Add to map //
			m_MapCount[count] = datastr.substr(start, end-start);
			m_MapCount[count] = trim(m_MapCount[count]);

			// Go to next map element //
			count++;

			// Reset the start and end //
			start = index+strlen(delimiter);
			end = index+strlen(delimiter);
		}

		if (data[index+1] == 0)
		{
			// Add the last map element //
			m_MapCount[count] = datastr.substr(end, datastr.length());
			m_MapCount[count] = trim(m_MapCount[count]);
			break;
		}
	}

	m_Max = count;
}

/////////////////////////////
// Get the max of elements //
/////////////////////////////
int CezTok::GetMax()
{
	return m_Max;
}

////////////////////////////////////
// Get the value at a given index //
////////////////////////////////////
string CezTok::GetValue(int index)
{
	return m_MapCount[index];
}