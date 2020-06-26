package com.controlpad.pay_fac.tokenization;

import com.controlpad.pay_fac.ControllerTest;
import com.controlpad.pay_fac.payment_info.TokenRequest;
import com.controlpad.payman_common.address.Address;
import com.controlpad.payman_common.transaction.Card;
import com.controlpad.payman_common.util.GsonUtil;
import org.junit.Test;

import java.util.ArrayList;
import java.util.LinkedList;
import java.util.List;

import static org.springframework.test.web.servlet.request.MockMvcRequestBuilders.post;
import static org.springframework.test.web.servlet.result.MockMvcResultMatchers.status;

public class SaveCardTest extends ControllerTest {

    @Test
    public void gatewaySaveCardTest(){
        // TODO refactor to be an api validation test after validation of card numbers is added, no need for loops either
        String path = "/tokenization/card";

        String[][] cardNumberSet = {{"4929847549855561", "4000100211112223", "4000100", "400010021111222234214312", "400010021a112222"},
                                    {"0", "1", "1", "1", "1"}};

        List<int[]> expirations = new ArrayList<>();
        expirations.add(new int[] {10, 2023, 0});
        expirations.add(new int[] {9, 2023, 0});
        expirations.add(new int[] {0, 2023, 1});
        expirations.add(new int[] {123, 2045, 1});
        expirations.add(new int[] {13, 2022, 1});
        expirations.add(new int[] {12, 2010, 1});

        for(int i=0; i<cardNumberSet[0].length; ++i){
            TokenRequest tokenRequestData = new TokenRequest(new Card(cardNumberSet[0][i], 10, 2019, "999"), new Address("123 Main St", "47802"));
            if("0".equals(cardNumberSet[1][i])){
                TokenizeCardResponse response = performPostRequest(path, tokenRequestData);
                assert response.getSuccess();
            }else{
                performBadPostRequest(path, tokenRequestData);
            }
        }

        for(int i=0; i < expirations.size(); ++i){
            TokenRequest tokenRequestData = new TokenRequest(new Card("4000100211112222", expirations.get(i)[0], expirations.get(i)[1], "999"), new Address("123 Main St", "47802"));
            if(expirations.get(i)[2] == 0){
                TokenizeCardResponse response = performPostRequest(path, tokenRequestData);
                assert response.getSuccess();
            }else{
                System.out.println("Date check: " + GsonUtil.getGson().toJson(tokenRequestData));
                performBadPostRequest(path, tokenRequestData);
            }
        }

        Address address = new Address().setLine1("123 Main St").setCity("Salt Lake City").setState("UT").setCountryCode("US").setPostalCode("84123");

        List<Card> invalidCards = new LinkedList<>();
        invalidCards.add(new Card().setNumber("4000100211112222").setMonth(13).setYear(2020).setCode("999"));
        invalidCards.add(new Card().setNumber("4000100211112223").setMonth(10).setYear(2020).setCode("999"));
        invalidCards.add( new Card().setNumber("4000100211112222").setMonth(10).setYear(2010).setCode("999"));
        invalidCards.add( new Card().setNumber("4032").setMonth(10).setYear(20).setCode("999"));
        invalidCards.add( new Card().setNumber("4000100211112222123125").setMonth(10).setYear(2020).setCode("999"));
        for(Card card : invalidCards){
            TokenRequest request = new TokenRequest(card, address);
            performBadPostRequest(path, request);
        }

        Card validCard = new Card().setNumber("4000100211112222").setMonth(12).setYear(2020).setCode("999");
        System.out.println("Valid Card: " + GsonUtil.getGson().toJson(validCard));
        TokenizeCardResponse response = performPostRequest(path, new TokenRequest(validCard, address));
        assert response.getSuccess();
    }

    private TokenizeCardResponse performPostRequest(String path, TokenRequest body) {
        try {
            return getGson().fromJson(getMockMvc().perform(post(path)
                    .header("Authorization", "APIKey " + getTestUtil().getMockData().getTestApiKey().getId())
                    .contentType(getJsonContentType())
                    .content(getGson().toJson(body)))
                    .andExpect(status().isOk())
                    .andReturn().getResponse().getContentAsString(), TokenizeCardResponse.class);
        } catch (Exception e) {
            throw new RuntimeException(e);
        }
    }
}