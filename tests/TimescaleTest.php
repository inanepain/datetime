<?php
declare(strict_types=1);

namespace Inane\Datetime\Tests;

use Inane\Datetime\Timescale;
use Inane\Datetime\Timestamp;
use PHPUnit\Framework\TestCase;

final class TimescaleTest extends TestCase
{
    public function testUnitAndTimestampHelpers(): void
    {
        $sec = Timescale::SECOND;
        $this->assertSame('seconds', $sec->unit());
        $t = $sec->timestamp(true);
        $this->assertInstanceOf(Timestamp::class, $t);
        $this->assertLessThanOrEqual(2, abs(time() - $t->getSeconds()));

        $ms = Timescale::MILLISECOND;
        $v = $ms->timestamp(false);
        $this->assertIsInt($v);
        $this->assertGreaterThan(1_000, ($v % 10_000), "Assert the 1_000 < " . ($v % 10_000)); // not a small seconds value
    }

    public function testTryFromTimestampInfersScale(): void
    {
        $this->assertSame(Timescale::SECOND, Timescale::tryFromTimestamp(1_700_000_000));
        $this->assertSame(Timescale::MILLISECOND, Timescale::tryFromTimestamp(1_700_000_000_123));
        $this->assertSame(Timescale::MICROSECOND, Timescale::tryFromTimestamp(1_700_000_000_000_123));

        // Exact 13/16 with trailing zeros maps back to lower scales
        $this->assertSame(Timescale::SECOND, Timescale::tryFromTimestamp(1_700_000_000_000)); // 10 digits + 000
        $this->assertSame(Timescale::MILLISECOND, Timescale::tryFromTimestamp(1_700_000_000_123_000)); // 13 digits + 000
    }
}
