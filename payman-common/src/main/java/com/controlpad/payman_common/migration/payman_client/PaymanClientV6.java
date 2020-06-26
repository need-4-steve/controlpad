package com.controlpad.payman_common.migration.payman_client;

import com.controlpad.payman_common.migration.Migration;

import java.sql.Connection;
import java.sql.SQLException;
import java.sql.Statement;

public class PaymanClientV6 implements Migration {

    @Override
    public void migrate(Connection connection) throws SQLException {
        try (Statement statement = connection.createStatement()) {
            statement.execute("INSERT INTO transactions (id, team_id, payee_user_id, for_txn_id, amount, status_code, transaction_type_id, gateway_reference_id, created_at, updated_at)\n" +
                    " SELECT r.id, t.team_id, t.payee_user_id, r.transaction_id, r.amount, r.status_code, 90, r.external_id, r.created_at, r.updated_at FROM refunds AS r\n" +
                    " JOIN transactions AS t ON r.transaction_id = t.id");

            statement.execute("INSERT INTO user_balances(user_id, team_id, sales_tax)\n" +
                    " SELECT user_id, 'company', balance FROM user_tax_balance");

            statement.execute("DROP TABLE refunds");

            statement.execute("DROP TABLE user_tax_balance");

            statement.execute("INSERT INTO migrations(version, db_type) VALUES(6, 'payman_client')");
        }
    }
}
