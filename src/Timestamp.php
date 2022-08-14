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

use function date;
use function is_null;
use function time;

/**
 * Timestamp
 *
 * @package Inane\Datetime
 *
 * @version 0.1.0
 */
class Timestamp implements Stringable {
    /**
     * Timespan constructor
     *
     * symbol type 2: long
     * symbol type 1: medium
     * symbol type 0: single
     *
     * @param int $timestamp seconds
     * @param int $symbolType unit symbol single, medium, long
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
     * String representation of timestamp
     *
     * @return string date
     */
    public function __toString(): string {
        return $this->format();
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
     * Adjust timestamp by $seconds
     *
     * @param int $seconds positive or negative seconds
     *
     * @return \Inane\Datetime\Timestamp
     */
    public function adjust(int $seconds): self {
        $this->timestamp += $seconds;
        return $this;
    }

    /**
     * Difference between two Timestamps
     *
     * @param Timestamp $timestamp from which to measure difference
     *
     * @return \Inane\Datetime\Timespan
     */
    public function diff(Timestamp $timestamp): Timespan {
        return new Timespan($this->timestamp > $timestamp->timestamp ? $this->timestamp - $timestamp->timestamp : $timestamp->timestamp - $this->timestamp);
    }
}
