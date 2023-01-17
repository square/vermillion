<?php declare(strict_types=1);

namespace Square\Vermillion\Formats\Numeric;

use Square\Vermillion\ApiVersion;

/**
 * Versioning format where version numbers are straight-up integers. Think, "Major versions only."
 *
 * @package Square\Vermillion\Formats\Numeric
 */
class NumericVersion extends ApiVersion
{
    public function toInt(): int
    {
        return (int) $this->versionString;
    }
}
