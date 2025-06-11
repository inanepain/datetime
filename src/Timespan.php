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

use DateInterval;
use Stringable;

use function abs;
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
use function is_numeric;
use function is_string;
use function preg_match_all;
use function preg_replace;
use function str_replace;
use function str_split;
use function str_starts_with;

use const null;

/**
 * Timespan
 *
 * A duration of time stored as a number of seconds and can be formatted for display as desired.
 *
 * @version 0.4.0
 */
class Timespan implements TimeWrapper, Stringable {
    /**
     * Single character symbol
     *
     * @var int
     */
    public const int SYMBOL_CHAR = 0;

    /**
     * Abbreviated symbol
     *  In some cases this is the full symbol
     *
     * @var int
     */
    public const int SYMBOL_ABBREVIATED = 1;

    /**
     * Symbol word
     *
     * @var int
     */
    public const int SYMBOL_WORD = 2;

    /**
     * Time period units (symbols and values)
     *
     * The $units data defines how to parse a duration string
     *  first by the unit symbol, which tells us what to look for
     *  then by the unit value, which gives us the units second value
     *
     * @var array
     */
    private static array $units = [
        'y' => ['symbols' => ['years', 'year', 'y', 'yrs', 'yr'], 'value'=> 31556952],
        'm' => ['symbols' => ['months', 'month', 'm', 'months', 'month'], 'value'=> 2628029],
        'w' => ['symbols' => ['weeks', 'week', 'w','weeks', 'week'], 'value'=> 604800],
        'd' => ['symbols' => ['days', 'day', 'd','days', 'day'], 'value'=> 86400],
        'h' => ['symbols' => ['hours', 'hour', 'h', 'hrs', 'hr'], 'value'=> 3600],
        'i' => ['symbols' => ['minutes', 'minute', 'i', 'mins', 'min'], 'value'=> 60],
        's' => ['symbols' => ['seconds', 'second', 's', 'secs', 'sec'], 'value'=> 1],
    ];

    /**
     * Uses the TimeTrait to provide additional functionality
     * related to time operations within the Timespan class.
     */
    // use TimeTrait;
    /**
     * Represents the number of seconds as an integer.
     *
     * @since 0.4.0
     *
     * @var int $seconds The number of seconds.
     */
    public private(set) int $seconds {
        get => intval($this->milliseconds * 1000);
        set(?int $value) {
            $this->milliseconds = $value / 1000;
        }
    }

    /**
     * Represents the number of milliseconds.
     *
     * @since 0.4.0
     *
     * @var float $milliseconds The number of milliseconds as a float.
     */
    public private(set) float $milliseconds {
        get => $this->microseconds * 1000;
        // get => intval($this->microseconds * 1000);
        set(?float $value) {
            $this->microseconds = $value / 1000;
        }
    }

    /**
     * The number of microseconds.
     *
     * @since 0.4.0
     *
     * This property represents the microsecond component of a timestamp.
     *
     * @var float $microseconds The number of microseconds as a float.
     */
    public private(set) float $microseconds {
        get => $this->microseconds;
        set(?float $value) {
            $this->microseconds = $value;
        }
    }

