<?php declare(strict_types=1);

namespace Square\Vermillion\Formats\Numeric;

use Square\Vermillion\ApiVersion;
use Square\Vermillion\Exceptions\BadVersionFormatException;
use Square\Vermillion\Formats\Numeric\NumericVersion;
use Square\Vermillion\Formats\VersionComparator;
use Square\Vermillion\Formats\VersionNormalizer;

/**
 * Class Normalizer
 *
 * @package Square\Vermillion\Formats\Numeric
 */
class NumericNormalizer implements VersionNormalizer
{
    /**
     * @inheritDoc
     */
    public function normalize(string|ApiVersion $version): ApiVersion
    {
        if ($version instanceof NumericVersion) {
            return $version;
        }

        if (!is_numeric($version)) {
            throw new BadVersionFormatException(sprintf(
                'Expected numeric string or instance of %s. Got %s.',
                NumericVersion::class,
                get_debug_type($version),
            ));
        }

        if (!preg_match('/^[0-9]+$/', $version)) {
            throw new BadVersionFormatException(sprintf(
                'Expected whole, positive integer. Got %s.',
                $version,
            ));
        }

        return new NumericVersion($version, $this);
    }

    public function getComparator(): NumericComparator
    {
        return new NumericComparator();
    }
}
