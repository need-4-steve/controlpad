package com.controlpad.payman_common.migration.payman_client;

import com.controlpad.payman_common.migration.Migration;

import java.sql.Connection;
import java.sql.SQLException;
import java.sql.Statement;

public class PaymanClientV15 implements Migration {

    @Override
    public void migrate(Connection connection) throws SQLException {
        try (Statement statement = connection.createStatement()) {
            // Add order_id to transactions
            statement.execute("ALTER TABLE transactions ADD COLUMN `order_id` VARCHAR(255)");

            // Fix relation for payments
            statement.execute("ALTER TABLE payments DROP FOREIGN KEY payment_type");
            statement.execute("ALTER TABLE payments ADD CONSTRAINT `payment_type` FOREIGN KEY (`type_id`) REFERENCES `payment_type` (`id`)");

            // Add indexes
            statement.execute("ALTER TABLE affiliate_charges ADD INDEX(payee_user_id)");
            statement.execute("ALTER TABLE entries ADD INDEX(created_at)");
            statement.execute("ALTER TABLE gateway_connection_type ADD INDEX(slug)");
            statement.execute("ALTER TABLE gateway_connections ADD INDEX(team_id)");
            statement.execute("ALTER TABLE gateway_connections ADD INDEX(user_id)");
            statement.execute("ALTER TABLE gateway_connections ADD INDEX(funds_company)");
            statement.execute("ALTER TABLE gateway_connections ADD INDEX(process_cards)");
            statement.execute("ALTER TABLE gateway_connections ADD INDEX(process_checks)");
            statement.execute("ALTER TABLE gateway_connections ADD INDEX(process_internal)");
            statement.execute("ALTER TABLE gateway_connections ADD INDEX(master_connection_id)");
            statement.execute("ALTER TABLE gateway_connections ADD INDEX(active)");
            statement.execute("ALTER TABLE payment_files ADD INDEX(created_at)");
            statement.execute("ALTER TABLE payment_files ADD INDEX(submitted_at)");
            statement.execute("ALTER TABLE payments ADD INDEX(user_id)");
            statement.execute("ALTER TABLE payments ADD INDEX(reference_id)");
            statement.execute("ALTER TABLE payments ADD INDEX(paid_at)");
            statement.execute("ALTER TABLE payments ADD INDEX(returned)");
            statement.execute("ALTER TABLE payments ADD INDEX(created_at)");
            statement.execute("ALTER TABLE payout_jobs ADD INDEX(start_at)");
            statement.execute("ALTER TABLE payout_jobs ADD INDEX(status)");
            statement.execute("ALTER TABLE payout_jobs ADD INDEX(payout_scheme)");
            statement.execute("ALTER TABLE payout_jobs ADD INDEX(created_at)");
            statement.execute("ALTER TABLE team_credits ADD INDEX(user_id)");
            statement.execute("ALTER TABLE transaction_batches ADD INDEX(external_id)");
            statement.execute("ALTER TABLE transaction_batches ADD INDEX(status)");
            statement.execute("ALTER TABLE transaction_batches ADD INDEX(settled_at)");
            statement.execute("ALTER TABLE transaction_charges ADD INDEX(user_id)");
            statement.execute("ALTER TABLE transaction_charges ADD INDEX(processed)");
            statement.execute("ALTER TABLE transaction_debits ADD INDEX(user_id)");
            statement.execute("ALTER TABLE transaction_debits ADD CONSTRAINT `td_pf` FOREIGN KEY (`payment_file_id`) REFERENCES `payment_files` (`id`)");
            statement.execute("ALTER TABLE transaction_debits ADD INDEX(returned)");
            statement.execute("ALTER TABLE transaction_debits ADD INDEX(created_at)");
            statement.execute("ALTER TABLE transaction_fees ADD INDEX(gateway_reference_id)");
            statement.execute("ALTER TABLE transactions ADD INDEX(payee_user_id)");
            statement.execute("ALTER TABLE transactions ADD INDEX(payer_user_id)");
            statement.execute("ALTER TABLE transactions ADD INDEX(amount)");
            statement.execute("ALTER TABLE transactions ADD INDEX(sales_tax)");
            statement.execute("ALTER TABLE transactions ADD INDEX(shipping)");
            statement.execute("ALTER TABLE transactions ADD INDEX(account_holder)");
            statement.execute("ALTER TABLE transactions ADD INDEX(order_id)");
            statement.execute("ALTER TABLE transactions ADD INDEX(status_code)");
            statement.execute("ALTER TABLE transactions ADD INDEX(processed)");
            statement.execute("ALTER TABLE transactions ADD INDEX(description)");
            statement.execute("ALTER TABLE transactions ADD INDEX(gateway_reference_id)");
            statement.execute("ALTER TABLE transactions ADD INDEX(created_at)");
            statement.execute("ALTER TABLE transactions ADD INDEX(swiped)");
            statement.execute("ALTER TABLE user_accounts ADD INDEX(user_id)");
            statement.execute("ALTER TABLE user_accounts ADD INDEX(name)");
            statement.execute("ALTER TABLE user_accounts ADD INDEX(validated)");
            statement.execute("ALTER TABLE user_balances ADD INDEX(e_wallet)");
            statement.execute("ALTER TABLE user_balances ADD INDEX(sales_tax)");
            statement.execute("ALTER TABLE user_balances ADD INDEX(transaction)");

            statement.execute("DROP TABLE e_wallets");
            statement.execute("DROP TABLE payout_batch_chain");
            statement.execute("DROP TABLE transaction_payouts");
            statement.execute("DROP TABLE transaction_payout_type");

            statement.execute("INSERT INTO migrations(version, db_type) VALUES(15, 'payman_client')");
        }
    }

}