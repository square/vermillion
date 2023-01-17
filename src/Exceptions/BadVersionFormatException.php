<?php declare(strict_types=1);

namespace Square\Vermillion\Exceptions;

use RuntimeException;

/**
 * Thrown by normalizers when they encounter a badly-formed version string.
 *
 * @package Square\Vermillion\Exception
 */
class BadVersionFormatException extends VersioningException
{

}
