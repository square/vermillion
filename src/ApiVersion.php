<?php declare(strict_types=1);

namespace Square\Vermillion;

use Square\Vermillion\Formats\VersionNormalizer;

/**
 * Class ApiVersion
 *
 * @package Square\Vermillion
 */
abstract class ApiVersion
{
    protected string $versionString;

    protected VersionNormalizer $normalizer;

    /**
     * ApiVersion constructor.
     *
     * @param string            $versionString
     * @param VersionNormalizer $normalizer
     */
    public function __construct(string $versionString, VersionNormalizer $normalizer)
    {
        $this->versionString = $versionString;
        $this->normalizer = $normalizer;
    }

    /**
     * Comparator function to rank this version object with another.
     *
     * @return int
     * @phpstan-param static $version
     */
    public function compare(ApiVersion $version): int
    {
        return $this->normalizer->getComparator()->compare($this, $version);
    }

    public function gt(string|ApiVersion $version): bool
    {
        return $this->compare($this->normalizer->normalize($version)) === 1;
    }

    /**
     * @param string|self $version
     * @return bool
     */
    public function gte(string|ApiVersion $version): bool
    {
        return $this->compare($this->normalizer->normalize($version)) >= 0;
    }

    public function lt(string|ApiVersion $version): bool
    {
        return $this->compare($this->normalizer->normalize($version)) === -1;
    }

    public function lte(string|ApiVersion $version): bool
    {
        return $this->compare($this->normalizer->normalize($version)) <= 0;
    }

    public function eq(string|ApiVersion $version): bool
    {
        return $this->compare($this->normalizer->normalize($version)) === 0;
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        return $this->versionString;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->toString();
    }
}
