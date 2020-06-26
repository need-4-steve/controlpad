package com.controlpad.payman_processor.util;

import com.controlpad.payman_common.affiliate_charge.AffiliateCharge;
import com.controlpad.payman_common.affiliate_charge.AffiliateChargeMapper;
import com.controlpad.payman_common.transaction.Transaction;
import com.controlpad.payman_common.transaction.TransactionMapper;
import com.controlpad.payman_common.util.GsonUtil;
import org.apache.ibatis.session.SqlSession;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import java.util.List;

public class TransactionUtil {

    private static final Logger logger = LoggerFactory.getLogger(TransactionUtil.class);

    public static boolean insertTransaction(TransactionMapper transactionMapper, Transaction transaction, IDUtil idUtil,
                                            AffiliateChargeMapper affiliateChargeMapper, List<AffiliateCharge> affiliateChargeList) {
        if (!insertTransactionTry(transactionMapper, transaction, idUtil)) {
            return false;
        }
        if (affiliateChargeList != null && !affiliateChargeList.isEmpty()) {
            for (AffiliateCharge affiliateCharge : affiliateChargeList) {
                if (transaction.getTransactionType().equals("refund")) {
                    affiliateCharge.negateAmount();
                }
                affiliateCharge.setTransactionId(transaction.getId());
            }
            affiliateChargeMapper.insertList(affiliateChargeList);
        }
        return true;
    }

    private static boolean insertTransactionTry(TransactionMapper transactionMapper, Transaction transaction, IDUtil idUtil) {
        for (int i = 0; i < 3; i++) {
            try {
                transaction.setId(idUtil.generateId());
                transactionMapper.insert(transaction);
                return true;
            } catch (Exception e) {
                logger.error(String.format("Transaction: %s", GsonUtil.getGson().toJson(transaction)), e);
            }
        }
        return false;
    }
}
