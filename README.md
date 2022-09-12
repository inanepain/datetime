# Readme: Datetime

> version: $Id$ ($Date$)

Things to help you bend space and time to your will. Or at least do some calculations with it.

**Contents:**

* Timespan
* Timestamp

## Install

`composer require inanepain/datetime`

## Timespan

A Timespan essentially is a number of seconds that can be expressed in a few different ways. They can also be added or subtracted from NOW to get a future and past date and time.

Time periods:

- timespan: seconds (5400)
- duration: string (2hours / 3hrs / 3h)
- DateInterval: DateInterval (class)

Date (in relation to NOW):

- timestamp: int (1756487296)
- formatted date: @see DateTimeInterface::format()
- DateTime(Immutable): DateTime(Immutable) (classes)

### Usage

```php
// Naturally values based on now will differ with every execution of the code.

$epoch = new \Inane\Datetime\Timespan(time());
echo("Time since epoch: $epoch\n"); // and it will be greater then when this was written: 52y 32w 1d 6h 13m 1s

$dur1 = '3 yrs 2w 2days 4 min';
$ts1 = \Inane\Datetime\Timespan::fromDuration($dur1);

echo($ts1->getTimespan());       // 96053496
echo("$ts1");                    // 3y 2w 2d 4m

echo($ts1->getTimestamp());      // 1756477381 (added timespan to now)
echo($ts1->getTimestamp(false)); // 1564370389 (subtracted timespan to now)
echo($ts1->format());            // 2025-08-29 16:23:01
echo($ts1->format('', false) . "\n");   // 2019-07-29 05:19:49

$ts2 = new \Inane\Datetime\Timespan(8600);
echo("\tSymbol Format:");
echo("Default:\t" . $ts2->getDuration()); // 2hrs 23mins 20secs
echo("Single:\t\t" . $ts2->getDuration(\Inane\Datetime\Timespan::SYMBOL_SINGLE)); // 2h 23m 20s
echo("Abbreviated:\t" . $ts2->getDuration(\Inane\Datetime\Timespan::SYMBOL_MEDIUM)); // 2hrs 23mins 20secs
echo("Word:\t\t" . $ts2->getDuration(\Inane\Datetime\Timespan::SYMBOL_LONG) . "\n"); // 2hours 23minutes 20seconds

$s1 = \Inane\Datetime\Timespan::dur2ts('1hr 30min');
echo("1hr 30min => $s1");

$d1 = \Inane\Datetime\Timespan::ts2dur(10800);
echo("10800 => $d1");
```
