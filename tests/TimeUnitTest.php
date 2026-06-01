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

use Inane\Datetime\TimeUnit;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for `Inane\Datetime\TimeUnit` enum.
 *
 * Verifies that each time unit maps to its expected number of seconds via
 * the `toSeconds` method.
 *
 * @covers \Inane\Datetime\TimeUnit
 */
final class TimeUnitTest extends TestCase {
    /**
     * Ensures each enum case returns the correct seconds multiplier.
     *
     * @return void
     */
    public function testToSecondsMapping(): void {
        // Base units and common calendar approximations (30-day month, 365-day year)
        $this->assertSame(1.0, TimeUnit::SECOND->toSeconds());
        $this->assertSame(60.0, TimeUnit::MINUTE->toSeconds());
        $this->assertSame(3600.0, TimeUnit::HOUR->toSeconds());
        $this->assertSame(86400.0, TimeUnit::DAY->toSeconds());
        $this->assertSame(604800.0, TimeUnit::WEEK->toSeconds());
        $this->assertSame(2592000.0, TimeUnit::MONTH->toSeconds());
        $this->assertSame(31536000.0, TimeUnit::YEAR->toSeconds());
    }
}
