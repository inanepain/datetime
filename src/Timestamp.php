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
 *
 * @license UNLICENSE
 * @license https://github.com/inanepain/datetime/raw/develop/UNLICENSE UNLICENSE
 *
 * @version $Id$
 * $Date$
 */

declare(strict_types=1);

namespace Inane\Datetime;

use DateTime;
use DateTimeImmutable;
use Stringable;

use function abs;
use function date;
use function is_int;
use function is_null;
use function time;

/**
 * Timestamp
 *
 * A point in time, a date.
 * Unix time is measured as the number of seconds since or prior to <strong>01 January 1970 00:00:00 AM GMT</strong>.
 *
 * @property int $timestamp - unix timestamp
 *
 * @package Inane\Datetime
 *
 * @version 0.2.0
 */
class Timestamp implements TimeWrapper, Stringable {
    /**
     * Timestamp constructor
     *
     * @param int $timestamp seconds
     *
     * @return void
     */
    public function __construct(
        /**
         * timestamp (seconds)
         *
         * @var int
         */
        private ?int $timestamp = null,
    ) {
        if (is_null($this->timestamp)) $this->timestamp = time();
    }

    /**
     * Get property $name
     *
     * @return string date
     */
    public function __get(string $name): mixed {
        return match($name) {
            'timestamp' => $this->timestamp,
            default => throw new \Inane\Stdlib\Exception\InvalidPropertyException("Timestamp:=not found: `$name`!"),
        };
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
     * Get as seconds
     *
     * @return int seconds
     */
    public function getSeconds(): int {
        return $this->timestamp;
    }

    /**
     * Get DateTime
     *
     * @param bool $immutable get DateTimeImmutable
     *
     * @return \DateTime|\DateTimeImmutable DateTime
     */
    public function getDateTime(bool $immutable = false): DateTime|DateTimeImmutable {
        return $immutable ? new DateTimeImmutable('@' . $this->timestamp) : new DateTime('@' . $this->timestamp);
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

        return date($format, $this->timestamp);
    }

    /**
     * Adjust timestamp by $timespan
     *
     * @param int|\Inane\Datetime\Timespan $timespan positive or negative seconds or Timespan
     *
     * @return \Inane\Datetime\Timestamp
     */
    public function adjust(int|Timespan $timespan): self {
        $this->timestamp += is_int($timespan) ? $timespan : $timespan->getSeconds();
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
        $ts = is_int($timestamp) ? $timestamp : $timestamp->timestamp;
        return new Timespan($ts - $this->timestamp);
    }

    /**
     * Absolute value
     *
     * @since 0.2.0
     *
     * @return \Inane\Datetime\Timestamp An absolute copy
     */
    public function abs(): Timestamp {
        return new static(abs($this->timestamp));
    }
}
