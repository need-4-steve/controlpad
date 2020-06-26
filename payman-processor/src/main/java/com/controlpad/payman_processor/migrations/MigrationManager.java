package com.controlpad.payman_processor.migrations;

import com.controlpad.payman_common.client.ControlPadClient;
import com.controlpad.payman_common.migration.MigrationUtil;
import com.controlpad.payman_processor.client.ClientConfigUtil;
import com.controlpad.payman_processor.datasource.SqlSessionUtil;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Component;

import javax.annotation.PostConstruct;

@Component
public class MigrationManager {

    private static final Logger logger = LoggerFactory.getLogger(MigrationManager.class);

    @Autowired
    ClientConfigUtil clientConfigUtil;
    @Autowired
    SqlSessionUtil sqlSessionUtil;

    private MigrationUtil migrationUtil;

    @PostConstruct
    private void runMigrations() {
        System.out.println("Running migrations");
        migrationUtil = new MigrationUtil();
        migratePayman();
        migrateClients();
    }

    private void migratePayman() {
        if(!migrationUtil.migrate(sqlSessionUtil.getPaymanDatasource().getSqlConfig())) {
            logger.error("Failed to migrate payman");
        }
    }

    private void migrateClients() {
        // Update to make sure migrations have taken effect
        clientConfigUtil.updateClientList();
        for(ControlPadClient client : clientConfigUtil.getClientMap().values()) {
            if (!migrationUtil.migrate(client.getSqlConfigWrite())) {
                logger.error("Failed to migrate client: " + client.getId());
                // Exit so only one client is messed up
                return;
            }
        }
    }
}
