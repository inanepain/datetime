<?php

/**
 * Inane: Datetime
 *
 * Inane Datetime Library
 *
 * $Id$
 * $Date$
 *
 * PHP version 8.4
 *
 * @author Philip Michael Raab<philip@cathedral.co.za>
 * @package inanepain\datetime
 * @category datetime
 *
 * @license UNLICENSE
 * @license https://unlicense.org/UNLICENSE UNLICENSE
 *
 * _version_ $version
 */

declare(strict_types=1);

namespace Inane\Datetime;

use Inane\Stdlib\Enum\CoreEnumInterface;

use Inane\Stdlib\Enum\CoreEnumTrait;
use function intval;
use function microtime;
use function preg_match;
use function strlen;

use const false;
use const null;
use const true;

/**
 * Enum Timescale sets the level of precision.
 *
 * Represents a timescale with integer values.
 * This enum implements the `CoreEnumInterface`, providing additional functionality for working with enumerated types.
 *
 * @version 0.2.0
 */
enum Timescale: int implements CoreEnumInterface {

    /**
     * Represents the timescale unit for microseconds.
     *
     * This constant is used to define a precision level of 16,
     * corresponding to microsecond-level granularity in time measurement.
     */
    case MICROSECOND = 16;

    /**
     * Represents the millisecond timescale.
     *
     * This constant is used to define a timescale with a precision of milliseconds.
     */
    /**
     * Represents the timescale unit for milliseconds.
     *
     * This constant is used to define a precision level of 13,
     * corresponding to millisecond-level granularity in time measurement.
     */
    case MILLISECOND = 13;

    /**
     * Represents the timescale unit for seconds.
     *
     * This constant is used to define a precision level of 10,
     * corresponding to second-level granularity in time measurement.
     */
    case SECOND = 10;

    /**
     * This file is part of the inanepain datetime library.
     *
     * It includes the usage of the CoreEnumTrait, which provides
     * additional functionality or behavior to the Timescale enum.
     *
     * @uses CoreEnumTrait
     */
    use CoreEnumTrait;

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

        $timestamp = (string) $timestamp;
        $length = strlen($timestamp);

        switch ($length) {
            case 1:
            case 2:
            case 3:
            case 4:
            case 5:
            case 6:
            case 7:
            case 8:
            case 9:
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
     * Retrieves the current timestamp at the current scale.
     *
     * @param bool $asObject If *true*, returns the timestamp as a `Timestamp` object.
     *                       If *false*, returns the timestamp as an integer.
     *
     * @return int|Timestamp The current timestamp, either as an integer or a `Timestamp` object.
     */
    public function timestamp(bool $asObject = false): int|Timestamp {
        $ts = match ($this) {
            self::MICROSECOND => intval(time() * 1000000),
            self::MILLISECOND => intval(time() * 1000),
            self::SECOND => time(),
        };

        return $asObject ? new Timestamp($ts) : $ts;
    }

    /**
     * Retrieves the unit of time as a string.
     *
     * @return string The unit of time.
     */
    public function unit(): string {
        return match ($this) {
            self::MICROSECOND => 'microseconds',
            self::MILLISECOND => 'milliseconds',
            self::SECOND => 'seconds',
        };
    }
}
