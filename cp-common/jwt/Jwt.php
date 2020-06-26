<?php namespace CPCommon\Jwt;

/**
 * Static class that allows you to sign and verify JWTs.
 *
 * This simple library does NOT allow you to customize the JWT header. The algorithm is HS256 and cannot be changed. This is for security purposes as well as to simplify the implementation.
 *
 */
class Jwt
{
    // { "alg": "HS256", "typ": "JWT" }
    private static $header = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9';

    /**
     * Accepts a list of claims and a secret key and returns a signed JWT.
     *
     * Note: if the 'exp' claim is set to -1, the token will be immortal; if the 'exp' token is not set or set to null, it will be reset to five minutes into the future
     *
     * @param array[string]any $claims An associative array of claims to be used as the payload in the JWT.
     * @param string $secret A secret used to sign the JWT
     *
     * @throws Exception Throws an exception if $claims is null or empty
     *
     * @return string $token A signed JWT in compact serialization format
     */
    public static function sign($claims, $secret)
    {
        if (empty($claims)) {
            throw new \Exception('Invalid claims. Object cannot be null or empty.');
        }
        if (!isset($claims['exp'])) {
            $claims['exp'] = time() + 300;
        }
        $payload = self::encode(json_encode($claims));
        $combo = self::$header . '.' . $payload;
        $signature = self::getSignature($combo, $secret);
        return $combo . '.' . $signature;
    }

    /**
     * Parses and verifies a JWT
     *
     * This function will parse the JWT, verify it's format, signature, expiration claim, and optionally verify additional claims
     *
     * Note: if the 'exp' claim is set to -1, the token will be immortal; if the 'exp' token is not set or set to null, it will be reset to five minutes into the future
     *
     * @param string $token A signed JWT
     * @param string $secret The secret used to sign the token
     * @param array[string]any An associative array of additional claims to verify (such as 'iss' and 'aud')
     *
     * @throws Exception Throws an exception if the token is invalid in any way
     *
     * @return array[string]any An associative array containing the claims in the token
     */
    public static function verify($token, $secret, $claimsToVerify = null)
    {
        $parts = explode('.', $token);
        if (count($parts) != 3) {
            throw new \Exception('Malformed token');
        }
        $header = $parts[0];
        $payload = $parts[1];
        $signature = $parts[2];
        $verifiedSignature = self::getSignature($header.'.'.$payload, $secret);
        if ($signature !== $verifiedSignature) {
            throw new \Exception('Invalid token');
        }
        $claims = json_decode(self::decode($payload), true);
        if (!isset($claims['exp']) || ($claims['exp'] != -1 && $claims['exp'] < time())) {
            throw new \Exception('Expired token');
        }
        if (!empty($claimsToVerify)) {
            foreach ($claimsToVerify as $key => $value) {
                if ($value != $claims[$key]) {
                    throw new \Exception("Invalid token claim '$key'. Expected '$value', actual '$claims[$key]'.");
                }
            }
        }
        return $claims;
    }

    private static function getSignature($headerAndPayload, $secret)
    {
        return self::encode(hash_hmac('sha256', $headerAndPayload, $secret, true));
    }

    private static function encode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function decode($data)
    {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }
}
