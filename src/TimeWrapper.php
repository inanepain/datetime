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
 * Used by unit type classes usable in formatting and calculations.
 *
 * @package Inane\Datetime
 *
 * @version 0.1.0
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
}
