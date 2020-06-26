package com.controlpad.payman_common.migration.payman_client;

import com.controlpad.payman_common.migration.Migration;

import java.sql.Connection;
import java.sql.SQLException;
import java.sql.Statement;

public class PaymanClientV20 implements Migration {
    @Override
    public void migrate(Connection connection) throws SQLException {
        try (Statement statement = connection.createStatement()) {
            statement.execute("CREATE TABLE payment_batches(" +
                    "`id` char(15) NOT NULL," +
                    "`description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL," +
                    "`team_id` varchar(32) NOT NULL," +
                    "`net_amount` double(12,2) NOT NULL DEFAULT '0.00'," +
                    "`payment_count` bigint NOT NULL DEFAULT 0," +
                    "`status` varchar(32) NOT NULL," +
                    "`submitted_at` DATETIME DEFAULT NULL," +
                    "`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,\n" +
                    "`updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,\n" +
                    "PRIMARY KEY (`id`)" +
                    ") ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");
            statement.execute("ALTER TABLE payments ADD COLUMN payment_batch_id char(15) DEFAULT NULL," +
                    " ADD CONSTRAINT `p_pb` FOREIGN KEY `payment_batch_id`(`payment_batch_id`) REFERENCES `payment_batches`(`id`)");
            statement.execute("ALTER TABLE payout_jobs ADD COLUMN payment_batch_id char(15) DEFAULT NULL," +
                    " ADD CONSTRAINT `pj_pb` FOREIGN KEY `payment_batch_id`(`payment_batch_id`) REFERENCES `payment_batches`(`id`)");

            statement.execute("INSERT INTO migrations(version, db_type) VALUES(20, 'payman_client')");
        }
    }
}
