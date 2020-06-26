package com.controlpad.payman_common.migration.payman_client;

import com.controlpad.payman_common.migration.Migration;

import java.sql.Connection;
import java.sql.SQLException;
import java.sql.Statement;

public class PaymanClientV16 implements Migration {

    @Override
    public void migrate(Connection connection) throws SQLException {
        try (Statement statement = connection.createStatement()) {
            statement.execute("CREATE TABLE payment_providers(" +
                    "`id` BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT," +
                    " `name` VARCHAR(255)," +
                    " `type` VARCHAR(64)," +
                    " `credentials` BLOB(255)," +
                    " `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP," +
                    " `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP" +
                    ") ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");

            statement.execute("ALTER TABLE teams ADD COLUMN payment_provider_id BIGINT UNSIGNED DEFAULT NULL," +
                    " ADD CONSTRAINT `team_pp` FOREIGN KEY `payment_provider_id`(`payment_provider_id`) REFERENCES `payment_providers`(`id`)");

            statement.execute("INSERT INTO migrations(version, db_type) VALUES(16, 'payman_client')");
        }
    }
}