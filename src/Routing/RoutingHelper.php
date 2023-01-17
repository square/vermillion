<?php declare(strict_types=1);

namespace Square\Vermillion\Routing;

use Closure;
use Illuminate\Http\Response;
use Square\Vermillion\ApiVersion;
use Square\Vermillion\VersioningManager;

/**
 * Registered to Router::versioning() macro.
 *
 * Usable in route files e.g. $router->versioning()->latest(), $router->versioning()->max(), etc.
 *
 * @package Square\Vermillion\Routing
 */
class RoutingHelper
{
    private VersioningManager $versioningManager;

    /**
     * RoutingHelper constructor.
     *
     * @param VersioningManager $versioningManager
     */
    public function __construct(VersioningManager $versioningManager)
    {
        $this->versioningManager = $versioningManager;
    }

    /**
     * @return ApiVersion
     */
    public function min(): ApiVersion
    {
        return $this->versioningManager->min();
    }

    /**
     * @return ApiVersion
     */
    public function max(): ApiVersion
    {
        return $this->versioningManager->max();
    }

    /**
     * @return ApiVersion
     */
    public function latest(): ApiVersion
    {
        return $this->versioningManager->latest();
    }

    /**
     * @return Closure
     */
    public function unsupported(): callable
    {
        return function () {
            return new Response('', Response::HTTP_NOT_FOUND);
        };
    }

    /**
     * @return Closure
     */
    public function methodNotAllowed(): callable
    {
        return function () {
            return new Response('', Response::HTTP_METHOD_NOT_ALLOWED);
        };
    }
}
