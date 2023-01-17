<?php

namespace Square\Vermillion\Formats;

use Square\Vermillion\ApiVersion;

interface VersionComparator
{
    /**
     * Returns -1 if $version1 is lesser than $version2
     * Returns 0 if $version1 is equal to $version2
     * Returns 1 if $version1 is greater than $version2
     *
     * @param ApiVersion $version1
     * @param ApiVersion $version2
     * @return int
     */
    public function compare(ApiVersion $version1, ApiVersion $version2): int;
}