package com.controlpad.payman_common.datasource;

import javax.sql.DataSource;
import java.io.PrintWriter;
import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.SQLException;
import java.sql.SQLFeatureNotSupportedException;
import java.util.logging.Logger;

public class DBDataSource implements DataSource {

    private SqlConfig sqlConfig = null;

    public DBDataSource(SqlConfig sqlConfig) {
        this.sqlConfig = sqlConfig;
    }

    public void setSqlConfig(SqlConfig sqlConfig) {
        this.sqlConfig = sqlConfig;
    }

    public SqlConfig getSqlConfig() {
        return sqlConfig;
    }

    @Override
    public Connection getConnection() throws SQLException {
        return getConnection(sqlConfig.getUsername(), sqlConfig.getPassword());
    }

    @Override
    public Connection getConnection(String username, String password) throws SQLException {
        return DriverManager.getConnection(sqlConfig.getUrl(), username, password);
    }

    @Override
    public PrintWriter getLogWriter() throws SQLException {
        return null;
    }

    @Override
    public void setLogWriter(PrintWriter out) throws SQLException {

    }

    @Override
    public void setLoginTimeout(int seconds) throws SQLException {

    }

    @Override
    public int getLoginTimeout() throws SQLException {
        return 0;
    }

    @Override
    public Logger getParentLogger() throws SQLFeatureNotSupportedException {
        return null;
    }

    @Override
    public <T> T unwrap(Class<T> iface) throws SQLException {
        return null;
    }

    @Override
    public boolean isWrapperFor(Class<?> iface) throws SQLException {
        return false;
    }
}
