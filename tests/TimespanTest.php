<?php
declare(strict_types=1);

namespace Inane\Datetime\Tests;

use Inane\Datetime\Timespan;
use Inane\Datetime\Timestamp;
use PHPUnit\Framework\TestCase;

final class TimespanTest extends TestCase
{
    public function testConstructAndGetters(): void
    {
        $s = new Timespan(3661); // 1h 1m 1s
        $this->assertSame(3661, $s->getSeconds());
        $this->assertSame('1hr 1min 1sec', $s->getDuration());
        $this->assertSame('1h 1m 1s', $s->getDuration(symbolFormat: Timespan::SYMBOL_CHAR, spaced: false));
    }

    public function testFromDurationAndDur2Ts(): void
    {
        $this->assertSame(90, Timespan::dur2ts('1min30secs'));
        $this->assertSame(-3600, Timespan::dur2ts('-1hr'));

        $s = Timespan::fromDuration('2hrs 5mins');
        $this->assertSame(2 * 3600 + 5 * 60, $s->getSeconds());
    }

    public function testTs2DurFormattingAndSpacing(): void
    {
        $this->assertSame('0secs', Timespan::ts2dur(0));
        $this->assertSame('0s', Timespan::ts2dur(0, Timespan::SYMBOL_CHAR));
        $this->assertSame('0seconds', Timespan::ts2dur(0, Timespan::SYMBOL_WORD));

        $pos = Timespan::ts2dur(65, Timespan::SYMBOL_ABBREVIATED, [], true);
        $this->assertSame('1 min 5 secs', $pos);

        $neg = Timespan::ts2dur(-65, Timespan::SYMBOL_ABBREVIATED, [], false);
        $this->assertSame('- 1min 5secs', $neg);
    }

    public function testDurationUnitsFilterAndFormat(): void
    {
        $s = new Timespan(90061); // 1d 1h 1m 1s
        $this->assertSame('1day 1hr 1min 1sec', (string)$s);
        $this->assertSame('1day 1hr', $s->duration('dh'));

        $fmt = $s->format('%r%dd %hh %im %ss', 'dhis');
        $this->assertSame('1d 1h 1m 1s', $fmt);
    }

    public function testAdjustAddAndSubtractAndApply2Timestamp(): void
    {
        $s = new Timespan(120);
        $s->adjust('30secs');
        $this->assertSame(150, $s->getSeconds());
        $s->adjustSubtract('45'); // numeric string
        $this->assertSame(105, $s->getSeconds());

        $base = new Timestamp(1_700_000_000);
        $ts = $s->apply2Timestamp($base);
        $this->assertSame(1_700_000_105, $ts->getSeconds());
    }
}
