package com.controlpad.payman_common.migration.payman_client;


import com.controlpad.payman_common.migration.Migration;

import java.sql.Connection;
import java.sql.SQLException;
import java.sql.Statement;

public class PaymanClientV1 implements Migration {

    @Override
    public void migrate(Connection connection) throws SQLException {
        try(Statement statement = connection.createStatement()) {
            statement.execute("CREATE TABLE `account_type` (\n" +
                    "  `id` int(11) NOT NULL AUTO_INCREMENT,\n" +
                    "  `name` varchar(32) DEFAULT NULL,\n" +
                    "  `slug` varchar(32) DEFAULT NULL,\n" +
                    "  PRIMARY KEY (`id`),\n" +
                    "  UNIQUE KEY `slug` (`slug`)\n" +
                    ") ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");

            statement.execute("CREATE TABLE `transaction_payout_type` (\n" +
                    "  `id` int(11) NOT NULL AUTO_INCREMENT,\n" +
                    "  `name` text NOT NULL,\n" +
                    "  `slug` varchar(32) NOT NULL,\n" +
                    "  PRIMARY KEY (`id`),\n" +
                    "  UNIQUE KEY `slug` (`slug`)\n" +
                    ") ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");

            statement.execute("CREATE TABLE `transaction_type` (\n" +
                    "  `id` int(11) NOT NULL,\n" +
                    "  `name` varchar(40) NOT NULL,\n" +
                    "  `slug` varchar(40) NOT NULL,\n" +
                    "  PRIMARY KEY (`id`),\n" +
                    "  UNIQUE KEY `slug` (`slug`)\n" +
                    ") ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");

            statement.execute("CREATE TABLE `gateway_connection_type` (\n" +
                    "  `id` int(11) NOT NULL,\n" +
                    "  `name` varchar(64) NOT NULL,\n" +
                    "  `slug` varchar(64) NOT NULL,\n" +
                    "  PRIMARY KEY (`id`)\n" +
                    ") ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");

            statement.execute("CREATE TABLE `accounts` (\n" +
                    "  `id` bigint(20) NOT NULL AUTO_INCREMENT,\n" +
                    "  `name` varchar(255) NOT NULL,\n" +
                    "  `number` varbinary(128) NOT NULL,\n" +
                    "  `routing` char(9) NOT NULL,\n" +
                    "  `bank_name` varchar(255),\n" +
                    "  `type_id` int(11) NOT NULL,\n" +
                    "  `hash` varchar(64) CHARACTER SET utf8 NOT NULL,\n" +
                    "  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,\n" +
                    "  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,\n" +
                    "  PRIMARY KEY (`id`),\n" +
                    "  KEY `type_id` (`type_id`),\n" +
                    "  CONSTRAINT `account_type` FOREIGN KEY (`type_id`) REFERENCES `account_type` (`id`)\n" +
                    ") ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");

            statement.execute("CREATE TABLE `ach` (\n" +
                    "  `id` bigint(20) NOT NULL,\n" +
                    "  `origin_route` text NOT NULL,\n" +
                    "  `destination_route` text NOT NULL,\n" +
                    "  `origin_name` text NOT NULL,\n" +
                    "  `company_name` text NOT NULL,\n" +
                    "  `company_id` text NOT NULL,\n" +
                    "  `destination_name` text NOT NULL,\n" +
                    "  PRIMARY KEY (`id`)\n" +
                    ") ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");

            statement.execute("CREATE TABLE `teams` (\n" +
                    "  `id` varchar(32) NOT NULL,\n" +
                    "  `name` varchar(255) NOT NULL,\n" +
                    "  `account_id` bigint(20) DEFAULT NULL,\n" +
                    "  `tax_account_id` bigint(20) DEFAULT NULL,\n" +
                    "  `consignment_account_id` bigint(20) DEFAULT NULL,\n" +
                    "  `config` text NOT NULL,\n" +
                    "  `payout_schedule` text,\n" +
                    "  `paid_on` char(10) DEFAULT NULL,\n" +
                    "  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,\n" +
                    "  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,\n" +
                    "  UNIQUE KEY (`id`),\n" +
                    "  KEY `account_id` (`account_id`),\n" +
                    "  KEY `tax_account_id` (`tax_account_id`),\n" +
                    "  KEY `consignment_account_id` (`consignment_account_id`),\n" +
                    "  CONSTRAINT `team_account` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`),\n" +
                    "  CONSTRAINT `team_tax_account` FOREIGN KEY (`tax_account_id`) REFERENCES `accounts` (`id`),\n" +
                    "  CONSTRAINT `team_consignment_account` FOREIGN KEY (`consignment_account_id`) REFERENCES `accounts` (`id`)\n" +
                    ") ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");

            statement.execute("CREATE TABLE `company_credits` (\n" +
                    "  `user_id` varchar(255) NOT NULL,\n" +
                    "  `balance` double(12,2) NOT NULL DEFAULT '0.00',\n" +
                    "  PRIMARY KEY (`user_id`)\n" +
                    ") ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");

            statement.execute("CREATE TABLE `team_credits` (\n" +
                    "  `user_id` varchar(255) NOT NULL,\n" +
                    "  `team_id` varchar(32) NOT NULL,\n" +
                    "  `balance` double(12,2) NOT NULL DEFAULT '0.00',\n" +
                    "  UNIQUE KEY `user_team` (`user_id`,`team_id`),\n" +
                    "  KEY `team_id` (`team_id`),\n" +
                    "  CONSTRAINT `tc_team` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`)\n" +
                    ") ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");

            statement.execute("CREATE TABLE `consignments` (\n" +
                    "  `user_id` varchar(255) NOT NULL,\n" +
                    "  `is_percent` tinyint(1) NOT NULL,\n" +
                    "  `amount` double(6,2) unsigned NOT NULL,\n" +
                    "  `balance` double(9,2) unsigned NOT NULL,\n" +
                    "  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,\n" +
                    "  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,\n" +
                    "  PRIMARY KEY (`user_id`)\n" +
                    ") ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");

            statement.execute("CREATE TABLE `e_wallets` (\n" +
                    "  `user_id` varchar(255) NOT NULL,\n" +
                    "  `balance` double(9,2) NOT NULL,\n" +
                    "  `amount` double(8,2) unsigned NOT NULL,\n" +
                    "  `is_percent` tinyint(1) NOT NULL,\n" +
                    "  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,\n" +
                    "  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,\n" +
                    "  PRIMARY KEY (`user_id`)\n" +
                    ") ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");

            statement.execute("CREATE TABLE `fees` (\n" +
                    "  `id` bigint(20) NOT NULL AUTO_INCREMENT,\n" +
                    "  `description` text NOT NULL,\n" +
                    "  `account_id` bigint(20) DEFAULT NULL,\n" +
                    "  `amount` double(9,2) unsigned NOT NULL,\n" +
                    "  `is_percent` tinyint(1) NOT NULL,\n" +
                    "  PRIMARY KEY (`id`),\n" +
                    "  KEY `account_id` (`account_id`),\n" +
                    "  CONSTRAINT `fee_account` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`)\n" +
                    ") ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");

            statement.execute("CREATE TABLE `payout_jobs` (\n" +
                    "  `id` bigint(20) NOT NULL AUTO_INCREMENT,\n" +
                    "  `start_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,\n" +
                    "  `team_id` varchar(32) NOT NULL,\n" +
                    "  `status` varchar(10) NOT NULL,\n" +
                    "  `payout_scheme` varchar(64) NOT NULL,\n" +
                    "  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,\n" +
                    "  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,\n" +
                    "  PRIMARY KEY (`id`),\n" +
                    "  KEY `team_id` (`team_id`),\n" +
                    "  CONSTRAINT `pj_team` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`)\n" +
                    ") ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");

            statement.execute("CREATE TABLE `payment_files` (\n" +
                    "  `id` bigint(20) NOT NULL AUTO_INCREMENT,\n" +
                    "  `file_name` varchar(255) NOT NULL,\n" +
                    "  `credits` double(12,2) NOT NULL,\n" +
                    "  `e_wallet_credits` DOUBLE(12,2) UNSIGNED NOT NULL,\n" +
                    "  `stay_credits` DOUBLE(12,2) UNSIGNED NOT NULL,\n" +
                    "  `debits` double(12,2) NOT NULL,\n" +
                    "  `batch_count` int(11) NOT NULL,\n" +
                    "  `description` varchar(64) NOT NULL,\n" +
                    "  `transaction_count` int(11) NOT NULL,\n" +
                    "  `entry_count` bigint(20) NOT NULL,\n" +
                    "  `team_id` varchar(32) DEFAULT NULL,\n" +
                    "  `ach_id` bigint(20) DEFAULT NULL,\n" +
                    "  `submitted_at` timestamp NULL DEFAULT NULL,\n" +
                    "  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,\n" +
                    "  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,\n" +
                    "  PRIMARY KEY (`id`),\n" +
                    "  KEY `fk_pf_team_id` (`team_id`),\n" +
                    "  KEY `fk_pf_ach_id` (`ach_id`),\n" +
                    "  CONSTRAINT `pf_ach` FOREIGN KEY (`ach_id`) REFERENCES `ach` (`id`),\n" +
                    "  CONSTRAINT `pf_team` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`)\n" +
                    ") ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");

            statement.execute("CREATE TABLE `gateway_connections` (\n" +
                    "  `id` bigint(20) NOT NULL AUTO_INCREMENT,\n" +
                    "  `team_id` varchar(32) NOT NULL,\n" +
                    "  `user_id` varchar(255) DEFAULT NULL,\n" +
                    "  `name` varchar(255) NOT NULL,\n" +
                    "  `username` varbinary(128) DEFAULT NULL,\n" +
                    "  `merchant_id` varchar(255) DEFAULT NULL,\n" +
                    "  `entity_id` varchar(255) DEFAULT NULL,\n" +
                    "  `private_key` varbinary(128) NOT NULL,\n" +
                    "  `public_key` varchar(255) DEFAULT NULL,\n" +
                    "  `pin` varbinary(128) DEFAULT NULL,\n" +
                    "  `type_id` int(11) NOT NULL,\n" +
                    "  `funds_company` tinyint(1) NOT NULL DEFAULT '1',\n" +
                    "  `process_cards` tinyint(1) NOT NULL DEFAULT '0',\n" +
                    "  `process_checks` tinyint(1) NOT NULL DEFAULT '0',\n" +
                    "  `process_internal` tinyint(1) NOT NULL DEFAULT '0',\n" +
                    "  `master_connection_id` bigint(20) DEFAULT NULL,\n" +
                    "  `fee_group_id` varchar(127) DEFAULT NULL,\n" +
                    "  `funds_master` tinyint(1) NOT NULL DEFAULT '0',\n" +
                    "  `is_sandbox` tinyint(1) NOT NULL DEFAULT '0',\n" +
                    "  `active` tinyint(1) NOT NULL DEFAULT '0',\n" +
                    "  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,\n" +
                    "  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,\n" +
                    "  PRIMARY KEY (`id`),\n" +
                    "  KEY `type` (`type_id`),\n" +
                    "  CONSTRAINT `gc_type` FOREIGN KEY (`type_id`) REFERENCES `gateway_connection_type` (`id`)\n" +
                    ") ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");

            statement.execute("CREATE TABLE `gateway_batches` (\n" +
                    "  `id` bigint(20) NOT NULL AUTO_INCREMENT,\n" +
                    "  `gateway_connection_id` bigint(20) NOT NULL,\n" +
                    "  `external_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,\n" +
                    "  `external_number` bigint(20) DEFAULT NULL,\n" +
                    "  `status` int(11) NOT NULL DEFAULT '0',\n" +
                    "  `payout_job_id` bigint(20) DEFAULT NULL,\n" +
                    "  `payment_file_id` bigint(20) DEFAULT NULL,\n" +
                    "  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,\n" +
                    "  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,\n" +
                    "  PRIMARY KEY (`id`),\n" +
                    "  KEY `gateway_connection_id` (`gateway_connection_id`),\n" +
                    "  KEY `payout_job_id` (`payout_job_id`),\n" +
                    "  KEY `payment_file_id` (`payment_file_id`),\n" +
                    "  CONSTRAINT `gb_connection` FOREIGN KEY (`gateway_connection_id`) REFERENCES `gateway_connections` (`id`),\n" +
                    "  CONSTRAINT `gb_payout_job` FOREIGN KEY (`payout_job_id`) REFERENCES `payout_jobs` (`id`),\n" +
                    "  CONSTRAINT `gb_payment_file` FOREIGN KEY (`payment_file_id`) REFERENCES `payment_files` (`id`)\n" +
                    ") ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");

            statement.execute("CREATE TABLE `transactions` (\n" +
                    "  `position` bigint(20) NOT NULL AUTO_INCREMENT,\n" +
                    "  `id` char(15) DEFAULT NULL,\n" +
                    "  `payee_user_id` varchar(255) DEFAULT NULL,\n" +
                    "  `payer_user_id` varchar(255) DEFAULT NULL,\n" +
                    "  `team_id` varchar(32) NOT NULL,\n" +
                    "  `transaction_type_id` int(11) NOT NULL,\n" +
                    "  `amount` double(11,2) unsigned NOT NULL,\n" +
                    "  `sales_tax` double(9,2) unsigned DEFAULT NULL,\n" +
                    "  `shipping` double(9,2) unsigned DEFAULT NULL,\n" +
                    "  `account_holder` varchar(255) DEFAULT NULL,\n" +
                    "  `status_code` char(1) NOT NULL,\n" +
                    "  `processed` tinyint(1) NOT NULL DEFAULT '0',\n" +
                    "  `description` varchar(255) DEFAULT NULL,\n" +
                    "  `gateway_reference_id` varchar(32) DEFAULT NULL,\n" +
                    "  `gateway_connection_id` bigint(20) DEFAULT NULL,\n" +
                    "  `gateway_batch_id` bigint(20) DEFAULT NULL,\n" +
                    "  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,\n" +
                    "  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,\n" +
                    "  PRIMARY KEY (`position`),\n" +
                    "  UNIQUE KEY `id` (`id`),\n" +
                    "  KEY `team_id` (`team_id`),\n" +
                    "  KEY `transaction_type_id` (`transaction_type_id`),\n" +
                    "  KEY `gateway_connection_id` (`gateway_connection_id`),\n" +
                    "  KEY `batch_id` (`gateway_batch_id`),\n" +
                    "  CONSTRAINT `t_team` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`),\n" +
                    "  CONSTRAINT `t_type` FOREIGN KEY (`transaction_type_id`) REFERENCES `transaction_type` (`id`),\n" +
                    "  CONSTRAINT `t_gc` FOREIGN KEY (`gateway_connection_id`) REFERENCES `gateway_connections` (`id`),\n" +
                    "  CONSTRAINT `t_gb` FOREIGN KEY (`gateway_batch_id`) REFERENCES `gateway_batches` (`id`)\n" +
                    ") ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");

            statement.execute("CREATE TABLE `refunds` (\n" +
                    "  `position` bigint(20) NOT NULL AUTO_INCREMENT,\n" +
                    "  `id` char(15) COLLATE utf8_unicode_ci DEFAULT NULL,\n" +
                    "  `transaction_id` char(15) COLLATE utf8_unicode_ci NOT NULL,\n" +
                    "  `amount` double(9,2) NOT NULL,\n" +
                    "  `auth_user_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,\n" +
                    "  `type` varchar(16) COLLATE utf8_unicode_ci NOT NULL,\n" +
                    "  `external_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,\n" +
                    "  `status_code` char(1) COLLATE utf8_unicode_ci NOT NULL,\n" +
                    "  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,\n" +
                    "  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,\n" +
                    "  PRIMARY KEY (`position`),\n" +
                    "  UNIQUE KEY `id` (`id`),\n" +
                    "  KEY `transaction_id` (`transaction_id`),\n" +
                    "  CONSTRAINT `refund_transaction` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`)\n" +
                    ") ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");

            statement.execute("CREATE TABLE `affiliate_charges` (\n" +
                    "  `transaction_id` char(15) COLLATE utf8_unicode_ci NOT NULL,\n" +
                    "  `payee_user_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,\n" +
                    "  `amount` double(11,2) unsigned NOT NULL,\n" +
                    "  KEY `transaction_id` (`transaction_id`),\n" +
                    "  CONSTRAINT `affiliate_charge_transaction` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`)\n" +
                    ") ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");

            statement.execute("CREATE TABLE `transaction_charges` (\n" +
                    "  `id` bigint(20) NOT NULL AUTO_INCREMENT,\n" +
                    "  `user_id` varchar(255) NOT NULL,\n" +
                    "  `transaction_id` char(15) NOT NULL,\n" +
                    "  `account_id` bigint(20) DEFAULT NULL,\n" +
                    "  `amount` double(9,2) NOT NULL,\n" +
                    "  `paid` tinyint(1) NOT NULL DEFAULT 0,\n" +
                    "  `fee_id` bigint(20) DEFAULT NULL,\n" +
                    "  `payout_type_id` int(11) NOT NULL,\n" +
                    "  PRIMARY KEY (`id`),\n" +
                    "  KEY `transaction_id` (`transaction_id`),\n" +
                    "  KEY `account_id` (`account_id`),\n" +
                    "  KEY `fee_id` (`fee_id`),\n" +
                    "  KEY `payout_type_id` (`payout_type_id`),\n" +
                    "  CONSTRAINT `tc_transaction` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`),\n" +
                    "  CONSTRAINT `tc_account` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`),\n" +
                    "  CONSTRAINT `tc_fee` FOREIGN KEY (`fee_id`) REFERENCES `fees` (`id`),\n" +
                    "  CONSTRAINT `tc_type` FOREIGN KEY (`payout_type_id`) REFERENCES `transaction_payout_type` (`id`)\n" +
                    ") ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");

            statement.execute("CREATE TABLE `transaction_fees` (\n" +
                    "  `id` bigint(20) NOT NULL AUTO_INCREMENT,\n" +
                    "  `transaction_id` char(15) NOT NULL,\n" +
                    "  `gateway_reference_id` varchar(64) DEFAULT NULL,\n" +
                    "  `amount` decimal(24,2) UNSIGNED NOT NULL,\n" +
                    "  `description` varchar(255) DEFAULT NULL,\n" +
                    "  PRIMARY KEY (`id`),\n" +
                    "  KEY `transaction_id` (`transaction_id`),\n" +
                    "  CONSTRAINT `tf_transaction` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`)\n" +
                    ") ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");

            statement.execute("CREATE TABLE `transaction_debits` (\n" +
                    "  `position` bigint(20) NOT NULL AUTO_INCREMENT,\n" +
                    "  `id` char(15) DEFAULT NULL,\n" +
                    "  `user_id` varchar(255) NOT NULL,\n" +
                    "  `transaction_id` char(15) DEFAULT NULL,\n" +
                    "  `account_id` bigint(20) DEFAULT NULL,\n" +
                    "  `amount` double(9,2) unsigned NOT NULL,\n" +
                    "  `payment_file_id` bigint(20) DEFAULT NULL,\n" +
                    "  `returned` tinyint(1) NOT NULL DEFAULT '0',\n" +
                    "  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,\n" +
                    "  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,\n" +
                    "  PRIMARY KEY (`position`),\n" +
                    "  UNIQUE KEY `id` (`id`),\n" +
                    "  KEY `transaction_id` (`transaction_id`),\n" +
                    "  KEY `account_id` (`account_id`),\n" +
                    "  CONSTRAINT `td_account` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`),\n" +
                    "  CONSTRAINT `td_transaction` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`)\n" +
                    ") ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");

            statement.execute("CREATE TABLE `payout_batches` (\n" +
                    "  `position` bigint(20) NOT NULL AUTO_INCREMENT,\n" +
                    "  `id` char(15) DEFAULT NULL,\n" +
                    "  `user_id` varchar(255) DEFAULT NULL,\n" +
                    "  `account_id` bigint(20) DEFAULT NULL,\n" +
                    "  `team_id` varchar(32) DEFAULT NULL,\n" +
                    "  `amount` double(14,2) unsigned NOT NULL,\n" +
                    "  `payment_file_id` bigint(20) DEFAULT NULL,\n" +
                    "  `reference_id` varchar(128) DEFAULT NULL,\n" +
                    "  `type_id` int(11) NOT NULL,\n" +
                    "  `paid_at` timestamp NULL DEFAULT NULL,\n" +
                    "  `returned` tinyint(1) NOT NULL DEFAULT '0',\n" +
                    "  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,\n" +
                    "  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,\n" +
                    "  PRIMARY KEY (`position`),\n" +
                    "  UNIQUE KEY `id` (`id`),\n" +
                    "  KEY `account_id` (`account_id`),\n" +
                    "  KEY `team_id` (`team_id`),\n" +
                    "  KEY `payment_file_id` (`payment_file_id`),\n" +
                    "  KEY `type_id` (`type_id`),\n" +
                    "  CONSTRAINT `pb_account` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`),\n" +
                    "  CONSTRAINT `pb_team` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`),\n" +
                    "  CONSTRAINT `pb_payment_file` FOREIGN KEY (`payment_file_id`) REFERENCES `payment_files` (`id`),\n" +
                    "  CONSTRAINT `pb_type` FOREIGN KEY (`type_id`) REFERENCES `transaction_payout_type` (`id`)\n" +
                    ") ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");

            statement.execute("CREATE TABLE `payout_batch_chain` (\n" +
                    "  `payout_batch_id` char(15) NOT NULL,\n" +
                    "  `parent_payout_batch_id` char(15) NOT NULL,\n" +
                    "  `root_payout_batch_id` char(15) NOT NULL,\n" +
                    "  KEY `payout_batch` (`payout_batch_id`),\n" +
                    "  KEY `parent_payout_batch` (`parent_payout_batch_id`),\n" +
                    "  KEY `root_payout_batch` (`root_payout_batch_id`),\n" +
                    "  CONSTRAINT `pbc_parent_payout_batch` FOREIGN KEY (`parent_payout_batch_id`) REFERENCES `payout_batches` (`id`),\n" +
                    "  CONSTRAINT `pbc_payout_batch` FOREIGN KEY (`payout_batch_id`) REFERENCES `payout_batches` (`id`),\n" +
                    "  CONSTRAINT `pbc_root_payout_batch` FOREIGN KEY (`root_payout_batch_id`) REFERENCES `payout_batches` (`id`)\n" +
                    ") ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");

            statement.execute("CREATE TABLE `transaction_payouts` (\n" +
                    "  `id` bigint(20) NOT NULL AUTO_INCREMENT,\n" +
                    "  `user_id` varchar(255) NOT NULL,\n" +
                    "  `transaction_id` char(15) NOT NULL,\n" +
                    "  `account_id` bigint(20) DEFAULT NULL,\n" +
                    "  `amount` double(9,2) unsigned NOT NULL,\n" +
                    "  `fee_id` bigint(20) DEFAULT NULL,\n" +
                    "  `transaction_charge_id` bigint(20) DEFAULT NULL,\n" +
                    "  `payout_batch_id` char(15) DEFAULT NULL,\n" +
                    "  `payout_type_id` int(11) NOT NULL,\n" +
                    "  `processed` tinyint(1) NOT NULL DEFAULT 0,\n" +
                    "  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,\n" +
                    "  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,\n" +
                    "  PRIMARY KEY (`id`),\n" +
                    "  UNIQUE KEY `transaction_charge_id` (`transaction_charge_id`),\n" +
                    "  KEY `transaction_id` (`transaction_id`),\n" +
                    "  KEY `account_id` (`account_id`),\n" +
                    "  KEY `fee_id` (`fee_id`),\n" +
                    "  KEY `payout_batch_id` (`payout_batch_id`),\n" +
                    "  KEY `payout_type_id` (`payout_type_id`),\n" +
                    "  CONSTRAINT `tp_transaction` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`),\n" +
                    "  CONSTRAINT `tp_account` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`),\n" +
                    "  CONSTRAINT `tp_fee` FOREIGN KEY (`fee_id`) REFERENCES `fees` (`id`),\n" +
                    "  CONSTRAINT `tp_tc` FOREIGN KEY (`transaction_charge_id`) REFERENCES `transaction_charges` (`id`),\n" +
                    "  CONSTRAINT `tp_payout_batch` FOREIGN KEY (`payout_batch_id`) REFERENCES `payout_batches` (`id`),\n" +
                    "  CONSTRAINT `tp_type` FOREIGN KEY (`payout_type_id`) REFERENCES `transaction_payout_type` (`id`)\n" +
                    ") ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");

            statement.execute("CREATE TABLE `team_feesets` (\n" +
                    "  `team_id` varchar(32) NOT NULL,\n" +
                    "  `transaction_type` varchar(64) NOT NULL,\n" +
                    "  `description` text,\n" +
                    "  `fee_ids` text NOT NULL,\n" +
                    "  UNIQUE KEY `team_type` (`team_id`,`transaction_type`),\n" +
                    "  KEY `team_id` (`team_id`),\n" +
                    "  KEY `transaction_type` (`transaction_type`),\n" +
                    "  CONSTRAINT `team_feesets_team` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`),\n" +
                    "  CONSTRAINT `team_feesets_type` FOREIGN KEY (`transaction_type`) REFERENCES `transaction_type` (`slug`)\n" +
                    ") ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");

            statement.execute("CREATE TABLE `user_accounts` (\n" +
                    "  `user_id` varchar(255) NOT NULL DEFAULT '',\n" +
                    "  `name` varchar(255) NOT NULL,\n" +
                    "  `number` varbinary(128) NOT NULL,\n" +
                    "  `routing` char(9) NOT NULL,\n" +
                    "  `bank_name` varchar(255) DEFAULT NULL,\n" +
                    "  `type_id` int(11) NOT NULL,\n" +
                    "  `validated` tinyint(1) NOT NULL DEFAULT '0',\n" +
                    "  `hash` varchar(64) CHARACTER SET utf8 NOT NULL,\n" +
                    "  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,\n" +
                    "  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,\n" +
                    "  PRIMARY KEY (`user_id`),\n" +
                    "  KEY `type_id` (`type_id`),\n" +
                    "  CONSTRAINT `user_account_type` FOREIGN KEY (`type_id`) REFERENCES `account_type` (`id`)\n" +
                    ") ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");

            statement.execute("CREATE TABLE `user_account_validation` (\n" +
                    "  `id` bigint(20) NOT NULL AUTO_INCREMENT,\n" +
                    "  `user_id` varchar(255) NOT NULL,\n" +
                    "  `amount1` double(2,2) unsigned NOT NULL,\n" +
                    "  `amount2` double(2,2) unsigned NOT NULL,\n" +
                    "  `account_hash` varchar(64) CHARACTER SET utf8 NOT NULL,\n" +
                    "  `submitted_at` timestamp NULL DEFAULT NULL,\n" +
                    "  `payment_file_id` bigint(20) DEFAULT NULL,\n" +
                    "  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,\n" +
                    "  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,\n" +
                    "  `deleted` tinyint(1) NOT NULL DEFAULT '0',\n" +
                    "  PRIMARY KEY (`id`),\n" +
                    "  KEY `user_id` (`user_id`),\n" +
                    "  KEY `payment_file_id` (`payment_file_id`),\n" +
                    "  CONSTRAINT `uav_payment_file` FOREIGN KEY (`payment_file_id`) REFERENCES `payment_files` (`id`),\n" +
                    "  CONSTRAINT `uav_user_account` FOREIGN KEY (`user_id`) REFERENCES `user_accounts` (`user_id`)\n" +
                    ") ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");

            statement.execute("CREATE TABLE `user_tax_balance` (\n" +
                    "  `user_id` varchar(255) NOT NULL,\n" +
                    "  `balance` double(9,2) NOT NULL DEFAULT '0.00',\n" +
                    "  PRIMARY KEY (`user_id`)\n" +
                    ") ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");

            statement.execute("INSERT INTO `account_type` VALUES\n" +
                    "(1,'Checking','checking'),\n" +
                    "(2,'Savings','savings')");

            statement.execute("INSERT INTO `gateway_connection_type` VALUES\n" +
                    "(1,'USAePay','usaepay'),\n" +
                    "(2, 'Authorize.Net', 'authorizenet'),\n" +
                    "(3, 'Mock', 'mock'),\n" +
                    "(4, 'Paypal', 'paypal'),\n" +
                    "(5, 'Stripe', 'stripe'),\n" +
                    "(6, 'Square', 'square'),\n" +
                    "(7, 'BrainTree', 'braintree'),\n" +
                    "(8, 'NMI', 'nmi'),\n" +
                    "(9, 'Splash Payments', 'splashpayments'),\n" +
                    "(10, 'PayHub', 'payhub')");

            statement.execute("INSERT INTO `transaction_type` VALUES\n" +
                    "(1,'Cash Sale','cash-sale'),\n" +
                    "(2,'Check Sale','check-sale'),\n" +
                    "(3,'Credit Card Sale','credit-card-sale'),\n" +
                    "(4,'Debit Card Sale','debit-card-sale'),\n" +
                    "(6,'Check Subscription','check-sub'),\n" +
                    "(7,'Credit Card Subscription','credit-card-sub'),\n" +
                    "(8,'Debit Card Subscription','debit-card-sub'),\n" +
                    "(10,'E-Wallet Sale','e-wallet-sale'),\n" +
                    "(11,'E-Wallet Transfer','e-wallet-transfer'),\n" +
                    "(12,'E-Wallet Credit','e-wallet-credit'),\n" +
                    "(13,'E-Wallet Deposit','e-wallet-deposit'),\n" +
                    "(14,'E-Wallet Subscription','e-wallet-sub'),\n" +
                    "(15,'E-Wallet Withdraw','e-wallet-withdraw'),\n" +
                    "(20,'Team Credits Sale','team-credits-sale'),\n" +
                    "(21,'Team Credits Credit','team-credits-credit'),\n" +
                    "(22,'Team Credits Transfer','team-credits-transfer'),\n" +
                    "(30,'Company Credits Sale','company-credits-sale'),\n" +
                    "(31,'Company Credits Credit','company-credits-credit'),\n" +
                    "(32,'Company Credits Transfer','company-credits-transfer'),\n" +
                    "(40, 'PayPal Sale', 'paypal-sale'),\n" +
                    "(50, 'E-Check deposit to E-Wallet', 'e-check-deposit-e-wallet'),\n" +
                    "(51, 'ACH deposit to E-Wallet', 'ach-deposit-e-wallet'),\n" +
                    "(60, 'E-Check payment to Tax Balance', 'e-check-payment-tax'),\n" +
                    "(61, 'ACH payment to Tax Balance', 'ach-payment-tax'),\n" +
                    "(62, 'E-Wallet payment to Tax Balance', 'e-wallet-payment-tax'),\n" +
                    "(70, 'Credit Card Shipping Sale', 'credit-card-shipping'),\n" +
                    "(71, 'Debit Card Shipping Sale', 'debit-card-shipping'),\n" +
                    "(73, 'ECheck Shipping Sale', 'e-check-shipping'),\n" +
                    "(74, 'ACH Shipping Sale', 'ach-shipping'),\n" +
                    "(75, 'EWallet Shipping Sale', 'e-wallet-shipping')");

            statement.execute("INSERT INTO `transaction_payout_type` VALUES\n" +
                    "(1,'Cash Tax','cash-tax'),\n" +
                    "(2,'Cash Consignment','cash-consignment'),\n" +
                    "(3,'Cash Fee','cash-fee'),(4,'Tax','tax'),\n" +
                    "(5,'Consignment','consignment'),\n" +
                    "(6,'Merchant','merchant'),\n" +
                    "(7,'E-Wallet','e-wallet'),\n" +
                    "(8,'Team Credit','team-credit'),\n" +
                    "(9,'Company Credit','company-credit'),\n" +
                    "(10,'Fee','fee'),\n" +
                    "(11,'Merchant Credit','merchant-credit'),\n" +
                    "(12,'E-Wallet Withdraw','e-wallet-withdraw'),\n" +
                    "(13, 'Affiliate', 'affiliate'),\n" +
                    "(14, 'Affiliate E-Wallet', 'affiliate-e-wallet'),\n" +
                    "(15, 'Tax Balance', 'tax-balance')");

        }
    }
}
