package com.controlpad.pay_fac.transaction;

import com.controlpad.pay_fac.exceptions.InsertionException;
import com.controlpad.pay_fac.util.IDUtil;
import com.controlpad.payman_common.affiliate_charge.AffiliateCharge;
import com.controlpad.payman_common.affiliate_charge.AffiliateChargeMapper;
import com.controlpad.payman_common.transaction.Transaction;
import com.controlpad.payman_common.transaction.TransactionMapper;
import org.apache.ibatis.session.SqlSession;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import java.util.List;

public class TransactionUtil {

    private static final Logger logger = LoggerFactory.getLogger(TransactionUtil.class);

    private static final InsertionException<Transaction> insertionException = new InsertionException<>();

    public static void insertTransaction(SqlSession session, Transaction transaction, IDUtil idUtil, List<AffiliateCharge> affiliateChargeList) {
        TransactionMapper transactionMapper = session.getMapper(TransactionMapper.class);
        insertTransactionTry(transactionMapper, transaction, idUtil);
        if (affiliateChargeList != null && !affiliateChargeList.isEmpty()) {
            for (AffiliateCharge affiliateCharge : affiliateChargeList) {
                if (transaction.getTransactionType().equals("refund")) {
                    affiliateCharge.negateAmount();
                }
                affiliateCharge.setTransactionId(transaction.getId());
            }
            session.getMapper(AffiliateChargeMapper.class).insertList(affiliateChargeList);
        }
    }

    private static void insertTransactionTry(TransactionMapper transactionMapper, Transaction transaction, IDUtil idUtil) {
        for (int i = 0; i < 3; i++) {
            try {
                transaction.setId(idUtil.generateId());
                transactionMapper.insert(transaction);
                break;
            } catch (Exception e) {
                logger.error(String.format("Transaction: %s", transaction), e);
                insertionException.handle(e, transaction);
            }
        }
    }
}
