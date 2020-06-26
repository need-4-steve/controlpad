<?php
namespace CPCommon\Test;

require '../vendor/autoload.php';
require '../pid/Pid.php';

use Ramsey\Uuid\Uuid;
use CPCommon\Pid\Pid;

const ITERATIONS = 5;
$testCases = [
    'ffffffff-ffff-ffff-ffff-ffffffffffff',
    '00000000-0000-0000-0000-000000000000'
];

for ($i = 0; $i < ITERATIONS; $i++) {
    array_unshift($testCases, Uuid::uuid4()->toString());
}

$pass = "\033[32mPASS\033[0m";
$fail = "\033[31mFAIL\033[0m";

$pass_count = 0;
$fail_count = 0;
$loopIndex = 1;
foreach ($testCases as $case) {
    $pid = Pid::create($case);
    $uuid = Pid::toUuid($pid);
    $pidLen = strlen($pid);
    $uuidLen = strlen($uuid);
    $result = 'PASS';
    if (!$case === $uuid) {
        $result = "FAIL - toUuid(create(uuid)) did not yield same uuid.";
    }
    if ($pidLen !== 25) {
        $result = "FAIL - create() did not generate pid with right length. Expected '25', found '".$pidLen."'.";
    }
    if ($uuidLen !== 36) {
        $result = "FAIL - toUuid(...) did not yield uuid of correct length. Expected '36', found '".$uuidLen."'.";
    }
    $state = $result == 'PASS' ? $pass : $fail;
    echo "\n  " . $state . " Test #$loopIndex: {$uuid} > {$pid} > {$uuid}";
    if ($result === 'PASS') {
        $pass_count++;
    } else {
        $fail_count++;
    }
    $loopIndex++;
}
echo "\n\n  Stats:\n  =========";
echo "\n  Passed: " . $pass_count;
echo "\n  Failed: " . $fail_count;
echo "\n   Total: " . ($pass_count + $fail_count);
echo "\n\n";
