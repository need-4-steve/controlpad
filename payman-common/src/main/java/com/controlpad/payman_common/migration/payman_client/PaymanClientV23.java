package com.controlpad.payman_common.migration.payman_client;

import com.controlpad.payman_common.migration.Migration;

import java.sql.Connection;
import java.sql.SQLException;
import java.sql.Statement;

public class PaymanClientV23 implements Migration {
    @Override
    public void migrate(Connection connection) throws SQLException {
        try (Statement statement = connection.createStatement()) {

            statement.execute("INSERT INTO gateway_connection_type(id, name, slug) VALUES(11, 'EMVIO', 'emvio')");

            statement.execute("INSERT INTO migrations(version, db_type) VALUES(23, 'payman_client')");
        }
    }
}
