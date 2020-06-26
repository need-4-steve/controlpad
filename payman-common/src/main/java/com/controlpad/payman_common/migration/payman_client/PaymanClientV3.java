package com.controlpad.payman_common.migration.payman_client;

import com.controlpad.payman_common.migration.Migration;

import java.sql.Connection;
import java.sql.SQLException;
import java.sql.Statement;

public class PaymanClientV3 implements Migration {

    @Override
    public void migrate(Connection connection) throws SQLException {
        try (Statement statement = connection.createStatement()) {
            statement.execute("CREATE TABLE `user_balances` (\n" +
                    "  `user_id` varchar(255) NOT NULL,\n" +
                    "  `sales_tax` DECIMAL(24,2) NOT NULL DEFAULT '0.00',\n" +
                    "  `refunds` DECIMAL(24,2) NOT NULL DEFAULT '0.00',\n" +
                    "  `fees` DECIMAL(24,2) NOT NULL DEFAULT '0.00',\n" +
                    "  PRIMARY KEY (`user_id`)\n" +
                    ") ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");

            statement.execute("INSERT INTO transaction_type VALUES (90, 'Refund', 'refund'),(91, 'Cash Refund', 'cash-refund')");

            statement.execute("INSERT INTO migrations(version, db_type) VALUES(3, 'payman_client')");
        }
    }
}
