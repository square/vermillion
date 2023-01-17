<?php declare(strict_types=1);

namespace Square\Vermillion\Schemes\UrlPrefix;

use Illuminate\Contracts\Routing\UrlGenerator as UrlGeneratorContract;
use Illuminate\Http\Request;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Routing\Router;
use Illuminate\Routing\RouteRegistrar;
use Illuminate\Routing\UrlGenerator;
use RuntimeException;
use Square\Vermillion\ApiVersion;
use Square\Vermillion\Schemes\VersioningScheme;
use Square\Vermillion\VersioningManager;

/**
 * Class UrlPrefixScheme
 *
 * @package Square\Vermillion\Schemes
 */
class UrlPrefixScheme implements VersioningScheme
{
    /**
     * Routes will be prefixed like this: /v1, /v2020-02-28, etc.
     * @param Router $router
     *
     * @return Router
     */
    public function router(Router $router): Router|RouteRegistrar
    {
        return $router->prefix('/v{apiVersion}');
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    public function isVersioned(Request $request): bool
    {
        return $request->route() && $request->route()->hasParameter('apiVersion');
    }

    /**
     * @inheritDoc
     */
    public function extract(Request $request): string
    {
        $route = $request->route();
        // @phpstan-ignore-next-line
        return $route ? (string) $route->parameter('apiVersion', '') : '';
    }

    /**
     * @inheritDoc
     */
    public function onActivation(ApiVersion $version): void
    {
        $this->resolveUrlGenerator()->defaults([
            'apiVersion' => $version->toString(),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function routeMatched(RouteMatched $event): void
    {
        $event->route->forgetParameter('apiVersion');
    }

    /**
     * @param VersioningManager $versioningManager
     */
    public function boot(VersioningManager $versioningManager): void
    {
        $this->resolveUrlGenerator()->defaults([
            'apiVersion' => $versioningManager->latest()->toString(),
        ]);
    }

    /**
     * @return UrlGenerator
     */
    private function resolveUrlGenerator(): UrlGenerator
    {
        $urlGenerator = app(UrlGeneratorContract::class);
        if (!$urlGenerator instanceof UrlGenerator) {
            throw new RuntimeException(
                sprintf(
                    'URL prefix versioning scheme needs a URL generator service that extends from %s. Got %s',
                    UrlGenerator::class,
                    get_class($urlGenerator)
                )
            );
        }
        return $urlGenerator;
    }
}
