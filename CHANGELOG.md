Version: $Id$ ($Date$)

# History

## 0.4.0 (2025 Sep 29)

- new: `Timestamp` now stores the time as a microsecond timestamp

- new: `Timestamp` has new **readonly** properties for: **seconds**,
  **milliseconds**, **microseconds**

- new: `Timescale` enum for timestamp precision: **seconds**,
  **milliseconds**, **microseconds**

- update: `Timespan` `ts2dur` & `getDuration` new parameter `$spaced` to
  add a space between **number** & **unit**

- new: `TimeTrait` common methods for time classes

- update: `Timespan` updated the months calculation to be more accurate
  (average 30.44 days)

- new: `Timespan::format` added param `$units` to specify unites in
  duration

- new: use `FuzzyTimeTrait` with methods 'static::fussyClock' with input
  and `getFuzzyTime` with current `Timestamp`

- minor bugfixes and improvements to code and documentation

## 0.3.1 (2022 Nov 09)

- new: `Timestamp::now:int` Get current unix time

- doc: Improved php doc comments

## 0.3.0 (2022 Oct 14)

- new: `TimeWrapper::absoluteCopy` Get a copy with an absolute value

- new: `Timestamp::createFromFormat` Parses a time string according to a
  specified format

- update: Improvements to documentation

## 0.2.0 (2022 Sep 22)

- new: `TimeWrapper` interface for time unit classes

- new: `Timestamp` wrapper around timestamps

- new: `abs` absolute value function added to both classes

- update: `Timespan`: get durations with specified units only

- update: `Timespan`: add month unit

- update: `Timespan`: **symbolType** renamed to **symbolFormat**

- update: `Timespan`: moved moment functions to timestamp

- update: `Timespan`: handles historic/negative values

- update: `Timespan`: format now returns duration

## 0.1.0 (2022 Aug 13)

- initial