    /**
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
        private int $timespan = 0 {
            get => $this->timespan;
            set(?int $value) {
                $this->timespan = $value ?? 0;
                $this->seconds = $value;
            }
        },
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
     * @param string $duration A duration string (e.g. "1hr 30mins 15secs")
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
     * @param string $duration A duration string (e.g. "1hr 30mins 15secs")
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
     * Convert a duration string into timespan as seconds
     *
     * @param string $duration A duration string (e.g. "1hr 30mins 15secs")
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

        return intval($s * $invert);
    }

    /**
     * Convert timespan to duration
     *
     * symbol format 2: word
     * symbol format 1: abbreviated
     * symbol format 0: char
     *
     * @since 0.4.0 added $spaced parameter
     *
     * @param int $timespan the length of the timespan in seconds
     * @param int $symbolFormat unit symbol: character, abbreviation, word
     * @param array $units array of units to include in the duration, single char to be used (e.g. ['y','m','d','h','i','s'])
     * @param bool $spaced should there be a space between the unit and the value (e.g. '1 h' vs '1h')
     *
     * @return string the duration string using the specified units of time
     */
    public static function ts2dur(int $timespan, int $symbolFormat = Timespan::SYMBOL_ABBREVIATED, array $units = [], bool $spaced = false): string {
        if ($timespan == 0) return match($symbolFormat) {
            static::SYMBOL_CHAR => '0s',
            static::SYMBOL_WORD => '0seconds',
            default => '0secs',
        };

        $gap = $spaced ? ' ' : '';

        $r = [];
        if ($timespan < 0) {
            // dd('$timespan = $timespan * -1', 'Testing Code');
            // $timespan = $timespan * -1;
            $timespan *= -1;
            $r[] = '-';
        }

        foreach (static::$units as $k => $u) {
            if (!empty($units) && !in_array($k, $units)) continue;

            $a = intval($timespan / $u['value']);
            if ($a > 0) {
                $r[] = (string)$a . $gap . static::getUnitSymbol($a == 1, $symbolFormat, $u['symbols']);
                // $timespan = $timespan % $u['value'];
                $timespan %= $u['value'];
            }
        }

        return implode(' ', $r);
    }

    /**
     * Get Timespan in seconds
     *
     * @return int the length of the timespan in seconds
     */
    public function getSeconds(): int {
        return $this->timespan;
    }

    /**
     * Get Duration
     *
     * @since 0.4.0 added $spaced parameter
     *
     * @param null|int $symbolFormat  unit symbol current, character, abbreviation, word
     * @param array $units array of units to include in duration, single char to be used (e.g. ['y','m','d','h','i','s'])
     * @param bool $spaced should there be a space between the unit and the value (e.g. '1 h' vs '1h')
     *
     * @return string the duration string using the specified units of time
     */
    public function getDuration(?int $symbolFormat = null, array $units = [], bool $spaced = false): string {
        return static::ts2dur($this->timespan, $symbolFormat ?? $this->symbolFormat, $units, $spaced);
    }

    /**
     * Get DateInterval
     *
     * @return \DateInterval DateInterval
     */
    public function getDateInterval(): DateInterval {
        return DateInterval::createFromDateString($this->getDuration(symbolFormat: Timespan::SYMBOL_WORD, spaced: false));
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
     * @return string the duration string using the specified units of time
     */
    public function duration(string $units = 'ymdhis'): string {
        $units = empty($units) ? ['y','m','d','h','i','s'] : str_split($units);

        return static::ts2dur($this->timespan, $this->symbolFormat, $units);
    }

    /**
     * Get a formatted duration string
     *
     * Formatting patterns are akin to those of the `date` function
     *  Except that a percent (`%`) symbol is used to denote them
     *  Only a select subset of applicable symbols are available
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
     * @param string $format string template with symbols as placeholders to be filled
     *
     * @return string formatted timespan string
     */
    public function format(string $format = '%r%yyrs %mmonth %ddays %hhrs %imin %ssecs'): string {
        $r = static::parseDuration($this->getDuration());
        $r['r'] = $r['r'] < 0 ? '-' : '';

        foreach($r as $u => $v)
            $format = str_replace("%$u", (string)$v, $format);

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
        else if (is_string($tsORdur) && !is_numeric($tsORdur)) $tsORdur = static::dur2ts((string)$tsORdur);
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
        if ($timestamp === null) $timestamp = new Timestamp();
        else if (is_int($timestamp)) $timestamp = new Timestamp($timestamp);

        $timestamp->adjust($this);

        return $timestamp;
    }

    /**
     * Get a copy with an absolute value
     *
     * @since 0.3.0
     *
     * @return \Inane\Datetime\Timespan An absolute copy
     */
    public function absoluteCopy(): Timespan {
        return new static(abs($this->timespan));
    }
}
