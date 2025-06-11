<?php

/**
 * Inane: Datetime
 *
 * Inane Datetime Library
 *
 * $Id$
 * $Date$
 *
 * PHP version 8.4
 *
 * @author Philip Michael Raab<philip@cathedral.co.za>
 * @package inanepain\datetime
 * @category datetime
 *
 * @license UNLICENSE
 * @license https://unlicense.org/UNLICENSE UNLICENSE
 *
 * @version $version
 */

declare(strict_types=1);

namespace Inane\Datetime;

/**
 * Interface: TimeWrapper
 *
 * Used by time unit type classes ensuring they can be formatted and offer the raw value in seconds.
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
