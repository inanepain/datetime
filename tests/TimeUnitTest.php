<?php
declare(strict_types=1);

namespace Inane\Datetime\Tests;

use Inane\Datetime\TimeUnit;
use PHPUnit\Framework\TestCase;

final class TimeUnitTest extends TestCase
{
    public function testToSecondsMapping(): void
    {
        $this->assertSame(1.0, TimeUnit::SECOND->toSeconds());
        $this->assertSame(60.0, TimeUnit::MINUTE->toSeconds());
        $this->assertSame(3600.0, TimeUnit::HOUR->toSeconds());
        $this->assertSame(86400.0, TimeUnit::DAY->toSeconds());
        $this->assertSame(604800.0, TimeUnit::WEEK->toSeconds());
        $this->assertSame(2592000.0, TimeUnit::MONTH->toSeconds());
        $this->assertSame(31536000.0, TimeUnit::YEAR->toSeconds());
    }
}
