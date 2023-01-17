<?php

namespace Square\Vermillion\Traits\JsonResource;

use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use RuntimeException;
use Square\Vermillion\VersionedSet;
use Square\Vermillion\VersioningManager;

trait WithReverseMigrations
{
    protected static ?VersionedSet $versionedSet;

    /**
     * @param $request
     * @return array|null
     * @throws BindingResolutionException
     */
    public function toArray($request)
    {
        if (!method_exists($this, 'toLatestArray')) {
            throw new RuntimeException(sprintf('%s must implement method toLatestArray($request)', static::class));
        }

        $array = $this->toLatestArray($request);

        /** @var VersioningManager $versioningManager */
        $versioningManager = Container::getInstance()->make(VersioningManager::class);

        $migrations = static::versionedSet()->resolveReversePath($versioningManager->getActive());

        if (empty($migrations)) {
            return $array;
        }

        return array_reduce(
            $migrations,
            fn($accum, $migration) => $migration($accum, $this, $request),
            $array,
        );
    }

    /**
     * @param VersionedSet $migrations
     * @return void
     */
    protected static function reverseMigrations(VersionedSet $migrations)
    {
        //
    }

    /**
     * @return VersionedSet
     * @throws BindingResolutionException
     */
    protected static function versionedSet(): VersionedSet
    {
        if (!isset(static::$versionedSet)) {
            static::$versionedSet = Container::getInstance()->make(VersioningManager::class)->versionedSet();
            static::reverseMigrations(self::$versionedSet);
        }

        return static::$versionedSet;
    }
}