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

use function intval;
use function str_pad;
use function strlen;
use function strval;

use const STR_PAD_RIGHT;

/**
 * TimeTrait
 *
 * @version 0.1.0
 */
trait TimeTrait {
    /**
     * Represents the number of seconds as an integer.
     *
     * @since 0.4.0
     *
     * @var int $seconds The number of seconds.
     */
    public private(set) int $seconds {
        get => intval($this->timestamp);
        set(null|int|float $value) {
            $this->timestamp = $value;
        }
    }

    /**
     * Represents the number of milliseconds.
     *
     * @since 0.4.0
     *
     * @var float $milliseconds The number of milliseconds as an integer.
     */
    public private(set) float $milliseconds {
        get => (int)($this->seconds * 1000);
        set(float $value) {
            $this->seconds = (int)($value / 1000);
        }
    }

    /**
     * The number of microseconds.
     *
     * @since 0.4.0
     *
     * This property represents the microsecond component of a timestamp.
     *
     * @var int $microseconds The number of microseconds as an integer.
     */
    public private(set) float $microseconds {
        get => (int)($this->milliseconds * 1000);
        set(float $value) {
            $this->milliseconds = $value / 1000;
        }
    }
}
