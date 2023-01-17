<?php declare(strict_types=1);

namespace Square\Vermillion\Routing;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Container\Container;
use Square\Vermillion\ApiVersion;
use Square\Vermillion\Exceptions\NoMatchFoundException;
use Square\Vermillion\Exceptions\VersioningException;
use Square\Vermillion\VersionedSet;
use Square\Vermillion\VersioningManager;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Routing\Events\RouteMatched;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Perform API version-related operations based on route events e.g. detecting current version & dispatching requests
 * to versioned controllers.
 *
 * @package Square\Vermillion\Routing
 */
class ApiVersioningSubscriber
{
    private Container $container;

    private bool $debugMode;

    /**
     * ApiVersioningSubscriber constructor.
     *
     * @param Container $container
     * @param bool      $debugMode
     */
    public function __construct(
        Container $container,
        bool $debugMode
    ) {
        $this->container = $container;
        $this->debugMode = $debugMode;
    }

    /**
     * Set active version in ApiVersion object.
     *
     * @param RouteMatched $event
     */
    public function setActiveVersion(RouteMatched $event): void
    {
        $versioningManager = $this->resolveVersioningManager();
        if (!$versioningManager->getScheme()->isVersioned($event->request)) {
            // Ensure active version is latest version if current route is not versioned.
            $versioningManager->setActive($versioningManager->latest());
            return;
        }

        $version = $versioningManager->getScheme()->extract($event->request);

        try {
            $apiVersion = $versioningManager->getNormalizer()->normalize($version);
            $versioningManager->setActive($apiVersion);
            $versioningManager->getScheme()->routeMatched($event);
        } catch (VersioningException $exception) {
            if ($this->debugMode) {
                throw new NotFoundHttpException(
                    'API versioning error: ' . $exception->getMessage(),
                    $exception
                );
            }
            // For production, it should 404.
            throw new NotFoundHttpException;
        }
    }

    /**
     * Tries to override the controller-action to use for the route, depending on the current version.
     * version.
     *
     * @param RouteMatched $event
     */
    public function overrideRouteAction(RouteMatched $event): void
    {
        $route = $event->route;

        $versionedSet = $route->defaults['api_version:versioned_set'] ?? null;
        if (!$versionedSet instanceof VersionedSet) {
            return;
        }

        $apiVersion = $this->resolveVersioningManager()->getActive();

        try {
            $value = $versionedSet->resolve($apiVersion);
        } catch (NoMatchFoundException $e) {
            return;
        }

        if (!$value) {
            return;
        }

        if (isset($value['uses'])) {
            $route->action['uses'] = $value['uses'];
        }
        if (isset($value['controller'])) {
            $route->action['controller'] = $value['controller'];
        }
    }

    /**
     * Register listeners to RouteMatched event to derive proper API version and configure routes when needed.
     *
     * @param Dispatcher $dispatcher
     */
    public function subscribe(Dispatcher $dispatcher): void
    {
        $dispatcher->listen(RouteMatched::class, [$this, 'setActiveVersion']);
        $dispatcher->listen(RouteMatched::class, [$this, 'overrideRouteAction']);
    }

    /**
     * @return VersioningManager
     * @throws BindingResolutionException
     */
    private function resolveVersioningManager(): VersioningManager
    {
        return $this->container->make(VersioningManager::class);
    }
}
