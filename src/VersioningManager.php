<?php

declare(strict_types=1);

namespace Square\Vermillion;

use Square\Vermillion\Exceptions\UnknownVersionException;
use Square\Vermillion\Formats\VersionNormalizer;
use Square\Vermillion\Schemes\VersioningScheme;

/**
 * Class ApiVersion
 *
 * @package App\Http
 *
 */
class VersioningManager
{
    private ApiVersion $min;

    private ApiVersion $latest;

    private ApiVersion $max;

    private VersionNormalizer $normalizer;

    private VersioningScheme $scheme;

    /**
     * Current active version in request context.
     */
    protected ApiVersion|null $active;


    /**
     * ApiVersionManager constructor.
     *
     * @param VersionNormalizer $normalizer
     * @param VersioningScheme  $scheme
     * @param string|ApiVersion        $active
     * @param string|ApiVersion        $min
     * @param string|ApiVersion        $latest
     * @param string|ApiVersion|null   $max
     */
    public function __construct(
        VersionNormalizer $normalizer,
        VersioningScheme $scheme,
        string|ApiVersion $active,
        string|ApiVersion $min,
        string|ApiVersion $latest,
        string|ApiVersion|null $max = null,
    ) {
        $this->normalizer = $normalizer;
        $this->active = $this->normalizer->normalize($active);
        $this->min = $this->normalizer->normalize($min);
        $this->latest = $this->normalizer->normalize($latest);
        $this->max = $this->normalizer->normalize($max ?? $this->latest);
        $this->scheme = $scheme;
    }

    /**
     * @return ApiVersion
     */
    public function latest(): ApiVersion
    {
        return $this->latest;
    }

    /**
     * @return ApiVersion
     */
    public function getActive(): ApiVersion
    {
        return $this->active ?? $this->latest();
    }

    /**
     * Set the active version.
     *
     * @param ApiVersion|string $version
     * @throws UnknownVersionException
     */
    public function setActive(string|ApiVersion $version): void
    {
        $version = $this->normalizer->normalize($version);

        if ($this->min->gt($version)) {
            throw new UnknownVersionException(sprintf(
                'Setting active version to %s failed: Minimum API version is %s.',
                $version,
                $this->min
            ));
        }

        if ($this->max->lt($version)) {
            throw new UnknownVersionException(sprintf(
                'Setting active version to %s failed: Maximum API version is %s.',
                $version,
                $this->max
            ));
        }

        $this->active = $version;
        $this->scheme->onActivation($version);
    }

    /**
     * @return ApiVersion
     */
    public function max(): ApiVersion
    {
        return $this->max;
    }

    /**
     * @return ApiVersion
     */
    public function min(): ApiVersion
    {
        return $this->min;
    }

    /**
     * @return VersionNormalizer
     */
    public function getNormalizer(): VersionNormalizer
    {
        return $this->normalizer;
    }

    public function getScheme(): VersioningScheme
    {
        return $this->scheme;
    }

    /**
     * @return VersionedSet
     */
    public function versionedSet(): VersionedSet
    {
        return new VersionedSet($this);
    }
}
