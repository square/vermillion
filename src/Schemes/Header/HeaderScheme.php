<?php declare(strict_types=1);

namespace Square\Vermillion\Schemes\Header;

use Illuminate\Http\Request;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Routing\Router;
use Illuminate\Routing\RouteRegistrar;
use Square\Vermillion\ApiVersion;
use Square\Vermillion\Schemes\VersioningScheme;
use Square\Vermillion\VersioningManager;

/**
 * Class HeaderVersioningScheme
 *
 * @package Square\Vermillion\Schemes
 */
class HeaderScheme implements VersioningScheme
{
    private string $headerName;

    private bool $headerMustBePresent;

    /**
     * @param string $headerName
     */
    public function __construct(string $headerName = 'X-Api-Version', bool $requireHeaderPresent = true)
    {
        $this->headerName = $headerName;
        $this->headerMustBePresent = $requireHeaderPresent;
    }

    /**
     * Does nothing to routes.
     *
     * @param Router $router
     *
     * @return Router
     */
    public function router(Router $router): Router|RouteRegistrar
    {
        $middleware = null;
        if ($this->headerMustBePresent) {
            $middleware = RequireVersionHeader::class;
        }
        return $router->middleware($middleware);
    }

    /**
     * @inheritDoc
     */
    public function isVersioned(Request $request): bool
    {
        return $request->headers->has($this->headerName);
    }

    /**
     * @inheritDoc
     */
    public function extract(Request $request): string
    {
        return (string) $request->headers->get($this->headerName);
    }

    /**
     * @inheritDoc
     */
    public function onActivation(ApiVersion $version): void
    {
        // Nothing to do.
    }

    /**
     * @inheritDoc
     */
    public function routeMatched(RouteMatched $event): void
    {
        // Nothing to do.
    }

    public function boot(VersioningManager $versioningManager): void
    {
        // Nothing to do.
    }

    /**
     * @return string
     */
    public function getHeaderName(): string
    {
        return $this->headerName;
    }
}
