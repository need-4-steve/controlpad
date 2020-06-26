package com.controlpad.payman_common.migration.payman_client;

import com.controlpad.payman_common.entry.Entry;
import com.controlpad.payman_common.migration.Migration;
import com.controlpad.payman_common.payment.PaymentType;
import com.controlpad.payman_common.transaction.Transaction;

import java.math.BigDecimal;
import java.sql.Connection;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.sql.Statement;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

public class PaymanClientV8 implements Migration {

    @Override
    public void migrate(Connection connection) throws SQLException {
        // Pull transactions that are processed and skip cash
        try(Statement statement = connection.createStatement()) {
            migrateTables(statement);
            migrateData(statement);
        }
    }

    private void migrateTables(Statement statement) throws SQLException {
        statement.execute("ALTER TABLE user_balances CHANGE COLUMN fees `transaction` DECIMAL(24,5) NOT NULL DEFAULT 0");
        statement.execute("ALTER TABLE user_balances CHANGE COLUMN refunds e_wallet DECIMAL(24,5) NOT NULL default 0");
        statement.execute("ALTER TABLE gateway_batches RENAME TO transaction_batches");
        statement.execute("ALTER TABLE transaction_batches ADD COLUMN `settled_at` timestamp null AFTER `payment_file_id`");
        statement.execute("ALTER TABLE transactions CHANGE COLUMN gateway_batch_id batch_id bigint(20)");
        statement.execute("CREATE TABLE `payment_type` (\n" +
                "  `id` INT(11) NOT NULL AUTO_INCREMENT,\n" +
                "  `name` TEXT NOT NULL,\n" +
                "  `slug` VARCHAR(32) NOT NULL,\n" +
                "  PRIMARY KEY (`id`),\n" +
                "  UNIQUE KEY `slug` (`slug`)\n" +
                ") ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");
        statement.execute("ALTER TABLE payout_batches RENAME TO payments");
        statement.execute("ALTER TABLE payments DROP FOREIGN KEY `pb_type`");
        statement.execute("DELETE FROM transaction_payouts WHERE payout_type_id = 7");
        statement.execute("DELETE FROM payments WHERE type_id = 7");
        statement.execute("DELETE FROM payments WHERE type_id = 15");
        statement.execute("UPDATE payments SET type_id = 9 WHERE type_id IN (4, 15)");
        statement.execute("UPDATE payments SET type_id = 7 WHERE type_id = 10");
        statement.execute("UPDATE payments SET type_id = 6 WHERE type_id = 12");
        statement.execute("CREATE TABLE `entries` (\n" +
                "  `id` BIGINT(20) NOT NULL AUTO_INCREMENT,\n" +
                "  `balance_id` BIGINT(20) NOT NULL,\n" +
                "  `amount` DECIMAL(24,5) NOT NULL,\n" +
                "  `transaction_id` CHAR(15) DEFAULT NULL,\n" +
                "  `fee_id` BIGINT(20) DEFAULT NULL,\n" +
                "  `payment_id` CHAR(15) DEFAULT NULL,\n" +
                "  `type_id` INT(11) NOT NULL,\n" +
                "  `processed` TINYINT(1) NOT NULL DEFAULT '0',\n" +
                "  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,\n" +
                "  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,\n" +
                "  PRIMARY KEY (`id`),\n" +
                "  KEY `entry_balance` (`balance_id`),\n" +
                "  KEY `entry_transaction` (`transaction_id`),\n" +
                "  KEY `entry_fee` (`fee_id`),\n" +
                "  KEY `entry_payment` (`payment_id`),\n" +
                "  CONSTRAINT `entry_balance` FOREIGN KEY (`balance_id`) REFERENCES `user_balances` (`id`),\n" +
                "  CONSTRAINT `entry_fee` FOREIGN KEY (`fee_id`) REFERENCES `fees` (`id`),\n" +
                "  CONSTRAINT `entry_payment` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`),\n" +
                "  CONSTRAINT `entry_transaction` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`),\n" +
                "  CONSTRAINT `entry_type` FOREIGN KEY (`type_id`) REFERENCES `payment_type` (`id`)\n" +
                ") ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");
        statement.execute("INSERT INTO `payment_type` VALUES\n" +
                "(1,'Account Verification','account-verification'),\n" +
                "(2,'ACH Credit','ach-credit'),\n" +
                "(3,'Affiliate','affiliate'),\n" +
                "(4,'Commission', 'commission'),\n" +
                "(5,'Consignment','consignment'),\n" +
                "(6,'Withdraw','withdraw'),\n" +
                "(7,'Fee','fee'),\n" +
                "(8,'Merchant','merchant'),\n" +
                "(9,'Sales Tax','sales-tax'),\n" +
                "(10, 'Failed Payment', 'failed-payment');\n");
        statement.execute("ALTER TABLE payments ADD CONSTRAINT `payment_type` FOREIGN KEY (`type_id`) REFERENCES `payment_type` (`id`)");
        statement.execute("INSERT INTO `transaction_type` VALUES (63, 'Card Payment to Tax Balance', 'card-payment-tax')");
        statement.execute("ALTER TABLE transaction_charges CHANGE COLUMN payout_type_id type_id int(11) NOT NULL");
        statement.execute("ALTER TABLE transaction_charges DROP FOREIGN KEY `tc_type`");
        statement.execute("UPDATE transaction_charges SET type_id = 9 WHERE type_id IN (1, 4)");
        statement.execute("UPDATE transaction_charges SET type_id = 7 WHERE type_id IN (3, 10)");
        statement.execute("UPDATE transaction_charges SET type_id = 3 WHERE type_id = 13");
        statement.execute("ALTER TABLE transaction_charges ADD CONSTRAINT `tc_type` FOREIGN KEY (`type_id`) REFERENCES `payment_type` (`id`)");
        statement.execute("ALTER TABLE transaction_charges ADD COLUMN payment_id char(15) DEFAULT NULL");
        statement.execute("ALTER TABLE transaction_charges ADD CONSTRAINT `tc_payment` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`)");
        statement.execute("ALTER TABLE transaction_charges CHANGE COLUMN paid processed tinyint(1) NOT NULL DEFAULT '0'");
        statement.execute("ALTER TABLE transaction_charges CHANGE COLUMN `amount` `amount` decimal(24,5) NOT NULL");

        statement.execute("INSERT INTO migrations(version, db_type) VALUES(8, 'payman_client')");

    }

