package com.controlpad.payman_common.migration.payman_client;

import com.controlpad.payman_common.migration.Migration;

import java.sql.Connection;
import java.sql.SQLException;
import java.sql.Statement;

public class PaymanClientV10 implements Migration {
    @Override
    public void migrate(Connection connection) throws SQLException {
        // Add reference id to fees and swiped to transactions
        try (Statement statement = connection.createStatement()) {
            // Add card swipe sale as a type to work around fees for card swipe until fees can be refactored
            statement.execute("INSERT INTO transaction_type(id, name, slug) VALUES(5, 'Card Swipe Sale', 'card_swipe_sale')");
            statement.execute("ALTER TABLE transactions ADD COLUMN `swiped` TINYINT(1)");
            statement.execute("ALTER TABLE fees ADD COLUMN `reference_id` VARCHAR(32)");

            statement.execute("INSERT INTO migrations(version, db_type) VALUES(10, 'payman_client')");
        }
    }
}