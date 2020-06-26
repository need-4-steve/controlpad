package com.controlpad.payman_common.migration.payman_client;

import com.controlpad.payman_common.migration.Migration;
import com.controlpad.payman_common.payment.PaymentType;
import com.controlpad.payman_common.transaction.TransactionType;

import java.sql.Connection;
import java.sql.SQLException;
import java.sql.Statement;

public class PaymanClientV11 implements Migration {

    @Override
    public void migrate(Connection connection) throws SQLException {
        try (Statement statement = connection.createStatement()) {
            // Add transaction type e_wallet debit
            String transactionQuery = String.format("INSERT INTO transaction_type(id, name, slug)" +
                    " VALUES(%d, '%s', '%s')",
                    TransactionType.E_WALLET_DEBIT.id,
                    "E-Wallet Debit",
                    TransactionType.E_WALLET_DEBIT.slug);
            statement.execute(transactionQuery);

            String paymentTransferQuery = String.format("INSERT INTO payment_type(id, name, slug)" +
                    " VALUES(%d, '%s', '%s')",
                    PaymentType.TRANSFER.id,
                    "Transfer",
                    PaymentType.TRANSFER.slug);
            statement.execute(paymentTransferQuery);

            statement.execute("INSERT INTO migrations(version, db_type) VALUES(11, 'payman_client')");
        }
    }
}
