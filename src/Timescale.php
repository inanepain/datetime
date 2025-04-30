<?php

/**
 * Inane: Datetime
 *
 * Inane Datetime Library
 *
 * PHP version 8.1
 *
 * @author Philip Michael Raab<peep@inane.co.za>
 * @package Inane\Datetime
 * @category datetime
 *
 * @license UNLICENSE
 * @license https://github.com/inanepain/datetime/raw/develop/UNLICENSE UNLICENSE
 *
 * @version $Id$
 * $Date$
 */

declare(strict_types=1);

namespace Inane\Datetime;

use Inane\Stdlib\Enum\CoreEnumInterface;

use function intval;
use function microtime;
use function strcasecmp;

use const false;
use const true;

/**
 * TimeScale
 *
 * @package Inane\Datetime
 *
 * @version 0.1.0
 */
enum Timescale: int implements CoreEnumInterface {
/**
     * Represents the time scale in microseconds.
     */
    case MICROSECOND = 16;
/**
     * Represents the millisecond time scale.
     */
    case MILLISECOND = 13;
/**
     * Represents a time scale measured in seconds.
     */
    case SECOND = 10;

    /**
     * Example implementation: Try get enum from name
     *
     * @param string $name       enum name
     * @param bool   $ignoreCase case insensitive option
     *
     * @return null|static
     */
    public static function tryFromName(string $name, bool $ignoreCase = false): ?static {
        foreach (static::cases() as $case)
            if (($ignoreCase && strcasecmp($case->name, $name) == 0) || $case->name === $name)
                return $case;

        return null;
    }

    /**
     * Attempts to determine the time scale of the given timestamp.
     *
     * @param int|Timestamp $timestamp The timestamp to evaluate. Can be an integer or a Timestamp object.
     *
     * @return Timescale|null Returns the corresponding TimeScaleEnum if the scale is determined, or null if it cannot be determined.
     */
    public static function tryFromTimestamp(int|Timestamp $timestamp): ?Timescale {
        if ($timestamp instanceof Timestamp)
            $timestamp = $timestamp->microseconds;

        $timestamp = (string)$timestamp;
        $length = strlen($timestamp);

        switch ($length) {
            case 10:
                return static::SECOND;
            case 13:
                if (preg_match('/^[0-9]{10}000$/', $timestamp)) {
                    return static::SECOND;
                } else {
                    return static::MILLISECOND;
                }
            case 16:
                if (preg_match('/^[0-9]{10}000000$/', $timestamp)) {
                    return static::SECOND;
                } elseif (preg_match('/^[0-9]{13}000$/', $timestamp)) {
                    return static::MILLISECOND;
                } else {
                    return static::MICROSECOND;
                }
        }

        return null;
    }

    /**
     * Creates a timestamp based on the current time scale.
     *
     * @return int|Timestamp The generated timestamp as an integer.
     */
    public function timestamp(bool $asObject = false): int|Timestamp {
        $ts = match ($this) {
            self::MICROSECOND => intval(microtime(true) * 1000000),
            self::MILLISECOND => intval(microtime(true) * 1000),
            self::SECOND => intval(microtime(true)),
        };

        return $asObject ? new Timestamp($ts) : $ts;
    }

    public function unit(): string {
        return match ($this) {
            self::MICROSECOND => 'microseconds',
            self::MILLISECOND => 'milliseconds',
            self::SECOND => 'seconds',
        };
    }
}
