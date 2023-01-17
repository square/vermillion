<?php

declare(strict_types=1);

namespace Square\Vermillion;

use Closure;
use Illuminate\Contracts\Routing\Registrar;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Events\Dispatcher;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use ReflectionClass;
use Square\Vermillion\Formats\Date\DateNormalizer as DateVersionNormalizer;
use Square\Vermillion\Formats\Numeric\NumericNormalizer as NumericVersionNormalizer;
use Square\Vermillion\Formats\VersionNormalizer;
use Square\Vermillion\Routing\ApiVersioningSubscriber;
use Square\Vermillion\Routing\RoutingHelper;
use Square\Vermillion\Schemes\Header\HeaderScheme;
use Square\Vermillion\Schemes\UrlPrefix\UrlPrefixScheme;
use Square\Vermillion\Schemes\VersioningScheme;

/**
 * Boostrap API versioning package
 *
 * @package Square\Vermillion
 */
class VersioningServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/versioning.php' => config_path('versioning.php'),
        ]);
        $scheme = $this->app->make(VersioningScheme::class);
        $scheme->boot($this->app->make(VersioningManager::class));
    }

    public function register()
    {
        $this->configureCoreVersioningServices();
        $this->registerRoutingExtensions();
    }

    private function configureCoreVersioningServices(): void
    {
        $this->app->singleton(VersionNormalizer::class, function () {
            $format = (string) config('versioning.format', 'major');
            return match($format) {
                'major', 'numeric', '' => new NumericVersionNormalizer(),
                'date' => new DateVersionNormalizer(),
                default => $this->app->make($format),
            };
        });

        $this->app->singleton(VersioningScheme::class, function () {
            $scheme = (string) config('versioning.scheme', 'url_prefix');
            return match ($scheme) {
                'url_prefix' => new UrlPrefixScheme(),
                'header' => new HeaderScheme(
                    (string) config('versioning.schemes.header.name'),
                    config('versioning.schemes.header.require_header') ?? true,
                ),
                default => $this->app->make($scheme),
            };
        });

        $this->app->singleton(VersioningManager::class, function () {
            $normalizer = $this->app->make(VersionNormalizer::class);
            $scheme = $this->app->make(VersioningScheme::class);
            return new VersioningManager(
                $normalizer,
                $scheme,
                config('versioning.latest', '1'),
                config('versioning.min', '1'),
                config('versioning.latest', '1'),
                config('versioning.max', '2')
            );
        });

        $this->app->singleton(ApiVersioningSubscriber::class, function () {
            return new ApiVersioningSubscriber(
                $this->app,
                (bool) config('app.debug')
            );
        });

        /** @var Dispatcher $dispatcher */
        $dispatcher = $this->app->make('events');
        $dispatcher->subscribe(ApiVersioningSubscriber::class);
    }

    private function registerRoutingExtensions(): void
    {
        $this->app->singleton(RoutingHelper::class);

        $app = $this->app;
        // Helper method that can be used in route files to grab
        Router::macro('versioning', function () use ($app) {
            return $app->make(RoutingHelper::class);
        });

        Router::macro('versioned', function () use ($app) {
            // @phpstan-ignore-next-line
            return $app->make(VersioningScheme::class)->router($this);
        });

        Router::macro('unsupported', function () {
            // @phpstan-ignore-next-line
            return $this->versioning()->unsupported();
        });

        // As of Laravel 5.4, there is no robust way of creating route objects outside the "regular" route registration
        // which we can't leverage for proper versioning. This should be revisited to see if it's possible
        // in newer Laravel versions.
        $classRef = new ReflectionClass(Router::class);
        $createRouteRef = $classRef->getMethod('createRoute');
        $createRouteRef->setAccessible(true);

        $router = $this->app->make(Router::class);
        $router->bind('apiVersion', function ($version) use ($app) {
            return $app->make(VersioningManager::class)
                ->getNormalizer()
                ->normalize($version);
        });

        $this->app->bind(ApiVersion::class, function () {
            return $this->app->make(VersioningManager::class)->getActive();
        });

        Route::macro('apiVersion', function ($min, $action) use ($createRouteRef, $app): Route {
            // @phpstan-ignore-next-line
            assert($this instanceof Route);
            $manager = $app->make(VersioningManager::class);

            $set = $this->defaults['api_version:versioned_set'] = $this->defaults['api_version:versioned_set'] ??
                new VersionedSet($manager);

            $definition = [];
            if (!$action instanceof Closure) {
                $dummyRoute = $createRouteRef->invoke($this->router, $this->methods(), $this->uri(), $action);
                if (isset($dummyRoute->action['controller'])) {
                    $definition['controller'] = $dummyRoute->action['controller'];
                }
                if (isset($dummyRoute->action['uses'])) {
                    $definition['uses'] = $dummyRoute->action['uses'];
                }
            } else {
                $definition['uses'] = $action;
            }

            $set->for($min, $definition);
            return $this;
        });
    }
}
