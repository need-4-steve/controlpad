package com.controlpad.payman_common.migration.payman_client;

import com.controlpad.payman_common.migration.Migration;

import java.sql.Connection;
import java.sql.SQLException;
import java.sql.Statement;

public class PaymanClientV22 implements Migration {
    @Override
    public void migrate(Connection connection) throws SQLException {
        try (Statement statement = connection.createStatement()) {

            statement.execute("ALTER TABLE transactions" +
                    " ADD COLUMN `result_code` INTEGER UNSIGNED DEFAULT 1 AFTER status_code," +
                    " ADD INDEX(result_code)");

            statement.execute("UPDATE transactions SET result_code = 1");

            statement.execute("INSERT INTO migrations(version, db_type) VALUES(22, 'payman_client')");
        }
    }
}
