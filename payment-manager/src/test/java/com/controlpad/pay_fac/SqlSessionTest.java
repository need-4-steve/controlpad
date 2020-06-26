/*===============================================================================
* Copyright 2016(c) ControlPad
*==============================================================================*/
package com.controlpad.pay_fac;

import com.controlpad.pay_fac.auth.AuthUtil;
import com.controlpad.pay_fac.datasource.SqlSessionUtil;
import com.controlpad.pay_fac.test.MockData;
import com.controlpad.pay_fac.test.TestUtil;
import com.controlpad.pay_fac.util.IDUtil;
import org.apache.ibatis.session.LocalCacheScope;
import org.apache.ibatis.session.SqlSession;
import org.junit.After;
import org.junit.Before;
import org.junit.runner.RunWith;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.test.context.ActiveProfiles;
import org.springframework.test.context.ContextConfiguration;
import org.springframework.test.context.junit4.SpringJUnit4ClassRunner;
import org.springframework.test.context.web.WebAppConfiguration;

@WebAppConfiguration
@RunWith(SpringJUnit4ClassRunner.class)
@ContextConfiguration(classes = {TestUtil.class, AppConfig.class})
@ActiveProfiles("test")
public abstract class SqlSessionTest {

    @Autowired
    SqlSessionUtil sqlSessionUtil;
    @Autowired
    TestUtil testUtil;
    @Autowired
    AuthUtil authUtil;

    private SqlSession sqlSession;

    @Before
    public void init() {
        getNewSqlSession(isAutoCommit());
    }

    @After
    public void cleanup() {
        sqlSession.close();
    }

    public boolean isAutoCommit() {
        return false;
    }

    protected TestUtil getTestUtil() {
        return testUtil;
    }

    protected MockData getMockData() {
        return testUtil.getMockData();
    }

    protected SqlSession getSqlSession() {
        return sqlSession;
    }

    public IDUtil getIdUtil() {
        return testUtil.getIdUtil();
    }

    protected SqlSession getNewSqlSession(boolean autoCommit) {
        if (sqlSession != null) {
            sqlSession.close();
        }
        sqlSession = sqlSessionUtil.openSession(testUtil.getMockData().getTestApiKey().getClientId(), autoCommit);
        sqlSession.getConfiguration().setCacheEnabled(false);
        sqlSession.getConfiguration().setLocalCacheScope(LocalCacheScope.STATEMENT);
        return sqlSession;
    }

    public AuthUtil getAuthUtil() {
        return authUtil;
    }

    protected SqlSessionUtil getSqlSessionUtil() {
        return sqlSessionUtil;
    }
}