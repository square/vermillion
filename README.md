# Vermillion 🍂

[![PHP](https://github.com/square/vermillion/actions/workflows/php.yaml/badge.svg)](https://github.com/square/vermillion/actions/workflows/php.yaml)

An API versioning toolkit for Laravel applications️

---
* [What you get](#what-you-get)
* [Installation](#installation)
* [Configuration](#configuration)
  * [Choose an API version format](#choose-an-api-version-format)
  * [Choose an API versioning scheme](#choose-an-api-versioning-scheme)
  * [Specify supported versions](#specify-supported-versions)
* [Basic Usage](#basic-usage)
  * [How to define versioned routes](#how-to-define-versioned-routes)
  * [Route URL generation](#route-url-generation)
  * [Versioning API responses](#versioning-api-responses)
  * [Header-based versioning &amp; MissingVersionException](#header-based-versioning--missingversionexception)
* [Advanced Usage](#advanced-usage)
  * [Using your own custom versioning scheme](#using-your-own-custom-versioning-scheme)
  * [Using your own custom version format](#using-your-own-custom-version-format)
  * [Version anything using VersionedSet](#version-anything-using-versionedset)
  * [PHPStan](#phpstan)
---


### What you get

- An `ApiVersion` service you can use to detect requested API version & to perform comparison between versions.
- You can have as many as API versions you need (10 versions? 1,000? No judgement here.) without exploding your app's route collection. Keep the router's time to match versioned routes constant, and your developers sane: no more route-name pollution–it's `user.list`, not `users.list.v1`, `users.list.v3`, `users.list.v21`, etc.
- A scalable way to version your API responses. Take your existing `JsonResource`s and make them support multiple API versions by employing reverse migrations.
- A declarative utility API that you can use for *anything*. Just specify logical variations that apply to a range of API versions, and programmatically resolve the correct variant whenever you need it.
- An extensible versioning scheme system to define exactly how you wish clients to request an API version. Has built-in support for URI prefixes (e.g. `/v2/...`) or HTTP headers (e.g. `X-Api-Version: ...`), and you can easily build your own.
- An extensible versioning format system to define exactly what API versions you understand. Has built-in support for numeric versions (e.g. `v2`) and date versions (e.g. `2022-11-01`), and its also dang easy to roll your own.

### Installation

```bash
composer install square/vermillion
```

## Configuration

First, ensure that `Square\Vermillion\VersioningServiceProvider` is registered in your app. Sometimes it's automatic (via package discovery), sometimes it's not, so please double-check your app configuration.

Run this to generate a copy of the versioning config:

```
php artisan vendor:publish
```

### Choose an API version format

There are many ways versions are notated e.g. SemVer. There is built-in support for two formats you can choose from:

* `major` - Stands for _"major versions only"_ e.g. `2`
* `date` - Versions are in date format e.g. `2020-02-24`

You can also roll your own. See _"Use your own versioning format"_ under _Advanced Usage_.

### Choose an API versioning scheme

You can pick a way clients can specify API versions:

* `url_prefix` - API version will be specified in URLs e.g. `/api/v2/hello`
* `header` - API version will be specified in the request header e.g. `X-Api-Version: 2020-02-01`
* 
You can also roll your own. See _"Use your own versioning scheme"_ under _Advanced Usage_.

### Specify supported versions

You can configure `min`, `latest`, and `max` versions independently in the config file:

  1. **Minimum version** (e.g. `'min' => '1'`) - The oldest version your API currently supports. Any versioned routes requested with a version lesser than the `min` version will automatically 404.
  2. **Latest version** (e.g. `'latest' => '2'`) - The latest stable version your API supports. This will be the version used when nothing was not explicitly specified or when none can be inferred e.g. when generating URLs when not in a versioned route context, in async jobs, etc.
  3. **Maximum version** (e.g. `'max' => '3'`) - The maximum version your API supports. Anything that one would consider alpha, beta, or RC are better suited in versions that is greater `latest`. This the max API version clients can ask for i.e. Any versioned routes requested with a greater version than the `max` version will automatically 404.

## Basic Usage

### How to define versioned routes

```php
# routes/api.php

/*
 * Desired URL format: /api/v3/hello-world, etc.
 */
Route::prefix('/api')->group(function ($router) {

   // Start a group of versioned routes.
    $router->versioned()->group(function ($router) {

        // Define the base route. The specified controller-action will be used from min version to right before next...

        Route::get('/users', [UserController::class, 'list'])
          ->name('users.list')
          ->apiVersion('2', [UserController::class, 'listWithImplicitDefaults']) // Another controller used for v2+
          ->apiVersion('5', [UsersController::class, 'listViaCursorPagination']); // ...and another controller for v5+.

        // This other endpoint is only available v3+
        Route::post('/insights', $router->versioning()->unsupported()) // Default to "not supported" response (404)
          ->name('insights')
          ->apiVersion('3', [StatsController::class, 'insights']); // ...then goes to working controller for v3+
    });
});

```

### Route URL generation

When using `url_prefix` as versioning scheme, the URL generator will be automatically configured to use the current active version.

```php
# During an /api/v3/insights request
route('users.list');  #=> /api/v3/users
```

If there is no active version e.g. code executed within an unversioned route, the latest version will be used (defined in `config('versioning.latest')`).

If you wish to generate a route for another API version, specify the `apiVersion` option:

```php
# Generate URL with specific version:
route('users.list', ['apiVersion' => '4']); #=> /api/v4/users
```

### `ApiVersion` service

You can specify `Square\Vermillion\ApiVersion` as a dependency and you will get a reference to the current active (or latest) API version object:

```php

class InfoController
{
    public function list(Request $request, ApiVersion $apiVersion)
    {
        /*
         * Decide business logic based on API version...
         */
        if ($apiVersion->gte('4')) {
            // ...
        }

        if ($apiVersion->lte('6')) {
            // ...
        }

        if ($apiVersion->eq('7')) {
            // ...
        }

    }
}
```

**NOTE: You need to type-hint the `Square\Vermillion\ApiVersion` abstract, NOT any of concrete implementations in `Square\Vermillion\Formats\*` namespace!**

### Versioning API responses

You can use the `Square\Vermillion\Traits\JsonResource\WithReverseMigrations` trait to support Stripe-like data versioning via "reverse migrations":

```php

class UserResource extends JsonResource
{
     use WithReverseMigrations;

     /**
      * This must return the API response body of the max version you support.
      */
     public function toLatestArray($request)
     {
        return [
            'display_name' => $this->resource->name,
            'full_name' => $this->resource->full_name,
            'age' => $this->resource->age,
            'friends' => $this->whenLoaded($this->resource->friends, fn() ...),
        ];
     }

     /**
      * Override this method to specify the "reverse-migrations" 
      * responsible for rolling back the latest API response body, 
      * one iteration at at ime.
      */
     protected static function reverseMigrations(VersionedSet $migrations)
     {
           $migrations
               ->for('5', self::addBackFirstAndLastName(...))
               ->for('3', self::friendsWasNotNullable(...))
     }

     protected static function addBackFirstAndLastName(array $data, UserResource $resource, $request)
     {
           unset($data['full_name']);
           return array_merge(
               $data,
               [
                   'first_name' => $resource->user->first_name,
                   'last_name' => $resource->user->last_name,
               ],
           );
     }

     protected static function friendsWasNotNullable(array $data, UserResource $resource, $request)
     {
        return array_merge(
              $data,
              [
                 'friends' => $resource->user->friends ?? [],
              ],
        );
     }
}
```

### Header-based versioning & `MissingVersionException`

By default, using the `header` scheme will require that requests have the header present when its for a versioned route.
This manifests as a `MissingVersionException` thrown, which you will want to render appropriately:

```php
// app/Exceptions/Handler.php

namespace App\Exceptions;

use Square\Vermillion\Exceptions\VersionMissingException;

class Handler extends ExceptionHandler
{
    ...
    
    public function register()
    {
        $this->renderable(function (VersionMissingException $e) {
            return response([
                // Whatever you see fit.
            ])->setStatusCode(400);
        });
    }
}


```

## Advanced Usage

### Using your own custom versioning scheme

If you need to determine the API version to use given an HTTP request, you will need to write your own scheme by implementing `Square\Vermillion\VersioningScheme` contract.

Specify your custom versioning scheme's FQCN in the `versioning` configuration:

```php
<?php

// config/versioning.php

return [
  'scheme' => App\Http\Versioning\MyCustomScheme::class,
];

```

### Using your own custom version format

1. You can implement your own versioning format e.g. SemVer by extending the `Square\Vermillion\ApiVersion` abstract. All that is required for your new `ApiVersion` type is to provide an integer representation of your version strings. This is used by the library to compute the ordinality between versions, which is what is all that is needed for everything to work.

2. You will need to implement a `Square\Vermillion\VersionNormalizer` class that is responsible for converting a version string to an instance of your custom `ApiVersion` sub-class. It MUST support your custom version strings as input, _as well as the custom `ApiVersion` sub-class_. It MUST throw `BadVersionFormatException` if it is provided an input that it cannot convert into a valid `ApiVersion` for according to your custom version format spec.

3. Specify the FQCN of your normalizer as `versioning.normalizer` config value e.g.

```php
<?php

// config/versioning.php

return [
  'normalizer' => App\Http\Versioning\MyCustomNormalizer::class,
];

```


### Version anything using `VersionedSet`

A powerful way to model conditional logic is through the [Strategy pattern](https://en.wikipedia.org/wiki/Strategy_pattern), especially if there are many possible branches. This is often the case when supporting many API versions. The `VersionedSet` utility class
is specifically designed to make API version-based decisions easy to implement & manage:

```php

// Create a new VersionedSet
$set = app(VersioningManager::class)->versionedSet();

$set->for('1', new LegacyDoer()); // The original variation.
$set->for('3', new DifferentDoer()); // We made things a little different starting v3 but not in a very BC way.
$set->for('5', new BetterDoer()); // We made things a lot better starting v5, but it again requires breaking BC.

$doer = $set->resolve('4'); // returns `new DifferentDoer()`.

$doer->execute();

// Resolves variation according to default or active version:
$doer = $set->resolve(); // returns `new BetterDoer()`.

// Throws exception for unsupported versions e.g. below min or above max:
$doer = $set->resolve('100') // throws UnknownVersionException because we don't support this version.
```
### PHPStan 

Add this line to your `phpstan.neon` file to fill static analysis gaps around `Route` methods, etc.

```yaml
includes:
  - ./vendor/square/vermillion/phpstan/extension.neon
```