    private void migrateData(Statement statement) throws SQLException {
        List<Transaction> transactions = new ArrayList<>();
        ResultSet resultSet = statement.executeQuery("SELECT * FROM transactions WHERE status_code = 'S' AND processed = 1 AND transaction_type_id <> 1");
        Transaction transaction;
        while(resultSet.next()) {
            transaction = new Transaction(resultSet.getString("id"), resultSet.getString("payee_user_id"),
                    resultSet.getString("team_id"), null, resultSet.getBigDecimal("amount"),
                    null, null, 1, null, null);
            transaction.setCreatedAt(resultSet.getString("created_at"));
            transactions.add(transaction);
        }
        resultSet.close();
        List<Entry> entries = new ArrayList<>();
        for (Transaction currentTransaction : transactions) {
            // Get or create balance
            Long userBalanceId = getUserBalanceId(statement, currentTransaction.getPayeeUserId(), currentTransaction.getTeamId());
            // Create merchant entry
            entries.add(new Entry(userBalanceId, currentTransaction.getAmount(), currentTransaction.getId(), null,
                    null, PaymentType.MERCHANT.slug, true, currentTransaction.getCreatedAt()));

            // Move transaction fees to entries
            resultSet = statement.executeQuery(String.format("SELECT -amount FROM transaction_fees WHERE transaction_id = '%s'", currentTransaction.getId()));
            while(resultSet.next()) {
                entries.add(new Entry(userBalanceId, resultSet.getBigDecimal(1),
                        currentTransaction.getId(), null, null, PaymentType.FEE.slug, true, currentTransaction.getCreatedAt()));
            }
            resultSet.close();

            // Collect payouts
            List<Map<Integer, Object>> payouts = new ArrayList<>();
            resultSet = statement.executeQuery(String.format("SELECT user_id, -amount, payout_batch_id, processed, created_at, payout_type_id" +
                            " FROM transaction_payouts WHERE transaction_id = '%s' AND payout_type_id IN (4, 6, 10, 12, 13)",
                    currentTransaction.getId()));
            while (resultSet.next()) {
                Map<Integer, Object> payout = new HashMap<>();
                payout.put(1, resultSet.getString(1));
                payout.put(2, resultSet.getBigDecimal(2));
                payout.put(3, resultSet.getString(3));
                payout.put(4, resultSet.getBoolean(4));
                payout.put(5, resultSet.getString(5));
                payout.put(6, resultSet.getInt(6));
                payouts.add(payout);

            }
            resultSet.close();

            // Move payouts to entries
            for (Map<Integer, Object> payout : payouts) {
                switch ((Integer)payout.get(6)) {
                    case 4:
                        entries.add(new Entry(userBalanceId, (BigDecimal)payout.get(2), currentTransaction.getId(),
                                null, (String)payout.get(3), PaymentType.SALES_TAX.slug, (boolean)payout.get(4),
                                (String)payout.get(5)));
                        break;
                    case 10:
                        entries.add(new Entry(userBalanceId, (BigDecimal)payout.get(2), currentTransaction.getId(),
                                null, (String)payout.get(3), PaymentType.FEE.slug, (boolean)payout.get(4),
                                (String)payout.get(5)));
                        break;
                    case 6:
                    case 12:
                        if (!(boolean)payout.get(4)) {
                            continue; // Skip payouts that were not processed
                        }
                        entries.add(new Entry(userBalanceId, (BigDecimal)payout.get(2), currentTransaction.getId(),
                                null, (String)payout.get(3), PaymentType.WITHDRAW.slug, (boolean)payout.get(4),
                                (String)payout.get(5)));
                        break;
                    case 13:
                        String userId = (String)payout.get(1);
                        BigDecimal amount = (BigDecimal)payout.get(2);
                        String paymentId = (String)payout.get(3);
                        Boolean processed = (boolean)payout.get(4);
                        String createdAt = (String)payout.get(5);
                        // get balance id for payee
                        Long payeeBalance = getUserBalanceId(statement, userId, currentTransaction.getTeamId());
                        // create negative and positive entries
                        entries.add(new Entry(userBalanceId, amount, currentTransaction.getId(), null,
                                null, PaymentType.AFFILIATE.slug, true, createdAt));
                        entries.add(new Entry(payeeBalance, amount.negate(), currentTransaction.getId(), null,
                                null, PaymentType.AFFILIATE.slug, true, createdAt));
                        if ((boolean)payout.get(4)) {
                            // Only add the payout if it had been processed
                            entries.add(new Entry(payeeBalance, amount, currentTransaction.getId(), null, paymentId
                                    , PaymentType.WITHDRAW.slug, processed, createdAt));
                        }
                        break;
                }
            }

            // Move transaction charge tax payments to entries
            resultSet = statement.executeQuery(String.format("SELECT -amount, processed FROM transaction_charges" +
                    " WHERE transaction_id = '%s' AND type_id = 9 LIMIT 1", currentTransaction.getId()));
            if (resultSet.next()) {
                entries.add(new Entry(userBalanceId, resultSet.getBigDecimal(1), currentTransaction.getId(),
                        null, null, PaymentType.SALES_TAX.slug, resultSet.getBoolean(2), currentTransaction.getCreatedAt()));
            }
            resultSet.close();

            // Delete tax charge that was moved
            statement.execute(String.format("DELETE FROM transaction_charges WHERE transaction_id = '%s' AND type_id = 9 LIMIT 1", currentTransaction.getId()));
        }

        // store entries
        StringBuilder entryBuilder = new StringBuilder();

        if (!entries.isEmpty()) {
            for (Entry entry : entries) {
                entryBuilder.append("(");
                entryBuilder.append(entry.getBalanceId()).append(",");
                entryBuilder.append(entry.getAmount()).append(",");
                if (entry.getTransactionId() != null) {
                    entryBuilder.append("'").append(entry.getTransactionId()).append("',");
                } else {
                    entryBuilder.append("null,");
                }
                entryBuilder.append(entry.getFeeId()).append(",");
                entryBuilder.append("'").append(entry.getCreatedAt()).append("',");
                if (entry.getPaymentId() != null) {
                    entryBuilder.append("'").append(entry.getPaymentId()).append("',");
                } else {
                    entryBuilder.append("null,");
                }
                entryBuilder.append(entry.getTypeId()).append(",");
                entryBuilder.append(entry.getProcessed()); // Make sure this is a 1 or 0?
                entryBuilder.append("),");
            }
            entryBuilder.deleteCharAt(entryBuilder.length() - 1); // Delete trailing comma
            statement.execute(String.format("INSERT INTO entries(balance_id, amount, transaction_id, fee_id, created_at, payment_id, type_id, processed)" +
                    " VALUES%s", entryBuilder.toString()));

            // Update balances based on entries
            statement.execute("UPDATE user_balances AS ub," +
                    " (SELECT balance_id, SUM(amount) AS e_wallet, SUM(CASE WHEN processed = 1 THEN amount ELSE 0 END) AS `transaction` FROM entries GROUP BY balance_id) AS e" +
                    " SET ub.e_wallet = e.e_wallet, ub.transaction = e.transaction WHERE ub.id = e.balance_id");
        }
    }

    private Long getUserBalanceId(Statement statement, String userId, String teamId) throws SQLException {
        ResultSet resultSet = statement.executeQuery(String.format("SELECT id FROM user_balances WHERE user_id = '%s' AND team_id = '%s'", userId, teamId));
        if (resultSet.first()) {
            return resultSet.getLong("id");
        } else {
            resultSet.close();
            statement.execute(String.format("INSERT INTO user_balances(user_id, team_id) VALUES('%s','%s')", userId, teamId));
            resultSet = statement.executeQuery("SELECT LAST_INSERT_ID()");
            if(resultSet.first()) {
                Long id = resultSet.getLong(1);
                resultSet.close();
                return id;
            }else {
                resultSet.close();
                throw new RuntimeException("Failed to create user balance for v8 migration");
            }
        }
    }

    private void updateBalance(Statement statement, Long userBalanceId, BigDecimal transaction, BigDecimal eWallet) throws SQLException{
        statement.execute(String.format("UPDATE user_balances SET `transaction` = `transaction` + %f, e_wallet = e_wallet + %f WHERE id = %d",
                transaction, eWallet, userBalanceId));
    }
}
