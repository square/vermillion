<?php

namespace Square\Vermillion\PHPStan\MethodReflections;

use PHPStan\Reflection\Native\NativeParameterReflection;
use PHPStan\Reflection\ParameterReflection;
use PHPStan\Reflection\ParametersAcceptor;
use PHPStan\Reflection\PassedByReference;
use PHPStan\Type\Generic\TemplateTypeMap;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;

class RouteApiVersionParametersAcceptor implements ParametersAcceptor
{
    private ParameterReflection $actionParameter;
    private Type $routeType;

    public function __construct(ParameterReflection $actionParameter, Type $routeType)
    {
        $this->actionParameter = $actionParameter;
        $this->routeType = $routeType;
    }

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
        return [
            new NativeParameterReflection(
                'apiVersion',
                false,
                new StringType(),
                PassedByReference::createNo(),
                false,
                null,
            ),
            $this->actionParameter,
        ];
    }

    public function isVariadic(): bool
    {
        return false;
    }

    public function getReturnType(): Type
    {
        return $this->routeType;
    }
}