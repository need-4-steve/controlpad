package com.controlpad.payman_common.migration.payman;


import com.controlpad.payman_common.migration.Migration;

import java.sql.Connection;
import java.sql.SQLException;
import java.sql.Statement;

public class PaymanV5 implements Migration {

    @Override
    public void migrate(Connection connection) throws SQLException {
        try (Statement statement = connection.createStatement()) {
            statement.execute("ALTER TABLE clients DROP COLUMN sql_config");


            statement.execute("INSERT INTO migrations(version, db_type) VALUES(5, 'payman')");
        }

    }
}
