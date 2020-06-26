package com.controlpad.payman_common.migration.payman_client;

import com.controlpad.payman_common.migration.Migration;

import java.sql.Connection;
import java.sql.SQLException;
import java.sql.Statement;

public class PaymanClientV7 implements Migration {

    @Override
    public void migrate(Connection connection) throws SQLException {
        // Switch to decimal
        try (Statement statement = connection.createStatement()) {
            statement.execute("ALTER TABLE affiliate_charges CHANGE COLUMN amount amount DECIMAL(24,5) UNSIGNED NOT NULL");
            statement.execute("ALTER TABLE company_credits CHANGE COLUMN balance balance DECIMAL(24,5) NOT NULL DEFAULT 0");
            statement.execute("ALTER TABLE consignments CHANGE COLUMN amount amount DECIMAL(24,4) UNSIGNED NOT NULL");
            statement.execute("ALTER TABLE consignments CHANGE COLUMN balance balance DECIMAL(24,5) UNSIGNED NOT NULL");
            statement.execute("ALTER TABLE e_wallets CHANGE COLUMN balance balance DECIMAL(24,5) NOT NULL");
            statement.execute("ALTER TABLE e_wallets CHANGE COLUMN amount amount DECIMAL(24,4) UNSIGNED NOT NULL");
            statement.execute("ALTER TABLE fees CHANGE COLUMN amount amount DECIMAL(24,4) UNSIGNED NOT NULL");
            statement.execute("ALTER TABLE payment_files CHANGE COLUMN credits credits DECIMAL(24,2) UNSIGNED NOT NULL");
            statement.execute("ALTER TABLE payment_files CHANGE COLUMN debits debits DECIMAL(24,2) UNSIGNED NOT NULL");
            statement.execute("ALTER TABLE payment_files CHANGE COLUMN e_wallet_credits e_wallet_credits DECIMAL(24,5) UNSIGNED NOT NULL");
            statement.execute("ALTER TABLE payment_files CHANGE COLUMN stay_credits stay_credits DECIMAL(24,5) UNSIGNED NOT NULL");
            statement.execute("ALTER TABLE payout_batches CHANGE COLUMN amount amount DECIMAL(24,5) UNSIGNED NOT NULL");
            statement.execute("ALTER TABLE team_credits CHANGE COLUMN balance balance DECIMAL(24,5) NOT NULL DEFAULT 0");
            statement.execute("ALTER TABLE transaction_charges CHANGE COLUMN amount amount DECIMAL(24,5) UNSIGNED NOT NULL");
            statement.execute("ALTER TABLE transaction_debits CHANGE COLUMN amount amount DECIMAL(24,2) UNSIGNED NOT NULL");
            statement.execute("ALTER TABLE transaction_fees CHANGE COLUMN amount amount DECIMAL(24,5) UNSIGNED NOT NULL");
            statement.execute("ALTER TABLE transaction_payouts CHANGE COLUMN amount amount DECIMAL(24,5) UNSIGNED NOT NULL");
            statement.execute("ALTER TABLE transactions CHANGE COLUMN amount amount DECIMAL(24,2) UNSIGNED NOT NULL");
            statement.execute("ALTER TABLE transactions CHANGE COLUMN sales_tax sales_tax DECIMAL(24,2) UNSIGNED");
            statement.execute("ALTER TABLE transactions CHANGE COLUMN shipping shipping DECIMAL(24,2) UNSIGNED");
            statement.execute("ALTER TABLE user_account_validation CHANGE COLUMN amount1 amount1 DECIMAL(2,2) UNSIGNED NOT NULL");
            statement.execute("ALTER TABLE user_account_validation CHANGE COLUMN amount2 amount2 DECIMAL(2,2) UNSIGNED NOT NULL");
            statement.execute("ALTER TABLE user_balances CHANGE COLUMN fees fees DECIMAL(24,5) NOT NULL DEFAULT 0");

            statement.execute("INSERT INTO migrations(version, db_type) VALUES(7, 'payman_client')");
        }
    }
}