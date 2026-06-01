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
 * Timestamp test suite
 *
 * Verifies construction, formatting, mutation and comparison behaviour of
 * the `Inane\\Datetime\\Timestamp` value object, including interoperability
 * with `strtotime()`-style strings and `DateTime`/`DateTimeImmutable`.
 */
final class TimestampTest extends TestCase {
    /**
     * Ensures `Timestamp::createFromFormat()` correctly parses a formatted date
     * string and yields the expected Unix epoch seconds.
     *
     * @return void
     */
    public function testCreateFromFormatParsesDate(): void {
        $dt = '05 May 2022 07:08:09';
        $ts = Timestamp::createFromFormat('d F Y H:i:s', $dt);

        $this->assertInstanceOf(Timestamp::class, $ts);
        $this->assertSame(strtotime($dt), $ts->getSeconds());
    }

    /**
     * Confirms `Timestamp::createFromString()` can handle both absolute
     * (e.g. RFC-/SQL-like) and relative (e.g. 'now') time strings.
     * Relative strings are validated within a small delta to account for the
     * passage of time during test execution.
     *
     * @return void
     */
    public function testCreateFromStringParsesRelativeAndAbsolute(): void {
        $abs = '2024-01-02 03:04:05 UTC';
        $tsAbs = Timestamp::createFromString($abs);
        $this->assertInstanceOf(Timestamp::class, $tsAbs);
        $this->assertSame(strtotime($abs), $tsAbs->getSeconds());

        $rel = 'now';
        $tsRel = Timestamp::createFromString($rel);
        $this->assertInstanceOf(Timestamp::class, $tsRel);
        // Cannot assert exact equality (time passes); ensure it is within a sane delta
        $this->assertLessThanOrEqual(2, abs(time() - $tsRel->getSeconds()));
    }

    /**
     * Verifies primitive getters (seconds/milliseconds/microseconds) and the
     * default/empty formatting behaviour.
     *
     * @return void
     */
    public function testGettersAndFormat(): void {
        $known = 1_600_000_000; // 2020-09-13 12:26:40 UTC
        $t = new Timestamp($known);

        $this->assertSame($known, $t->getSeconds());
        $this->assertSame($known * 1000, $t->getMilliseconds());
        $this->assertSame($known * 1000_000, $t->getMicroseconds());

        // Default format and empty format should be Y-m-d H:i:s
        $expected = gmdate('Y-m-d H:i:s', $known);
        $this->assertSame($expected, $t->format());
        $this->assertSame($expected, $t->format(''));
    }

    /**
     * Checks conversion to `DateTime` and `DateTimeImmutable` retains the
     * exact Unix epoch timestamp.
     *
     * @return void
     */
    public function testGetDateTimeAndImmutable(): void {
        $known = 1_650_000_000;
        $t = new Timestamp($known);

        $dt = $t->getDateTime();
        $this->assertInstanceOf(\DateTime::class, $dt);
        $this->assertSame($known, (int)$dt->format('U'));

        $dti = $t->getDateTime(true);
        $this->assertInstanceOf(\DateTimeImmutable::class, $dti);
        $this->assertSame($known, (int)$dti->format('U'));
    }

    /**
     * Validates in-place adjustment via integer seconds and `Timespan`, and
     * that `diff()` returns the signed difference in seconds wrapped in a
     * `Timespan` (other - this).
     *
     * @return void
     */
    public function testAdjustAndDiff(): void {
        $base = new Timestamp(1_700_000_000);
        $span = new Timespan(3600); // 1 hour

        $base->adjust(60); // +1 minute
        $this->assertSame(1_700_000_060, $base->getSeconds());

        $base->adjust($span); // +1 hour
        $this->assertSame(1_700_003_660, $base->getSeconds());

        // diff(other) returns other - this
        $other = new Timestamp(1_700_004_660);
        $diff = $base->diff($other);
        $this->assertSame(1000, $diff->getSeconds());
    }

    /**
     * Ensures `modify()` accepts `strtotime()`-compatible modifiers and
     * updates the internal value; invalid modifiers return `false`.
     *
     * @return void
     */
    public function testModifySupportsStrtotimeSyntax(): void {
        $t = new Timestamp(1_700_000_000);
        $r = $t->modify('+1 day');
        $this->assertInstanceOf(Timestamp::class, $r);
        $this->assertSame(strtotime('+1 day', 1_700_000_000), $t->getSeconds());

        $fail = $t->modify('not-a-valid-modifier');
        $this->assertFalse($fail);
    }
}
