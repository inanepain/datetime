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

/**
 * PHPUnit tests for the `Inane\\Datetime` timescale utilities.
 *
 * Verifies helper behaviour on the `Timescale` enum as well as interactions
 * with `Timestamp` values produced by those helpers.
 */

use Inane\Datetime\{
    Timescale,
    Timestamp};
use PHPUnit\Framework\TestCase;

/**
 * Class TimescaleTest
 *
 * Ensures that:
 * - `Timescale::unit()` returns the expected human-readable strings.
 * - `Timescale::timestamp()` returns either a `Timestamp` object or a raw
 *   integer depending on the flag provided, and that the values are sensible
 *   for the corresponding scale.
 * - `Timescale::tryFromTimestamp()` correctly infers the scale from the length
 *   and composition of a numeric timestamp.
 */
final class TimescaleTest extends TestCase {
    /**
     * Verifies the helper methods for units and timestamp generation.
     *
     * Checks that seconds produce a `Timestamp` close to `time()` when
     * requesting an object, and that milliseconds produce a large integer when
     * requesting a raw value.
     */
    public function testUnitAndTimestampHelpers(): void {
        // SECOND scale: expect plural unit name and a Timestamp object value
        $sec = Timescale::SECOND;
        $this->assertSame('seconds', $sec->unit());
        $t = $sec->timestamp(true);
        $this->assertInstanceOf(Timestamp::class, $t);
        $this->assertLessThanOrEqual(2, abs(time() - $t->getSeconds()));

        // MILLISECOND scale: expect a raw integer with millisecond precision
        $ms = Timescale::MILLISECOND;
        $v = $ms->timestamp(false);
        $this->assertIsInt($v);
        $this->assertGreaterThan(1_000, ($v % 10_000), "Assert the 1_000 < " . ($v % 10_000)); // not a small seconds value
    }

    /**
     * Ensures `tryFromTimestamp` infers the correct scale from the size
     * of the provided numeric value, including edge cases with trailing zeros.
     */
    public function testTryFromTimestampInfersScale(): void {
        $this->assertSame(Timescale::SECOND, Timescale::tryFromTimestamp(1_700_000_000));
        $this->assertSame(Timescale::MILLISECOND, Timescale::tryFromTimestamp(1_700_000_000_123));
        $this->assertSame(Timescale::MICROSECOND, Timescale::tryFromTimestamp(1_700_000_000_000_123));

        // Exact 13/16 with trailing zeros maps back to lower scales
        $this->assertSame(Timescale::SECOND, Timescale::tryFromTimestamp(1_700_000_000_000));          // 10 digits + 000
        $this->assertSame(Timescale::MILLISECOND, Timescale::tryFromTimestamp(1_700_000_000_123_000)); // 13 digits + 000
    }
}
