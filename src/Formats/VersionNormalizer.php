<?php declare(strict_types=1);

namespace Square\Vermillion\Formats;

use Square\Vermillion\ApiVersion;

/**
 * Interface VersionNormalizer
 *
 * @package Square\Vermillion
 */
interface VersionNormalizer
{
    /**
     * Converts a version value into an instance of ApiVersion. If the value is already an instance of ApiVersion,
     * it should return it back.
     *
     * @param string|ApiVersion $version
     *
     * @return ApiVersion
     */
    public function normalize(string|ApiVersion $version): ApiVersion;

    /**
     * @return VersionComparator
     */
    public function getComparator(): VersionComparator;

}
