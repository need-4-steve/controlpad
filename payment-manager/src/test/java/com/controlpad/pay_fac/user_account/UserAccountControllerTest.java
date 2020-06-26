package com.controlpad.pay_fac.user_account;

import com.controlpad.pay_fac.ControllerTest;
import com.controlpad.pay_fac.common.PaginatedResponse;
import com.controlpad.payman_common.account.Account;
import com.controlpad.payman_common.user_account.UserAccount;
import com.controlpad.payman_common.user_account.UserAccountMapper;
import com.controlpad.payman_common.user_account.UserAccountValidation;
import com.google.gson.reflect.TypeToken;
import org.junit.Test;
import org.springframework.beans.factory.annotation.Autowired;

import java.lang.annotation.Annotation;
import java.lang.reflect.Field;
import java.util.ArrayList;
import java.util.List;

import static org.hamcrest.Matchers.is;
import static org.springframework.test.web.servlet.request.MockMvcRequestBuilders.get;
import static org.springframework.test.web.servlet.request.MockMvcRequestBuilders.put;
import static org.springframework.test.web.servlet.result.MockMvcResultMatchers.jsonPath;
import static org.springframework.test.web.servlet.result.MockMvcResultMatchers.status;

public class UserAccountControllerTest extends ControllerTest {

    @Autowired
    UserAccountUtil userAccountUtil;

    @Test
    public void putUserAccountTest() throws Exception {
        String userId = "3";
        String path = "/user-accounts/" + userId;
        String accountName = "Account Holder Name";
        String routing = "324377516";
        String number = "123456789";
        String type = "checking";
        String bankName = "Chase Bank";
        UserAccount userAccount = new UserAccount(accountName, routing, number, type, bankName, false);

        //put user account works
        getMockMvc().perform(put(path)
                .header("Authorization", "APIKey " + getTestUtil().getMockData().getTestApiKey().getId())
                .contentType(getJsonContentType())
                .content(getGson().toJson(userAccount)))
                .andExpect(status().isOk());

        UserAccountMapper userAccountMapper = getNewSqlSession(true).getMapper(UserAccountMapper.class);
        UserAccount savedAccount = userAccountMapper.findAccountForUserId(userId);
        assert savedAccount != null && userAccount.equals(userAccount);

        //name required
        System.out.println("Account Annotation:");
        for(Field field : Account.class.getDeclaredFields()){
            String name = field.getName();
            Annotation[] annotations = field.getDeclaredAnnotations();
            for(Annotation annotation : annotations){
                System.out.println(name + ": " + annotation.annotationType().getName());
            }
        }
        userAccount = new UserAccount(null, routing, number, type, bankName, false);
        performBadPutRequest(path, userAccount);

        //routing required
        userAccount = new UserAccount(accountName, null, number, type, bankName, false);
        performBadPutRequest(path, userAccount);

        //number required
        userAccount = new UserAccount(accountName, routing, null, type, bankName, false);
        performBadPutRequest(path, userAccount);

        //type required
        userAccount = new UserAccount(accountName, routing, number, null, bankName, false);
        performBadPutRequest(path, userAccount);

        //type must be known
        userAccount = new UserAccount(accountName, routing, number, "unknown", bankName, false);
        performBadPutRequest(path, userAccount);

    }

    @Test
    public void getUserAccountTest() throws Exception {
        String userId = "57";

        UserAccountMapper userAccountMapper = getNewSqlSession(true).getMapper(UserAccountMapper.class);
        UserAccount insertAccount = new UserAccount("User 57 Name", "324377516", "954687351", "savings", "Bank of Ameriaca", false);
        insertAccount.setUserId(userId);
        userAccountMapper.insert(insertAccount);

        UserAccount responseAccount = getGson().fromJson(getMockMvc().perform(get("/user-accounts/" + userId)
                .header("Authorization", "APIKey " + getTestUtil().getMockData().getTestApiKey().getId())
        ).andExpect(status().isOk()).andReturn().getResponse().getContentAsString(), UserAccount.class);

        assert responseAccount != null && responseAccount.equals(insertAccount);
    }

    @Test
    public void getAllUserAccounts() throws Exception {
        int totalAccount = 20;
        int defaultId = 1000;
        List<UserAccount> list = new ArrayList<>();
        UserAccountMapper userAccountMapper = getNewSqlSession(true).getMapper(UserAccountMapper.class);

        for(int i=0; i<totalAccount; i++){
            UserAccount insertAccount = new UserAccount("User " + i + " Name", "1111111" + i, i + "1111111", "savings", "Bank of Ameriaca", false);
            insertAccount.setUserId(defaultId + i + "");
            userAccountMapper.insert(insertAccount);
            list.add(insertAccount);
        }

        for(int page = 1; page < 4; page++) {
            for (int count = 1; count < 5; count++) {
                String response = getMockMvc().perform(get("/user-accounts?page=" + page + "&count=" + count)
                        .header("Authorization", "APIKey " + getTestUtil().getMockData().getTestApiKey().getId())
                ).andExpect(status().isOk()).andReturn().getResponse().getContentAsString();
                PaginatedResponse<UserAccount> res = getGson().fromJson(response, new TypeToken<PaginatedResponse<UserAccount>>(){}.getType());

                assert res != null && res.getData().size() == count;
            }
        }
    }

    @Test
    public void testUserAccountValidation() throws Exception {
        String userId = "44";

        getNewSqlSession(true);
        UserAccountMapper userAccountMapper = getSqlSession().getMapper(UserAccountMapper.class);

        // Insert a user account
        UserAccount insertAccount = new UserAccount("User 44 Name", "324377516", "321654789", "checking", "US Bank", false);
        insertAccount.setUserId(userId);
        userAccountUtil.putUserAccount(getSqlSession(), insertAccount, getTestUtil().getMockData().getTestClient().getId());

        // Create fake validation to check against
        UserAccountValidation userAccountValidation = UserAccountValidation.generateNew(insertAccount);
        userAccountMapper.insertDevAccountValidation(userAccountValidation);
        userAccountMapper.markValidationSubmittedForId(userAccountValidation.getId());

        String requestPath = String.format("/user-accounts/validate?userId=%s&amount1=%s&amount2=%s",
                userId, userAccountValidation.getAmount1(), userAccountValidation.getAmount2());

        getMockMvc().perform(get(requestPath)
                .header("Authorization", "APIKey " + getTestUtil().getMockData().getTestApiKey().getId()))
                .andExpect(status().isOk())
                .andExpect(jsonPath("$['success']", is(true)))
                .andExpect(jsonPath("$['statusCode']", is(1)));

        assert userAccountMapper.isAccountValidatedForUserId(userId);
    }

}