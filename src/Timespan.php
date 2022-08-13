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
 * @license https://github.com/inanepain/stdlib/raw/develop/UNLICENSE UNLICENSE
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
use function date;
use function implode;
use function intval;
use function is_numeric;
use function preg_match_all;
use function preg_replace;
use function time;
use const true;

/**
 * Timespan
 *
 * @package Inane\Datetime
 *
 * @version 0.1.0
 */
class Timespan implements Stringable {
    /**
     * Single symbol character
     */
    public const SYMBOL_SINGLE = 0;

    /**
     * Abbreviated symbol
     */
    public const SYMBOL_MEDIUM = 1;

    /**
     * Symbol word
     */
    public const SYMBOL_LONG = 2;

    /**
     * Time period units (symbols and values)
     *
     * @var array
     */
    private static array $units = [
        'y' => ['symbols' => ['years', 'year', 'y', 'yrs', 'yr'], 'value'=> 31556952],
        'w' => ['symbols' => ['weeks', 'week', 'w','weeks', 'week'], 'value'=> 604800],
        'd' => ['symbols' => ['days', 'day', 'd','days', 'day'], 'value'=> 86400],
        'h' => ['symbols' => ['hours', 'hour', 'h', 'hrs', 'hr'], 'value'=> 3600],
        'm' => ['symbols' => ['minutes', 'minute', 'm', 'mins', 'min'], 'value'=> 60],
        's' => ['symbols' => ['seconds', 'second', 's', 'secs', 'sec'], 'value'=> 1],
    ];

    /**
     * Timespan constructor
     *
     * symbol type 2: long
     * symbol type 1: medium
     * symbol type 0: single
     *
     * @param int $timespan seconds
     * @param int $symbolType unit symbol single, medium, long
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
         * unit symbol single, medium, long
         *
         * @var int
         */
        private int $symbolType = Timespan::SYMBOL_MEDIUM,
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
            $v = match(true) {
                in_array($u, static::$units['y']['symbols']) => static::$units['y']['value'],
                in_array($u, static::$units['w']['symbols']) => static::$units['w']['value'],
                in_array($u, static::$units['d']['symbols']) => static::$units['d']['value'],
                in_array($u, static::$units['h']['symbols']) => static::$units['h']['value'],
                in_array($u, static::$units['m']['symbols']) => static::$units['m']['value'],
                in_array($u, static::$units['s']['symbols']) => static::$units['s']['value'],
                default => 0,
            };

            $s += ($a * $v);
        }

        return $s;
    }

    /**
     * Gets the unit symbol by size and style
     *
     * @param bool $single singular
     * @param int $type single, medium, long
     * @param array $symbols available options
     *
     * @return string unit symbol
     */
    private static function getUnitSymbol(bool $single, int $type, array $symbols): string {
        return $symbols[$type == Timespan::SYMBOL_LONG ? ($single ? 1 : 0) : ($type == Timespan::SYMBOL_SINGLE ? 2 : ($single ? 4 : 3))];
    }

    /**
     * Convert timespan to duration
     *
     * symbol type 2: long
     * symbol type 1: medium
     * symbol type 0: single
     *
     * @param int $timespan seconds
     * @param bool $symbolType unit symbol single, medium, long
     *
     * @return string duration
     */
    public static function ts2dur(int $timespan, int $symbolType = Timespan::SYMBOL_MEDIUM): string {
        if ($timespan == 0) return match($symbolType) {
            static::SYMBOL_SINGLE => '0s',
            static::SYMBOL_LONG => '0seconds',
            default => '0secs',
        };

        $r = [];
        foreach (static::$units as $u) {
            $a = intval($timespan / $u['value']);
            if ($a > 0) {
                $r[] = "$a" . static::getUnitSymbol($a == 1, $symbolType, $u['symbols']);
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
     * @param null|int $symbolType  unit symbol current, single, medium, long
     *
     * @return string duration
     */
    public function getDuration(?int $symbolType = null): string {
        return static::ts2dur($this->timespan, $symbolType ?? $this->symbolType);
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
        return DateInterval::createFromDateString($this->getDuration(Timespan::SYMBOL_MEDIUM));
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
