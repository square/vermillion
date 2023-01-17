<?php declare(strict_types=1);

namespace Square\Vermillion\Schemes;

use Illuminate\Http\Request;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Routing\Router;
use Illuminate\Routing\RouteRegistrar;
use Square\Vermillion\ApiVersion;
use Square\Vermillion\VersioningManager;

/**
 * Interface VersioningScheme
 *
 * @package Square\Vermillion\Schemes
 */
interface VersioningScheme
{
    public function boot(VersioningManager $versioningManager): void;

    /**
     * @param Router $router
     *
     * @return Router
     */
    public function router(Router $router): Router|RouteRegistrar;

    /**
     * @param Request $request
     *
     * @return bool
     */
    public function isVersioned(Request $request): bool;

    /**
     * @param Request $request
     *
     * @return string
     */
    public function extract(Request $request): string;

    /**
     * @param ApiVersion $version
     */
    public function onActivation(ApiVersion $version): void;

    /**
     * @param RouteMatched $event
     */
    public function routeMatched(RouteMatched $event): void;
}
