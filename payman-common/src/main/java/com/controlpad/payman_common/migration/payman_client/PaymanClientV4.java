package com.controlpad.payman_common.migration.payman_client;

import com.controlpad.payman_common.migration.Migration;

import java.sql.Connection;
import java.sql.SQLException;
import java.sql.Statement;

public class PaymanClientV4 implements Migration {

    @Override
    public void migrate(Connection connection) throws SQLException {
        try (Statement statement = connection.createStatement()) {
            statement.execute("ALTER TABLE transactions ADD column `for_txn_id` char(15) DEFAULT NULL");

            statement.execute("ALTER TABLE transactions ADD CONSTRAINT `t_t` FOREIGN KEY (`for_txn_id`) REFERENCES `transactions` (`id`)");

            statement.execute("INSERT INTO migrations(version, db_type) VALUES(4, 'payman_client')");
        }
    }
}
