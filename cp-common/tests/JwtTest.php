<?php namespace CPCommon\Test;

require '../vendor/autoload.php';
require '../jwt/Jwt.php';

use CPCommon\Jwt\Jwt;

$verbose = in_array('-v', $argv);

$secret = 'abc123';

$claims = array(
    'exp' => time() + 3600,
    'iat' => time(),
    'iss' => 'TEST_ISSUER',
    'aud' => 'TEST_AUDIENCE',
    'sub' => '123',
    'name' => 'Ghandi',
    'role' => 'Guru'
);

$expiredClaims = array(
    'exp' => time() - 3600,
    'iat' => time(),
    'iss' => 'TEST_ISSUER',
    'aud' => 'TEST_AUDIENCE',
    'sub' => '123',
    'name' => 'Ghandi',
    'role' => 'Guru'
);

$cases = array(
    array(
        'label' => 'Case #1: Valid token',
        'valid' => true,
        'token' => Jwt::sign($claims, $secret),
        'claims' => $claims,
        'verifyClaims' => null
    ),
    array(
        'label' => 'Case #2: Invalid token',
        'valid' => false,
        'token' => '1234567890',
        'claims' => null,
        'verifyClaims' => null
    ),
    array(
        'label' => 'Case #3: Valid token, valid claims',
        'valid' => true,
        'token' => Jwt::sign($claims, $secret),
        'claims' => $claims,
        'verifyClaims' => array('iss' => 'TEST_ISSUER')
    ),
    array(
        'label' => 'Case #4: Valid token, invalid claims',
        'valid' => false,
        'token' => Jwt::sign($claims, $secret),
        'claims' => $claims,
        'verifyClaims' => array('iss' => 'FAKE_ISSUER')
    ),
    array(
        'label' => 'Case #5: Expired token',
        'valid' => false,
        'token' => Jwt::sign($expiredClaims, $secret),
        'claims' => $expiredClaims,
        'verifyClaims' => null
    ),
    array(
        'label' => 'Case #6: Null token',
        'valid' => false,
        'token' => null,
        'claims' => null,
        'verifyClaims' => null
    )
);

$pass = "\033[32mPASS\033[0m";
$fail = "\033[31mFAIL\033[0m";

$passCount = 0;
$failCount = 0;
$totalCount = 0;

$state = null;
try {
    $token = Jwt::sign(null, $secret);
    echo "\n  Oh, we found a token: ";
    var_dump($token);
    $failCount++;
    $state = $fail;
} catch (\Exception $e) {
    $passCount++;
    $state = $pass;
}
$totalCount++;
echo "\n\n  Case #0: Create token from empty payload (invalid)";
echo "\n    " . $state . ': Test #1: Jwt::sign(null) => throws exception';

foreach ($cases as $case) {
    echo "\n\n  " . $case['label'];
    $result = testVerify($case['token'], $case['claims'], $case['verifyClaims']);

    $state = $case['valid'] === $result['valid'] ? $pass : $fail;

    $reason = ($result['valid'] != $case['valid'] ? "\n        " . $result['exception'] : '');

    $res = $verbose ? formatClaims($result1['claims']) : "{...}";

    $params = $verbose ? "\033[36m" . $case['token'] . "\033[0m" : "...";

    echo "\n    " . $state . ': Test #1: Jwt::verify('.$params.') => ' . $res . $reason;

    $totalCount++;
    if ($state) {
        $passCount++;
    } else {
        $failCount++;
    }
}

echo "\n\n  Stats:\n  ==========";
echo "\n  Passed: " . str_pad($passCount, 2, ' ', STR_PAD_LEFT);
echo "\n  Failed: " . str_pad($failCount, 2, ' ', STR_PAD_LEFT);
echo "\n   Total: " . str_pad($totalCount, 2, ' ', STR_PAD_LEFT);
echo "\n\n";

function formatClaims($claims)
{
    if ($claims == null) {
        return '()';
    }
    $arr = array();
    foreach ($claims as $key => $value) {
        array_push($arr, "$key: $value");
    }
    return "(\n            " . join($arr, ",\n            ") . "\n          )";
}

function areClaimsEqual($claims1, $claims2)
{
    if ($claims1 == null && $claims2 == null) {
        return true;
    }
    if ($claims1 == null || $claims2 == null) {
        return false;
    }
    $aClaims = count($claims1) > count($claims2) ? $claims1 : $claims2;
    $bClaims = count($claims1) > count($claims2) ? $claims2 : $claims1;
    foreach ($aClaims as $key => $value) {
        if ($key == 'jti' || $key == 'iat') {
            continue;
        }
        if ($value != $bClaims[$key]) {
            return false;
        }
    }
    return true;
}

function testVerify($token, $claims, $claimsToVerify)
{
    global $secret;
    try {
        $result = Jwt::verify($token, $secret, $claimsToVerify);
        return array(
            'valid' => areClaimsEqual($result, $claims),
            'exception' => null,
            'claims' => $result
        );
    } catch (\Exception $e) {
        return array(
            'valid' => false,
            'exception' => get_class($e),
            'claims' => null
        );
    }
}
