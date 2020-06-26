/*===============================================================================
* Copyright 2016(c) ControlPad
*==============================================================================*/
package com.controlpad.pay_fac.transaction;

public class TransactionProcessUtilTest {

//    private void assertSaleBreakdown(Transaction transaction) {
//        List<TransactionPayout> payouts = getSqlSession().getMapper(TransactionPayoutMapper.class).listByTransactionId(transaction.getId());
//        boolean merchantPaid = false;
//        boolean taxPaid = false;
//        for (TransactionPayout payout : payouts) {
//            switch (TransactionPayoutType.findForSlug(payout.getType())) {
//                case TAX:
//                    assert BigDecimal.valueOf(payout.getAmount()).doubleValue() == getTax();
//                    taxPaid = true;
//                    break;
//                case FEE:
//                    // Not currently used
//                    break;
//                case MERCHANT:
//                    merchantPaid = true;
//                    break;
//            }
//        }
//        assert taxPaid;
//        assert merchantPaid;
//    }
}
