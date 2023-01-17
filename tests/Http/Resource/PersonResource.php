<?php

namespace Square\Vermillion\Tests\Http\Resource;

use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Resources\Json\JsonResource;
use Square\Vermillion\Traits\JsonResource\WithReverseMigrations;
use Square\Vermillion\VersionedSet;
use Square\Vermillion\VersioningManager;

class PersonResource extends JsonResource
{
    use WithReverseMigrations;

    /**
     * @param $request
     * @return array|void
     */
    public function toLatestArray($request)
    {
        $person = $this->resource;
        assert($person instanceof Person);
        return [
            'display_name' => $person->name,
            'age' => $person->age,
            'nickname' => $person->nickName,
            'hobbies' => $this->when(isset($person->hobbies), fn() => $person->hobbies),
        ];
    }

    /**
     * @param VersionedSet $set
     * @return void
     */
    protected static function reverseMigrations(VersionedSet $set)
    {
        $set->for('7', fn(array $v) => self::removeNickname($v))
            ->for('5', fn(array $v, PersonResource $res, $req) => self::showHobbiesEvenIfEmpty($v, $res))
            ->for('2', fn(array $v) => self::revertToName($v))
            ->for('1', fn(array $v, PersonResource $res, $req) => $res->useProtectedMethods($v));
    }


    private static function removeNickname($v)
    {
        unset($v['nickname']);
        return $v;
    }

    private static function showHobbiesEvenIfEmpty($v, self $resource)
    {
        $v['hobbies'] = $resource->hobbies ?? [];
        return $v;
    }

    private static function revertToName($v)
    {
        $v['name'] = $v['display_name'];
        unset($v['display_name']);
        return $v;
    }

    /**
     * @param array $v
     * @return array
     */
    protected function useProtectedMethods(array $v)
    {
        $v['always_true'] = $this->when(true, true);
        return $v;
    }
}