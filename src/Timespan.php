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
use Stringable;

use function array_combine;
use function array_fill;
use function array_filter;
use function array_key_exists;
use function array_keys;
use function array_merge;
use function array_pop;
use function array_shift;
use function count;
use function implode;
use function in_array;
use function intval;
use function is_int;
use function is_null;
use function is_numeric;
use function is_string;
use function preg_match_all;
use function preg_replace;
use function str_split;
use function str_starts_with;
use const null;

/** Timespan
 * Timespan
 *
 * @package Inane\Datetime
 *
 * @version 0.3.0
 */
class Timespan implements TimeWrapper, Stringable {
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
     * String representation of timespan
     *
     * @return string duration
     */
    public function __toString(): string {
        return $this->getDuration();
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

    /**
     * Parses a duration string into array by unit
     *
     * @param string $duration
     *
     * @return array unit values
     */
    private static function parseDuration(string $duration): array {
        $pattern = '/(([0-9]+)([a-z]+))/';
        $d = preg_replace('/\s/', '', $duration);
        preg_match_all($pattern, $d, $m);

        $invert = ['r' => str_starts_with($d, '-') ? -1 : 1];
        $k = array_combine(array_keys(static::$units), array_fill(0, count(static::$units), 0));
        $r = array_combine($m[3], $m[2]);

        foreach ($r as $u => $a) {
            $matches = array_filter(static::$units, function($unit) use ($u) {
                return in_array($u, $unit['symbols']);
            });

            $match = array_pop($matches) ?? ['value' => 0];
            if (array_key_exists('symbols', $match)) $k[$match['symbols'][2]] = intval($a);
        }

        return array_merge($invert, $k);;
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

        $r = static::parseDuration($duration);
        $invert = array_shift($r);
        $s = 0;

        foreach ($r as $u => $a)
            $s += static::$units[$u]['value'] * $a;

        return $s * $invert;
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
        if ($timespan < 0) {
            $timespan = $timespan * -1;
            $r[] = '-';
        }

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
     * Get Timespan in seconds
     *
     * @return int seconds
     */
    public function getSeconds(): int {
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
     * Get DateInterval
     *
     * @return \DateInterval DateInterval
     */
    public function getDateInterval(): DateInterval {
        return DateInterval::createFromDateString($this->getDuration(Timespan::SYMBOL_ABBREVIATED));
    }

    /**
     * Get duration string
     *
     * Only use units specified in $units
     *
     * Units:
     * - y: years
     * - m: months
     * - w: weeks
     * - d: days
     * - h: hours
     * - i: minutes
     * - s: seconds
     *
     * @param string $units specify desired units
     *
     * @return string duration
     */
    public function duration(string $units = 'ymdhis'): string {
        if (empty($units)) $units = ['y','m','d','h','i','s'];
        else $units = str_split($units);

        return static::ts2dur($this->timespan, $this->symbolFormat, $units);
    }

    /**
     * Get a formatted duration string
     *
     * Units:
     * - %r: sign (only shown if negative)
     * - %y: years
     * - %m: months
     * - %w: weeks
     * - %d: days
     * - %h: hours
     * - %i: minutes
     * - %s: seconds
     *
     * @param string $format specify desired units
     *
     * @return string formatted timespan string
     */
    public function format(string $format = '%r%yyrs %mmonth %ddays %hhrs %imin %ssecs'): string {
        $r = static::parseDuration($this->getDuration());
        $r['r'] = $r['r'] < 0 ? '-' : '';

        foreach($r as $u => $v)
            $format = \str_replace("%$u", "$v", $format);

        return $format;
    }

    /**
     * Adjust current timespan
     *
     * timespan can be negative seconds
     *
     * @param int|string|\Inane\Datetime\Timespan $tsORdur timespan, Timespan or duration
     *
     * @return \Inane\Datetime\Timespan
     */
    public function adjust(int|string|Timespan $tsORdur): self {
        if ($tsORdur instanceof Timespan) $tsORdur = $tsORdur->getSeconds();
        else if (is_string($tsORdur) && !is_numeric($tsORdur)) $tsORdur = static::dur2ts("$tsORdur");
        else if (is_string($tsORdur) && is_numeric($tsORdur)) $tsORdur = intval($tsORdur);

        $this->timespan += $tsORdur;
        return $this;
    }

    /**
     * Apply to Timestamp
     *
     * If none supplied Timestamp is uses NOW.
     *
     * @param null|int|\Inane\Datetime\Timestamp $timestamp to modify
     *
     * @return \Inane\Datetime\Timestamp
     */
    public function apply2Timestamp(null|int|Timestamp $timestamp = null): Timestamp {
        if (is_null($timestamp)) $timestamp = new Timestamp();
        else if (is_int($timestamp)) $timestamp = new Timestamp($timestamp);

        $timestamp->adjust($this->timespan);

        return $timestamp;
    }

    /**
     * Absolute value
     *
     * @since 0.3.0
     *
     * @return \Inane\Datetime\Timespan An absolute copy
     */
    public function abs(): Timespan {
        return new static(abs($this->timespan));
    }
}
