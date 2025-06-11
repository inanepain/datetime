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
 * @version $version
 */

declare(strict_types=1);

namespace Inane\Datetime;

use DateTime;
use DateTimeImmutable;
use Stringable;

use function abs;
use function date;
use function is_int;
use function intval;
use function time;

/**
 * Timestamp
 *
 * A point in time, a date.
 * Unix time is measured as the number of seconds since or prior to <strong>01 January 1970 00:00:00 AM GMT</strong>.
 *
 * @property int $timestamp - unix timestamp
 *
 * @version 0.4.0
 */
class Timestamp implements TimeWrapper, Stringable {
    /**
     * timestamp
     *
     * Returns the timestamp based on the set timescale.
     *
     * @var int the timestamp
     */
    public private(set) int $timestamp {
        get {
            return match ($this->timescale) {
                Timescale::SECOND => $this->seconds,
                Timescale::MILLISECOND => $this->milliseconds,
                Timescale::MICROSECOND => $this->microseconds,
            };
        }
        set(?int $value) {
            $this->seconds = $value;
        }
    }

    /**
     * @var Timescale $timescale The timescale used for the timestamp,
     *                           defaulting to seconds.
     */
    private Timescale $timescale;

    use TimeTrait;

    /**
     * Constructor for the Timestamp class.
     *
     * @param int|null $timestamp The timestamp value. If null, the current time will be used.
     * @param ?Timescale $timescale The timescale unit for precision. If null will autodetect the timescale.
     */
    public function __construct(?int $timestamp = null, ?Timescale $timescale = null) {
        $this->timescale = $timescale ?: (Timescale::tryFromTimestamp($timestamp ?: Timescale::SECOND->timestamp()) ?: Timescale::SECOND);
        $this->timestamp = $timestamp ?: $this->timescale->timestamp();
    }

    /**
     * Timestamp as a formatted string
     *
     * @return string date
     */
    public function __toString(): string {
        return $this->format();
    }

    /**
     * Parses a time string according to a specified format
     *
     * Format Examples:
     *  - 'd F Y'           : 05 May 2022
     *  - 'D d F Y'         : Thu 05 May 2022
     *  - 'l g:i:sa'        : Thursday 5:05:05am
     *  - 'd F Y g:i:s A T' : 05 May 2022 05:05:05 AM SAST
     *  - 'U'               : 1651719905 (string)
     *
     * @since 0.3.0
     *
     * @see doc/timewrapper.adoc#format about format symbols
     * @see \DateTimeImmutable::createFromFormat about format symbols
     *
     * @param string $format The format that the passed in string should be in
     * @param string $datetime String representing the time
     *
     * @return static|false Returns a new Timestamp instance or false on failure.
     */
    public static function createFromFormat(string $format, string $datetime): static|false {
        $datetime = DateTime::createFromFormat($format, $datetime);

        return $datetime === false ? false : new static(intval($datetime->format('U')));
    }

    /**
     * Get current unix time
     *
     * @since 0.3.1
     *
     * @return int timestamp
     */
    public static function now(): int {
        return time();
    }

    /**
     * Get as seconds
     *
     * length: 10 digits
     *
     * @return int seconds
     */
    public function getSeconds(): int {
        return $this->seconds;
    }

    /**
     * Get as milliseconds
     *
     * length: 13 digits
     *
     * @since 0.4.0
     *
     * @return int milliseconds
     */
    public function getMilliseconds(): int {
        return $this->milliseconds;
    }

    /**
     * Get as microseconds
     *
     * length: 16 digits
     *
     * @since 0.4.0
     *
     * @return int microseconds
     */
    public function getMicroseconds(): int {
        return $this->microseconds;
    }

    /**
     * Get DateTime
     *
     * @param bool $immutable get DateTimeImmutable
     *
     * @return \DateTime|\DateTimeImmutable DateTime
     */
    public function getDateTime(bool $immutable = false): DateTime|DateTimeImmutable {
        // return $immutable ? new DateTimeImmutable(datetime: "@{$this->timestamp}") : new DateTime("@{$this->timestamp}");
        return $immutable ? new DateTimeImmutable(datetime: "@{$this->seconds}") : new DateTime("@{$this->seconds}");
    }

    /**
     * Get a formatted date string
     *
     * Format:
     * @link \DateTimeInterface::format()
     *
     * @param string $format @see \DateTimeInterface::format()
     *
     * @return string formatted date string
     */
    public function format(string $format = 'Y-m-d H:i:s'): string {
        if (empty($format)) $format = 'Y-m-d H:i:s';

        // return date($format, $this->timestamp);
        return date($format, $this->seconds);
    }

    /**
     * Adjust timestamp by $timespan
     *
     * @param int|\Inane\Datetime\Timespan $timespan positive or negative seconds or Timespan
     *
     * @return \Inane\Datetime\Timestamp
     */
    public function adjust(int|Timespan $timespan): self {
        // $this->timestamp += is_int($timespan) ? $timespan : $timespan->getSeconds();
        $this->timestamp += is_int($timespan) ? $timespan : $timespan->{$this->timescale->unit()};
        return $this;
    }

    /**
     * Difference between two Timestamps
     *
     * @param int|\Inane\Datetime\Timestamp $timestamp from which to measure difference
     *
     * @return \Inane\Datetime\Timespan
     */
    public function diff(int|Timestamp $timestamp): Timespan {
        // $ts = is_int($timestamp) ? $timestamp : $timestamp->timestamp;
        $ts = is_int($timestamp) ? $timestamp : $timestamp->{$this->timescale->unit()};
        return new Timespan($ts - $this->timestamp);
    }

    /**
     * Get a copy with an absolute value
     *
     * @since 0.2.0
     *
     * @return \Inane\Datetime\Timestamp An absolute copy
     */
    public function absoluteCopy(): Timestamp {
        return new static(abs($this->timestamp), $this->timescale);
    }
}
