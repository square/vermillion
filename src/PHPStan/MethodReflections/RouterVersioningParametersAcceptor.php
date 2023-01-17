<?php

namespace Square\Vermillion\PHPStan\MethodReflections;

use PHPStan\Reflection\ParametersAcceptor;
use PHPStan\Type\Generic\TemplateTypeMap;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Square\Vermillion\Routing\RoutingHelper;

class RouterVersioningParametersAcceptor implements ParametersAcceptor
{
    public function getTemplateTypeMap(): TemplateTypeMap
    {
        return TemplateTypeMap::createEmpty();
    }

    public function getResolvedTemplateTypeMap(): TemplateTypeMap
    {
        return TemplateTypeMap::createEmpty();
    }

    public function getParameters(): array
    {
        return [];
    }

    public function isVariadic(): bool
    {
        return false;
    }

    public function getReturnType(): Type
    {
        return new ObjectType(RoutingHelper::class);
    }
}