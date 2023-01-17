<?php

declare(strict_types=1);

namespace Square\Vermillion;

class VersionedItem
{
    private ApiVersion $minVersion;

    private mixed $value;

    /**
     * @param ApiVersion $minVersion
     * @param mixed $value
     */
    public function __construct(ApiVersion $minVersion, mixed $value)
    {
        $this->minVersion = $minVersion;
        $this->value = $value;
    }

    /**
     * @param ApiVersion $apiVersion
     * @return bool
     */
    public function matches(ApiVersion $apiVersion): bool
    {
        return $this->minVersion->lte($apiVersion);
    }

    /**
     * @return ApiVersion
     */
    public function getMinVersion(): ApiVersion
    {
        return $this->minVersion;
    }

    /**
     * @return mixed
     */
    public function getValue(): mixed
    {
        return $this->value;
    }
}