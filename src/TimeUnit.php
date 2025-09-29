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
 * _version_ $version
 */

declare(strict_types=1);

namespace Inane\Datetime;

use Inane\Stdlib\Enum\CoreEnumInterface;

use Inane\Stdlib\Enum\CoreEnumTrait;

/**
 * Enum TimeUnit
 *
 * Represents a unit of time as a string.
 * Implements the CoreEnumInterface to provide additional functionality.
 */
enum TimeUnit implements CoreEnumInterface {
    case SECOND;
    case MINUTE;
    case HOUR;
    case DAY;
    case WEEK;
    case MONTH;
    case YEAR;

    /**
     * This file is part of the inanepain datetime library.
     *
     * It includes the usage of the CoreEnumTrait, which provides
     * additional functionality or behavior to the TimeUnit enum.
     *
     * @uses CoreEnumTrait
     */
    use CoreEnumTrait;

    public function toSeconds(): float {
        return match ($this) {
            self::SECOND => 1,
            self::MINUTE => 60,
            self::HOUR => 3600,
            self::DAY => 86400,
            self::WEEK => 604800,
            self::MONTH => 2592000,
            self::YEAR => 31536000,
        };
    }
}
