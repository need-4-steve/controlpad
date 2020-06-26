package com.controlpad.payman_common.migration.payman;

import com.controlpad.payman_common.migration.Migration;

import java.sql.Connection;
import java.sql.SQLException;
import java.sql.Statement;


public class PaymanV1 implements Migration {

    @Override
    public void migrate(Connection connection) throws SQLException {
        try(Statement statement = connection.createStatement()) {
            statement.execute("CREATE TABLE `clients` (" +
                    "  `position` bigint(20) NOT NULL AUTO_INCREMENT," +
                    "  `id` varchar(15) NOT NULL," +
                    "  `name` varchar(255) DEFAULT NULL," +
                    "  `config` text," +
                    "  `sql_config` varbinary(256) NOT NULL," +
                    "  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP," +
                    "  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP," +
                    "  `is_sandbox` tinyint(1) NOT NULL DEFAULT '0'," +
                    "                    PRIMARY KEY (`position`)," +
                    "                    UNIQUE KEY `id` (`id`)" +
                    ") ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");

            statement.execute("CREATE TABLE `users` (" +
                    "  `position` bigint(20) NOT NULL AUTO_INCREMENT," +
                    "  `id` varchar(15) NOT NULL," +
                    "  `username` varchar(255) NOT NULL," +
                    "  `password` varchar(255) DEFAULT NULL," +
                    "  `email` varchar(255) DEFAULT NULL," +
                    "  `client_id` varchar(15) DEFAULT NULL," +
                    "  `privilege` text," +
                    "  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP," +
                    "  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP," +
                    "            PRIMARY KEY (`position`)," +
                    "            UNIQUE KEY `id` (`id`)," +
                    "            UNIQUE KEY `username` (`username`)," +
                    "            KEY `client_id` (`client_id`)," +
                    "            CONSTRAINT `user_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`)" +
                    ") ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");

            statement.execute("CREATE TABLE `api_keys` (" +
                    "  `id` varchar(128) NOT NULL," +
                    "  `client_id` varchar(15) NOT NULL," +
                    "  `config` text," +
                    "  `disabled` tinyint(1) NOT NULL DEFAULT '0'," +
                    "  `deleted` tinyint(1) NOT NULL DEFAULT '0'," +
                    "  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP," +
                    "  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP," +
                    "            PRIMARY KEY (`id`)," +
                    "            KEY `client_id` (`client_id`)," +
                    "            CONSTRAINT `apikey_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`)" +
                    ") ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");

            statement.execute("CREATE TABLE `sessions` (" +
                    "  `id` varchar(128) NOT NULL," +
                    "  `user_id` varchar(15) NOT NULL," +
                    "  `client_id` varchar(15) DEFAULT NULL," +
                    "  `expires_at` bigint(20) NOT NULL," +
                    "  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP," +
                    "  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP," +
                    "            PRIMARY KEY (`id`)," +
                    "            KEY `user_id` (`user_id`)," +
                    "            KEY `client_id` (`client_id`)," +
                    "            CONSTRAINT `session_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)," +
                    "            CONSTRAINT `session_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`)" +
                    ") ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");
        }
    }
}
