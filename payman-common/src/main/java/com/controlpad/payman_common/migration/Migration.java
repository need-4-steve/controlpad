package com.controlpad.payman_common.migration;


import java.sql.Connection;
import java.sql.SQLException;

public interface Migration {

    void migrate(Connection connection) throws SQLException;

}
