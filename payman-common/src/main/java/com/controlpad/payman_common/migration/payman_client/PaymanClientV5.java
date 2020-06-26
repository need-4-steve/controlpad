package com.controlpad.payman_common.migration.payman_client;

import com.controlpad.payman_common.migration.Migration;

import java.sql.Connection;
import java.sql.SQLException;
import java.sql.Statement;

public class PaymanClientV5 implements Migration {

    @Override
    public void migrate(Connection connection) throws SQLException {
        try (Statement statement = connection.createStatement()) {
            statement.execute("ALTER TABLE user_balances DROP PRIMARY KEY");

            statement.execute("ALTER TABLE user_balances ADD COLUMN id bigint(20) NOT NULL PRIMARY KEY AUTO_INCREMENT FIRST");

            statement.execute("ALTER TABLE user_balances ADD COLUMN team_id varchar(32) NOT NULL AFTER user_id");

            statement.execute("ALTER TABLE user_balances ADD CONSTRAINT `ub_team` FOREIGN KEY `team_id`(`team_id`) REFERENCES `teams`(`id`)");

            statement.execute("ALTER TABLE user_balances ADD UNIQUE `user_team`(`user_id`, `team_id`)");

            statement.execute("INSERT INTO transaction_type(id, name, slug) VALUES(94, 'Void', 'void')");

            statement.execute("INSERT INTO migrations(version, db_type) VALUES(5, 'payman_client')");
        }
    }
}
