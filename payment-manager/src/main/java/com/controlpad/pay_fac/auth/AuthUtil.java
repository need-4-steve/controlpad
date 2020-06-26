package com.controlpad.pay_fac.auth;

import com.controlpad.pay_fac.api_key.APIKey;
import com.controlpad.pay_fac.api_key.APIKeyConfig;
import org.apache.ibatis.session.SqlSession;
import org.springframework.security.crypto.bcrypt.BCryptPasswordEncoder;
import org.springframework.stereotype.Component;

import javax.crypto.Cipher;
import javax.crypto.spec.SecretKeySpec;
import java.nio.charset.StandardCharsets;
import java.security.SecureRandom;
import java.util.Base64;

@Component
public class AuthUtil {

    private static final long SESSION_DURATION = 900000L;
    private static final double MILLIS_PER_SECONDS = 1000D;
    private static final double[] BASE_62_INDEX_MULTIPLIER = {916132831D, 14776335D, 238328D, 3844D, 62D, 1D};
    private static final char[] BASE_62_SET = {'0', '1', '2', '3', '4', '5', '6', '7', '8', '9',
            'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
            'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z'};

    private SecureRandom random = new SecureRandom();

    public void refreshSession(String sessionKey, SqlSession apiSqlSession) {
        apiSqlSession.getMapper(AuthMapper.class).updateSessionKeyExpiration(sessionKey, System.currentTimeMillis() + SESSION_DURATION);
    }

    void refreshSession(Session session, AuthMapper authMapper) {
        session.setExpiresAt(System.currentTimeMillis() + SESSION_DURATION);
        authMapper.updateSessionExpiration(session);
    }

    public Session generateNewSession(String userId, String clientId) throws Exception {
        return new Session(
                generateNewSessionKey(userId),
                userId,
                clientId,
                System.currentTimeMillis() + SESSION_DURATION
        );
    }

    public APIKey buildNewApiKey(String clientId, APIKeyConfig apiKeyConfig) throws Exception {
        return new APIKey(generateRandomApiKey(clientId), clientId, apiKeyConfig);
    }

    public String encodePassword(String password) {
        return new BCryptPasswordEncoder(7, random).encode(password);
    }

    public boolean verifyPassword(String password, String encodedPassword) {
        if (password == null || encodedPassword == null)
            return false;
        return new BCryptPasswordEncoder(7).matches(password, encodedPassword);
    }

    public String generateNewId(String encKey) throws Exception {
        return generateRandomKey(10, encKey).replace("+", "").replace("/", "").substring(0, 8);
    }

    private String generateNewSessionKey(String userId) throws Exception {
        return getBase62UnixTime() + generateRandomKey(68, userId).replace("+", "Y").replace("/", "B"); //Replace unfriendly characters
    }

    public String generateRandomApiKey(String clientId) throws Exception {
        return getBase62UnixTime() + generateRandomKey(112, clientId).substring(0, 122).replace("+", "X").replace("/", "S"); //Replace unfriendly characters
    }

    private String generateRandomKey(int byteLength, String key) throws Exception {
        byte[] bytes = new byte[byteLength];
        random.nextBytes(bytes);

        // Encrypt to obscure key generation for security
        byte[] keyData = key.getBytes(StandardCharsets.US_ASCII);
        SecretKeySpec KS = new SecretKeySpec(keyData, 0, 8, "Blowfish");
        Cipher cipher = Cipher.getInstance("Blowfish");
        cipher.init(Cipher.ENCRYPT_MODE, KS);
        byte[] hash = cipher.doFinal(bytes);
        return Base64.getEncoder().encodeToString(hash).replace("=", "");
    }

    private String getBase62UnixTime() {
        String dayString = "";
        double dayMillis = System.currentTimeMillis() / MILLIS_PER_SECONDS;
        int setValue;
        for (double value : BASE_62_INDEX_MULTIPLIER) {
            setValue = (int) Math.floor(dayMillis / value);
            dayString += BASE_62_SET[setValue];
            dayMillis = dayMillis % value;
        }
        return dayString;
    }
}