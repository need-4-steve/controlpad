#ifndef _SOCKETINV_PRODUCT_H
#define _SOCKETINV_PRODUCT_H

//////////////////////////////
// Define the product class //
//////////////////////////////
class CProduct
{
public:
	CProduct();
	
	// Skip to the chase for right now //
	//int SetProdInfoDB(int product_id, int quantity);
	//int SetProdInfoUser(int qtyresv);

	int m_ProductID; // Store product_id from database //
	int m_Quantity; // Store quantity from database //
	int m_QuantityReserved; // Track actual quantity in middle of purchase via each user //
};

#endif