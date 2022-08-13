# Readme: Datetime

> version: $Id$ ($Date$)

Things to help you bend space and time to your will. Or at least do some calculations with it.

**Contents:**

 - Timespan

## Install

`composer require inanepain/datetime`

## Usage

```php
// Timespan
$epoch = new \Inane\Datetime\Timespan(time());
echo "Time since epoch: $epoch\n"; // and it will be greater then when this was written: 52y 33w 6d 19h 9m 47s
```
