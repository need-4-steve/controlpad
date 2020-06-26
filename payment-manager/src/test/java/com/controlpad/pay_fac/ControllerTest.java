package com.controlpad.pay_fac;

import com.google.gson.Gson;
import com.google.gson.reflect.TypeToken;
import org.junit.Before;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.http.MediaType;
import org.springframework.test.web.servlet.MockMvc;
import org.springframework.test.web.servlet.ResultMatcher;
import org.springframework.web.context.WebApplicationContext;

import java.nio.charset.Charset;

import static org.hamcrest.Matchers.is;
import static org.springframework.test.web.servlet.request.MockMvcRequestBuilders.*;
import static org.springframework.test.web.servlet.result.MockMvcResultMatchers.jsonPath;
import static org.springframework.test.web.servlet.result.MockMvcResultMatchers.status;
import static org.springframework.test.web.servlet.setup.MockMvcBuilders.webAppContextSetup;


public abstract class ControllerTest extends SqlSessionTest {

    private MediaType jsonContentType = new MediaType(MediaType.APPLICATION_JSON.getType(),
            MediaType.APPLICATION_JSON.getSubtype(),
            Charset.forName("utf8"));

    private MockMvc mockMvc;
    private Gson gson = new Gson();
    private String teamOneId;
    private String teamTwoId;
    private String teamFourId;

    @Autowired
    WebApplicationContext webApplicationContext;

    @Before
    public void setup() {
        this.mockMvc = webAppContextSetup(webApplicationContext).build();
        teamOneId = getTestUtil().getMockData().getTeamOne().getId();
        teamTwoId = getTestUtil().getMockData().getTeamTwo().getId();
        teamFourId = getTestUtil().getMockData().getTeamFour().getId();
    }

    protected String toJson(Object o) {
        return gson.toJson(o);
    }

    protected <T> T fromJson(String json, Class<T> classOfT) {
        return gson.fromJson(json, classOfT);
    }

    protected String getTeamOneId() {
        return teamOneId;
    }

    protected String getTeamTwoId() {
        return teamTwoId;
    }

    protected String getTeamFourId() {
        return teamFourId;
    }

    protected Gson getGson() {
        return gson;
    }

    protected MockMvc getMockMvc() {
        return mockMvc;
    }

    protected MediaType getJsonContentType() {
        return jsonContentType;
    }

    protected void performBadGetRequest(String path) {
        try {
            getMockMvc().perform(get(path)
                    .header("Authorization", "APIKey " + getTestUtil().getMockData().getTestApiKey().getId()))
                    .andExpect(status().isBadRequest());
        } catch (Exception e) {
            throw new RuntimeException(e);
        }
    }

    protected void performBadGetRequest(String path, ResultMatcher result) {
        try {
            getMockMvc().perform(get(path)
                    .header("Authorization", "APIKey " + getTestUtil().getMockData().getTestApiKey().getId()))
                    .andExpect(result);
        } catch (Exception e) {
            throw new RuntimeException(e);
        }
    }

    protected void assertPaginationValidation(String path) {
        performBadGetRequest(path + "?page=1");
        performBadGetRequest(path + "?count=25");
        performBadGetRequest(path + "?page=1&count=0");
        performBadGetRequest(path + "?page=0&count=25");
    }

    protected void performBadPostRequest(String path, Object body, ResultMatcher result) {
        try {
            getMockMvc().perform(post(path)
                    .header("Authorization", "APIKey " + getTestUtil().getMockData().getTestApiKey().getId())
                    .contentType(getJsonContentType())
                    .content(getGson().toJson(body)))
                    .andExpect(result);
        } catch (Exception e) {
            throw new RuntimeException(e);
        }
    }

    protected void performBadPostRequest(String path, Object body) {
        performBadPostRequest(path, body, status().isBadRequest());
    }

    protected void performBadPutRequest(String path, Object body) {
        performBadPutRequest(path, body, status().isBadRequest());
    }

    protected void performBadPutRequest(String path, Object body, ResultMatcher result) {
        try {
            getMockMvc().perform(put(path)
                    .header("Authorization", "APIKey " + getTestUtil().getMockData().getTestApiKey().getId())
                    .contentType(getJsonContentType())
                    .content(getGson().toJson(body)))
                    .andExpect(result);
        } catch (Exception e) {
            throw new RuntimeException(e);
        }
    }

