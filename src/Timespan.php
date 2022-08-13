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

use Stringable;

use function array_combine;
use function implode;
use function intval;
use function is_int;
use function is_numeric;
use function preg_match_all;
use function preg_replace;
use function time;

/**
 * Timespan
 *
 * @package Inane\Datetime
 *
 * @version 0.1.0
 */
class Timespan implements Stringable {
    public function __construct(
        private int $timespan = 0,
    ) {
    }

    public function __toString(): string {
        return static::makeReadable($this->timespan);
    }

    public static function fromDuration(int|string $duration, bool $enableYear = false): static {
        return new static(static::parseDuration($duration, $enableYear));
    }

    public static function parseDuration(int|string $duration, bool $enableYear = false): int {
        $year = $enableYear ? 31536000 : 0;
        $s = 0;

        if (is_int($duration)) $s = $duration;
        else if (is_numeric($duration)) $s = intval($duration);
        else {
            $pattern = '/(([0-9]+)([a-z]+))/';
            $d = preg_replace('/\s/', '', $duration);
            preg_match_all($pattern, $d, $m);
            $r = array_combine($m[3], $m[2]);

            foreach ($r as $u => $a) {
                $v = match ($u) {
                    'years', 'year', 'yrs', 'yr', 'y' => $year,
                    'weeks', 'week', 'w' => 604800,
                    'days', 'day', 'd' => 86400,
                    'hours', 'hour', 'hrs', 'hr', 'h' => 3600,
                    'minutes', 'minute', 'mins', 'min', 'm' => 60,
                    'seconds', 'second', 'secs', 'sec', 's' => 1,
                    default => 0,
                };

                $s += ($a * $v);
            }
        }

        return $s;
    }

    public static function makeReadable(int $timespan): string {
        if ($timespan == 0) return '0s';

        $units = [
            'y' => 31536000,
            'w' => 604800,
            'd' => 86400,
            'h' => 3600,
            'm' => 60,
            's' => 1,
        ];

        $r = [];

        foreach($units as $u => $v) {
            $a = intval($timespan / $v);
            if ($a > 0) {
                $r[] = "$a$u";
                $timespan = $timespan % $v;
            }
        }

        return implode(' ', $r);
    }

    public function getTimespan(): int {
        return $this->timespan;
    }

    public function getTimestamp(): int {
        return $this->timespan + time();
    }

    public function addDuration(int|string $duration, bool $enableYear = false): self {
        $this->timespan += static::parseDuration($duration, $enableYear);
        return $this;
    }
}
