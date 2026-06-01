<?php

/**
 * Inane: Datetime
 *
 * Inane Datetime Library
 *
 * $Id$
 * $Date$
 *
 * PHP version 8.5
 *
 * @author   Philip Michael Raab<philip@cathedral.co.za>
 * @package  inanepain\datetime
 * @category datetime
 *
 * @license  UNLICENSE
 * @license  https://unlicense.org/UNLICENSE UNLICENSE
 *
 * _version_ $version
 */

declare(strict_types = 1);

namespace Inane\Datetime\Tests;

use Inane\Datetime\{
    Timespan,
    Timestamp};
use PHPUnit\Framework\TestCase;

/**
 * Tests for `Inane\\Datetime\\Timespan` behavior and formatting helpers.
 */
final class TimespanTest extends TestCase {
    /**
     * Verifies construction from seconds and basic getter/format outputs.
     */
    public function testConstructAndGetters(): void {
        // 1 hour, 1 minute, 1 second
        $s = new Timespan(3661); // 1h 1m 1s

        // Raw seconds should match constructor value
        $this->assertSame(3661, $s->getSeconds());

        // Default human-readable duration (words, compact)
        $this->assertSame('1hr 1min 1sec', $s->getDuration());

        // Symbol format (single-char) with no spaces
        $this->assertSame('1h 1m 1s', $s->getDuration(symbolFormat: Timespan::SYMBOL_CHAR, spaced: false));
    }

    /**
     * Ensures duration parsing to seconds and factory creation from strings work.
     */
    public function testFromDurationAndDur2Ts(): void {
        // Parse mixed unit string to seconds
        $this->assertSame(90, Timespan::dur2ts('1min30secs'));

        // Negative durations should produce negative seconds
        $this->assertSame(-3600, Timespan::dur2ts('-1hr'));

        // Factory builds a Timespan equivalent to parsed seconds
        $s = Timespan::fromDuration('2hrs 5mins');
        $this->assertSame(2 * 3600 + 5 * 60, $s->getSeconds());
    }

    /**
     * Checks `ts2dur` formatting variants and spacing rules for positive/negative values.
     */
    public function testTs2DurFormattingAndSpacing(): void {
        // Zero seconds formatting variants
        $this->assertSame('0secs', Timespan::ts2dur(0));
        $this->assertSame('0s', Timespan::ts2dur(0, Timespan::SYMBOL_CHAR));
        $this->assertSame('0seconds', Timespan::ts2dur(0, Timespan::SYMBOL_WORD));

        // Positive with spaced output using abbreviated symbols
        $pos = Timespan::ts2dur(65, Timespan::SYMBOL_ABBREVIATED, [], true);
        $this->assertSame('1 min 5 secs', $pos);

        // Negative with compact spacing
        $neg = Timespan::ts2dur(-65, Timespan::SYMBOL_ABBREVIATED, [], false);
        $this->assertSame('- 1min 5secs', $neg);
    }

    /**
     * Validates unit filtering in `duration()` and custom `format()` placeholders.
     */
    public function testDurationUnitsFilterAndFormat(): void {
        // 1 day, 1 hour, 1 minute, 1 second
        $s = new Timespan(90061); // 1d 1h 1m 1s

        // Default string cast
        $this->assertSame('1day 1hr 1min 1sec', (string)$s);

        // Filter to only days and hours
        $this->assertSame('1day 1hr', $s->duration('dh'));

        // Custom printf-style formatting across selected units
        $fmt = $s->format('%r%dd %hh %im %ss', 'dhis');
        $this->assertSame('1d 1h 1m 1s', $fmt);
    }

    /**
     * Confirms arithmetic adjustments and application to a `Timestamp` base.
     */
    public function testAdjustAddAndSubtractAndApply2Timestamp(): void {
        $s = new Timespan(120);

        // Add 30 seconds via duration string
        $s->adjust('30secs');
        $this->assertSame(150, $s->getSeconds());

        // Subtract using numeric string
        $s->adjustSubtract('45'); // numeric string
        $this->assertSame(105, $s->getSeconds());

        // Applying the timespan to a base timestamp should offset seconds accordingly
        $base = new Timestamp(1_700_000_000);
        $ts = $s->apply2Timestamp($base);
        $this->assertSame(1_700_000_105, $ts->getSeconds());
    }
}