    protected void performUnsuccessfulRequest(String path, Object body) {
        try {
            getMockMvc().perform(post(path)
                    .header("Authorization", "APIKey " + getTestUtil().getMockData().getTestApiKey().getId())
                    .contentType(getJsonContentType())
                    .content(getGson().toJson(body)))
                    .andExpect(status().isOk())
                    .andExpect(jsonPath("$['success']", is(false)));
        } catch (Exception e) {
            throw new RuntimeException(e);
        }
    }

    protected void performUnsuccessfulRequest(String path, Object body, int code) {
        try {
            getMockMvc().perform(post(path)
                    .header("Authorization", "APIKey " + getTestUtil().getMockData().getTestApiKey().getId())
                    .contentType(getJsonContentType())
                    .content(getGson().toJson(body)))
                    .andExpect(status().isOk())
                    .andExpect(jsonPath("$['success']", is(false)))
                    .andExpect(jsonPath("$['statusCode']", is(code)));
        } catch (Exception e) {
            throw new RuntimeException(e);
        }
    }

    protected <T>T performPost(String path, String authorization, Object body, TypeToken<T> responseTypeToken) {
        try {
            String response = getMockMvc().perform(post(path)
                    .header("Authorization", authorization)
                    .contentType(getJsonContentType())
                    .content(getGson().toJson(body)))
                    .andExpect(status().isOk()).andReturn().getResponse().getContentAsString();
            return getGson().fromJson(response, responseTypeToken.getType());
        } catch (Exception e) {
            throw new RuntimeException(e);
        }
    }

    protected <T>T performPost(String path, Object body, TypeToken<T> responseTypeToken) {
        return performPost(path, "APIKey " + getTestUtil().getMockData().getTestApiKey().getId(), body, responseTypeToken);
    }

    protected <T>T performPut(String path, Object body, TypeToken<T> responseTypeToken) {
        try {
            String response = getMockMvc().perform(put(path)
                    .header("Authorization", "APIKey " + getTestUtil().getMockData().getTestApiKey().getId())
                    .contentType(getJsonContentType())
                    .content(getGson().toJson(body)))
                    .andExpect(status().isOk()).andReturn().getResponse().getContentAsString();
            return getGson().fromJson(response, responseTypeToken.getType());
        } catch (Exception e) {
            throw new RuntimeException(e);
        }
    }

    protected <T>T performGet(String path, TypeToken<T> typeToken) {
        try {
            String response = getMockMvc().perform(get(path)
                    .header("APIKey", getTestUtil().getMockData().getTestApiKey().getId()))
                    .andExpect(status().isOk()).andReturn().getResponse().getContentAsString();
            return getGson().fromJson(response, typeToken.getType());
        } catch (Exception e) {
            throw new RuntimeException(e);
        }
    }

    protected void performDelete(String path) {
        try {
            getMockMvc().perform(delete(path)
                    .header("Authorization", "APIKey " + getTestUtil().getMockData().getTestApiKey().getId())
                    .contentType(getJsonContentType()))
                    .andExpect(status().isOk());
        } catch (Exception e) {
            throw new RuntimeException(e);
        }
    }

    protected String performGet(String path) {
        try {
            return getMockMvc().perform(get(path)
                    .header("Authorization", "APIKey " + getTestUtil().getMockData().getTestApiKey().getId()))
                    .andExpect(status().isOk()).andReturn().getResponse().getContentAsString();
        } catch (Exception e) {
            throw new RuntimeException(e);
        }
    }

    protected String performGet(String path, boolean success, int code) {
        try {
            return getMockMvc().perform(get(path)
                    .header("Authorization", "APIKey " + getTestUtil().getMockData().getTestApiKey().getId()))
                    .andExpect(status().isOk())
                    .andExpect(jsonPath("$['success']", is(success)))
                    .andExpect(jsonPath("$['statusCode']", is(code)))
                    .andReturn().getResponse().getContentAsString();
        } catch (Exception e) {
            throw new RuntimeException(e);
        }
    }
}