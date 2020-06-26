/*===============================================================================
* Copyright 2016(c) ControlPad
*==============================================================================*/
package com.controlpad.payman_processor;

import com.controlpad.payman_processor.datasource.SqlSessionUtil;
import com.controlpad.payman_processor.test.TestUtil;
import com.controlpad.payman_processor.util.IDUtil;
import com.google.gson.Gson;
import org.apache.ibatis.session.SqlSession;
import org.junit.After;
import org.junit.Before;
import org.junit.runner.RunWith;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.test.context.ContextConfiguration;
import org.springframework.test.context.junit4.SpringJUnit4ClassRunner;
import org.springframework.test.context.web.WebAppConfiguration;

@RunWith(SpringJUnit4ClassRunner.class)
@ContextConfiguration(locations = {"file:src/main/webapp/WEB-INF/applicationContext.xml", "file:src/main/webapp/WEB-INF/mvc-dispatcher-servlet.xml"})
@WebAppConfiguration
public abstract class CronTest {

    @Autowired
    SqlSessionUtil sqlSessionUtil;
    @Autowired
    TestUtil testUtil;
    @Autowired
    IDUtil idUtil;

    private Gson gson = new Gson();
    private SqlSession clientSqlSession;

    @Before
    public void setup() throws Exception {
        getNewClientSqlSession(false);
    }

    @After
    public void cleanup() {
        if (clientSqlSession != null) {
            clientSqlSession.close();
        }
    }

    protected SqlSession getNewClientSqlSession(boolean autoCommit) {
        if (clientSqlSession != null) {
            clientSqlSession.close();
        }
        clientSqlSession = sqlSessionUtil.openSession(testUtil.getMockData().getTestClient().getId(), autoCommit);
        return clientSqlSession;
    }

    public Gson getGson() {
        return gson;
    }

    protected TestUtil getTestUtil() {
        return testUtil;
    }

    public IDUtil getIdUtil() {
        return idUtil;
    }

    public SqlSession getClientSqlSession() {
        return clientSqlSession;
    }
}
