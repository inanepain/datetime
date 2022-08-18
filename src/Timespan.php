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

use DateInterval;
use DateTime;
use DateTimeImmutable;
use Stringable;

use function array_combine;
use function array_filter;
use function array_pop;
use function date;
use function implode;
use function in_array;
use function intval;
use function is_numeric;
use function preg_match_all;
use function preg_replace;
use function time;
use const true;

/** Timespan
 * Timespan
 *
 * @package Inane\Datetime
 *
 * @version 0.2.0
 */
class Timespan implements Stringable {
    /**
     * Single character symbol
     */
    public const SYMBOL_CHAR = 0;

    /**
     * Abbreviated symbol
     *  In some cases this is the full symbol
     */
    public const SYMBOL_ABBREVIATED = 1;

    /**
     * Symbol word
     */
    public const SYMBOL_WORD = 2;

    /**
     * Time period units (symbols and values)
     *
     * @var array
     */
    private static array $units = [
        'y' => ['symbols' => ['years', 'year', 'y', 'yrs', 'yr'], 'value'=> 31556952],
        'm' => ['symbols' => ['months', 'month', 'm', 'months', 'month'], 'value'=> 2629746],
        'w' => ['symbols' => ['weeks', 'week', 'w','weeks', 'week'], 'value'=> 604800],
        'd' => ['symbols' => ['days', 'day', 'd','days', 'day'], 'value'=> 86400],
        'h' => ['symbols' => ['hours', 'hour', 'h', 'hrs', 'hr'], 'value'=> 3600],
        'i' => ['symbols' => ['minutes', 'minute', 'i', 'mins', 'min'], 'value'=> 60],
        's' => ['symbols' => ['seconds', 'second', 's', 'secs', 'sec'], 'value'=> 1],
    ];

    /** __construct
     * Timespan constructor
     *
     * symbol format 2: word
     * symbol format 1: abbreviated
     * symbol format 0: char
     *
     * @param int $timespan seconds
     * @param int $symbolFormat unit symbol character, abbreviation, word
     *
     * @return void
     */
    public function __construct(
        /**
         * timespan (seconds)
         *
         * @var int
         */
        private int $timespan = 0,
        /**
         * unit symbol character, abbreviation, word
         *
         * @var int
         */
        private int $symbolFormat = Timespan::SYMBOL_ABBREVIATED,
    ) {
    }

    /**
     * Create Timespan from $duration string
     *
     * @param string $duration
     *
     * @return static Timespan
     */
    public static function fromDuration(string $duration): static {
        return new static(static::dur2ts($duration));
    }

    /**
     * String representation of timespan
     *
     * @return string duration
     */
    public function __toString(): string {
        return $this->getDuration();
    }

    /**
     * Convert duration to timespan
     *
     * @param string $duration text duration
     *
     * @return int timespan (seconds)
     */
    public static function dur2ts(string $duration): int {
        if (is_numeric($duration)) return intval($duration);

        $pattern = '/(([0-9]+)([a-z]+))/';
        $d = preg_replace('/\s/', '', $duration);
        preg_match_all($pattern, $d, $m);
        $r = array_combine($m[3], $m[2]);
        $s = 0;

        foreach ($r as $u => $a) {
            $matches = array_filter(static::$units, function($unit) use ($u) {
                return in_array($u, $unit['symbols']);
            });
            $match = array_pop($matches) ?? ['value' => 0];
            $v = $match['value'];

            $s += ($a * $v);
        }

        return $s;
    }

    /**
     * Gets the unit symbol by size and style
     *
     * @param bool $single should the unit be single or plural
     * @param int $symbolFormat character, abbreviation, word
     * @param array $symbols available options
     *
     * @return string unit symbol
     */
    private static function getUnitSymbol(bool $single, int $symbolFormat, array $symbols): string {
        return $symbols[$symbolFormat == Timespan::SYMBOL_WORD ? ($single ? 1 : 0) : ($symbolFormat == Timespan::SYMBOL_CHAR ? 2 : ($single ? 4 : 3))];
    }

    /** ts2dur
     * Convert timespan to duration
     *
     * symbol format 2: word
     * symbol format 1: abbreviated
     * symbol format 0: char
     *
     * @param int $timespan seconds
     * @param bool $symbolFormat unit symbol character, abbreviation, word
     * @param array $units array of units to include in duration, single char to be used
     *
     * @return string duration
     */
    public static function ts2dur(int $timespan, int $symbolFormat = Timespan::SYMBOL_ABBREVIATED, array $units = []): string {
        if ($timespan == 0) return match($symbolFormat) {
            static::SYMBOL_CHAR => '0s',
            static::SYMBOL_WORD => '0seconds',
            default => '0secs',
        };

        $r = [];
        foreach (static::$units as $k => $u) {
            if (!empty($units) && !in_array($k, $units)) continue;

            $a = intval($timespan / $u['value']);
            if ($a > 0) {
                $r[] = "$a" . static::getUnitSymbol($a == 1, $symbolFormat, $u['symbols']);
                $timespan = $timespan % $u['value'];
            }
        }

        return implode(' ', $r);
    }

    /**
     * Get Timespan
     *
     * @return int seconds
     */
    public function getTimespan(): int {
        return $this->timespan;
    }

    /**
     * Get Duration
     *
     * @param null|int $symbolFormat  unit symbol current, character, abbreviation, word
     * @param array $units array of units to include in duration, single char to be used
     *
     * @return string duration
     */
    public function getDuration(?int $symbolFormat = null, array $units = []): string {
        return static::ts2dur($this->timespan, $symbolFormat ?? $this->symbolFormat, $units);
    }

    /**
     * Get a unix timestamp adjusted by the timestamp's value
     *
     * @param bool $upcoming true adds ts to now for a future date, false subtracts ts for past date
     *
     * @return int adjusted timestamp
     */
    public function getTimestamp(bool $upcoming = true): int {
        return $upcoming ? ($this->timespan + time()) : (time() - $this->timespan);
    }

    /**
     * Get DateTime
     *
     * @return \DateTime|\DateTimeImmutable DateTime
     */
    public function getDateTime(bool $immutable = false, bool $upcoming = true): DateTime|DateTimeImmutable {
        return $immutable ? new DateTimeImmutable('@' . $this->getTimestamp($upcoming)) : new DateTime('@' . $this->getTimestamp($upcoming));
    }

    /**
     * Get DateInterval
     *
     * @return \DateInterval DateInterval
     */
    public function getDateInterval(): DateInterval {
        return DateInterval::createFromDateString($this->getDuration(Timespan::SYMBOL_ABBREVIATED));
    }

    /**
     * Get a formatted date string
     *
     * Format:
     * @link \DateTimeInterface::format()
     *
     * @param string $format @see \DateTimeInterface::format()
     * @param bool $upcoming true adds ts to now for a future date, false subtracts ts for past date
     *
     * @return string formatted date string
     */
    public function format(string $format = 'Y-m-d H:i:s', bool $upcoming = true): string {
        if (empty($format)) $format = 'Y-m-d H:i:s';

        return date($format, $this->getTimestamp($upcoming));
    }

    /**
     * Adjust current timespan
     *
     * timespan can be negative seconds
     *
     * @param int|string $tsORdur timespan or duration
     *
     * @return \Inane\Datetime\Timespan
     */
    public function adjust(int|string $tsORdur): self {
        $this->timespan += static::dur2ts("$tsORdur");
        return $this;
    }
}
