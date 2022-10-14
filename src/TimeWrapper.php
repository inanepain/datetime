<?php

/**
 * Inane: Datetime
 *
 * Inane Datetime Library
 *
 * PHP version 8.1
 *
 * @author Philip Michael Raab<peep@inane.co.za>
 * @package Inane\Datetime
 * @category datetime
 *
 * @license UNLICENSE
 * @license https://github.com/inanepain/datetime/raw/develop/UNLICENSE UNLICENSE
 *
 * @version $Id$
 * $Date$
 */

declare(strict_types=1);

namespace Inane\Datetime;

/**
 * Interface: TimeWrapper
 *
 * Used by time unit type classes ensuring they can be formatted and offer the raw value in seconds.
 *
 * @package Inane\Datetime
 *
 * @version 0.2.0
 */
interface TimeWrapper {
    /**
     * Returns a formatted string representation of the value
     *
     * @param string $format pattern
     *
     * @return string formatted string
     */
    public function format(string $format = ''): string;

    /**
     * Get as seconds
     *
     * @return int seconds
     */
    public function getSeconds(): int;

    /**
     * Get a copy with an absolute value
     *
     * @since 0.2.0
     *
     * @return \Inane\Datetime\TimeWrapper An absolute copy
     */
    public function absoluteCopy(): TimeWrapper;
}
