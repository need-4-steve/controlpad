package com.controlpad.payman_common.migration;


import com.controlpad.payman_common.datasource.SqlConfig;
import com.google.common.base.CaseFormat;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.ResultSet;
import java.sql.Statement;
import java.util.Locale;
import java.util.regex.Pattern;

public class MigrationUtil {

    private static final Logger logger = LoggerFactory.getLogger(MigrationUtil.class);

    // Groups: 1. file path  2. type  3. version
    private static String MIGRATION_FILE_PATTERN = "(.*(payman.*)_v(\\d+)\\.sql)$";

    private Pattern pattern;

    public MigrationUtil() {
        this.pattern = Pattern.compile(MIGRATION_FILE_PATTERN);
    }

    public boolean migrate(SqlConfig sqlConfig) {
        Long maxVersion = 0L;
        DatabaseType dbType = DatabaseType.findForSlug(sqlConfig.getDbType());
        if (dbType != null) {
            maxVersion = dbType.getMaxVersion();
        }

        ResultSet resultSet = null;
        try (Connection connection = DriverManager.getConnection(sqlConfig.getUrl(), sqlConfig.getUsername(), sqlConfig.getPassword())){
            connection.setAutoCommit(false);

            try(Statement statement = connection.createStatement()) {
                Long currentVersion = 1L;
                resultSet = statement.executeQuery("SHOW TABLES LIKE 'migrations'");
                boolean migrationsTableExists = resultSet.first();
                resultSet.close();

                if (migrationsTableExists) {
                    resultSet = statement.executeQuery("SELECT MAX(version) AS maxVersion FROM migrations");
                    if (resultSet.first()) {
                        currentVersion = resultSet.getLong("maxVersion");
                    }
                    resultSet.close();
                } else {
                    switch (sqlConfig.getDbType()) {
                        case "payman":
                            resultSet = statement.executeQuery("SHOW TABLES LIKE 'clients'");
                            break;
                        case "payman_client":
                            resultSet = statement.executeQuery("SHOW TABLES LIKE 'transactions'");
                            break;
                        default:
                            resultSet = null;
                    }
                    if (resultSet == null || !resultSet.first()) {
                        currentVersion = 0L;
                    }
                    if (resultSet != null) {
                        resultSet.close();
                    }
                }

                Migration migration;
                // No auto commit so that versions will only update in full
                for (long updateVersion = currentVersion + 1; updateVersion <= maxVersion; updateVersion++) {
                    try {
                        String className = String.format("com.controlpad.payman_common.migration.%s.%sV%d",
                                sqlConfig.getDbType(),
                                CaseFormat.LOWER_UNDERSCORE.to(CaseFormat.UPPER_CAMEL, sqlConfig.getDbType()),
                                updateVersion);
                        migration = (Migration) Class.forName(className).newInstance();
                    } catch (Exception e) {
                        // Unexpected error
                        logger.error(
                                String.format(
                                        "Unexpected error pulling Migration: %s(%d)",
                                        sqlConfig.getDbType(),
                                        updateVersion),
                                e);
                        return false;
                    }

                    migration.migrate(connection);
                    connection.commit();
                }
                return true;
            } catch (Exception e) {
                connection.rollback();
                if (resultSet != null) {
                    resultSet.close();
                }
                throw e;
            }
        } catch (Exception e) {
            e.printStackTrace();
            logger.error(String.format(Locale.US, "Failed to migrate: %s", sqlConfig.getUrl()), e);
        }
        return false;
    }

}