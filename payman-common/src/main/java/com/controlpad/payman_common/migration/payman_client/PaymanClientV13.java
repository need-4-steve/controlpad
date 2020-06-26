package com.controlpad.payman_common.migration.payman_client;

import com.controlpad.payman_common.migration.Migration;
import com.controlpad.payman_common.team.PayoutScheme;
import com.controlpad.payman_common.team.Team;
import com.controlpad.payman_common.team.TeamConfig;
import com.controlpad.payman_common.util.GsonUtil;

import java.sql.Connection;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.sql.Statement;
import java.util.ArrayList;
import java.util.List;

public class PaymanClientV13 implements Migration {

    @Override
    public void migrate(Connection connection) throws SQLException {
        try (Statement statement = connection.createStatement()) {

            // Update team configs to remove e_wallet override and fill in missing settings
            List<Team> teams = new ArrayList<>();
            ResultSet teamResults = statement.executeQuery("SELECT * FROM teams");
            while(teamResults.next()) {
                TeamConfig teamConfig = GsonUtil.getGson().fromJson(teamResults.getString("config"), TeamConfig.class);
                if (teamConfig == null) {
                    teamConfig = new TeamConfig(false, false, false,
                            false, false, TeamConfig.EWALLET_LIMIT_DEFAULT, PayoutScheme.NONE.getSlug());
                }
                teams.add(new Team(teamResults.getString("id"), teamResults.getString("name"), teamConfig));
            }
            teamResults.close();

            for (Team team : teams) {
                    if (team.getConfig().getAutoMerchantPayout() == null) {
                        team.getConfig().setAutoMerchantPayout(false);
                    }
                    if (team.getConfig().getMerchantPayouts() == null) {
                        team.getConfig().setMerchantPayouts(false);
                    }
                    if (team.getConfig().getCollectSalesTax() == null) {
                        team.getConfig().setCollectSalesTax(false);
                    }
                    if (team.getConfig().getAutoDeductOwedTax() == null) {
                        team.getConfig().setAutoDeductOwedTax(false);
                    }
                    if (team.getConfig().geteWalletLimit() == null) {
                        team.getConfig().seteWalletLimit(TeamConfig.EWALLET_LIMIT_DEFAULT);
                    }
                    if (team.getConfig().getPayoutScheme() == null) {
                        team.getConfig().setPayoutScheme(PayoutScheme.NONE.getSlug());
                    }
                    if (team.getConfig().getUserGatewayConnections() == null) {
                        team.getConfig().setUserGatewayConnections(false);
                    }
                    statement.execute(String.format("UPDATE teams SET config = '%s' WHERE id = '%s'",
                            GsonUtil.getGson().toJson(team.getConfig()),
                            team.getId()
                    ));
            }

            statement.execute("INSERT INTO migrations(version, db_type) VALUES(13, 'payman_client')");
        }
    }
}