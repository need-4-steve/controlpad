<?php
/**
 * I belong to a file
 */

namespace CPCommon\Pid;

use Ramsey\Uuid\Uuid;
use Brick\Math\BigInteger;

/**
 * A class for generating public IDs (pids).
 */
class Pid
{
    /**
     * Creates a public ID (pid) for use in ControlPad projects. These are compressed version 4 UUIDs.
     *
     * @param {string} [$uuid=''] An optional UUID. If not supplied, one will be generated.
     *
     * @return {string} A public ID
     */
    public static function create($uuid = '')
    {
        if ($uuid === '') {
            $uuid = Uuid::uuid4()->toString();
        }
        $uuid = strtolower(str_replace('-', '', $uuid));
        $pid = Pid::baseToBase($uuid, Pid::UUID_BASE16, Pid::PID_BASE36);
        return str_pad($pid, 25, "0", STR_PAD_LEFT);
    }

    public static function toUuid($pid)
    {
        $uuid = Pid::baseToBase($pid, Pid::PID_BASE36, Pid::UUID_BASE16);
        $uuid = str_pad($uuid, 32, "0", STR_PAD_LEFT);
        return preg_replace(Pid::RE_UUID_NO_HYPHEN, Pid::RE_UUID_WITH_HYPHEN, $uuid);
    }

    private const SYMBOLS = '0123456789abcdefghijklmnopqrstuvwxyz';
    private const UUID_BASE16 = 16;
    private const PID_BASE36 = 36;
    private const RE_UUID_NO_HYPHEN = '/^([a-f0-9]{8})([a-f0-9]{4})([a-f0-9]{4})([a-f0-9]{4})([a-f0-9]{12})$/';
    private const RE_UUID_WITH_HYPHEN = '${1}-${2}-${3}-${4}-${5}';

    private static function baseToBase($digits, $base1, $base2)
    {
        return Pid::valueToForm(Pid::formToValue($digits, $base1), $base2);
    }

    private static function valueToForm($number, $base)
    {
        if (!is_a($base, BigInteger::class)) {
            $base = BigInteger::of($base);
        }
        if (!is_a($number, BigInteger::class)) {
            $number = BigInteger::of($number);
        }
        $zero = BigInteger::of(0);
        $digits = '';
        while ($number->compareTo($zero) > 0) {
            list($quotient, $remainder) = $number->quotientAndRemainder($base);
            $digits = Pid::SYMBOLS[(int)$remainder->toBase(10)] . $digits;
            $number = $quotient;
        }
        return $digits;
    }

    private static function formToValue($digits, $base)
    {
        $baseAsBigInt = BigInteger::of($base);
        $digits = preg_split('//u', $digits, -1, PREG_SPLIT_NO_EMPTY);
        if ($base > Pid::PID_BASE36) {
            throw new Exception("Base '{$base}' exceeds maximum: '" . Pid::PID_BASE36 . "'");
        }
        $subset = substr(Pid::SYMBOLS, 0, $base);
        $number = BigInteger::of(0);
        foreach ($digits as &$char) {
            $index = strpos($subset, $char);
            if ($index === false) {
                throw new Exception("Invalid digit '{$char}', not found in base '{$base}' digits '{$subset}'.");
            }
            $number = ($number->multipliedBy($baseAsBigInt))->plus(BigInteger::of($index));
        }
        return $number;
    }
}
