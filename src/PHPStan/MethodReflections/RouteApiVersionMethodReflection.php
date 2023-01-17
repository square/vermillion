<?php

namespace Square\Vermillion\PHPStan\MethodReflections;

use Illuminate\Routing\Route;
use PHPStan\Reflection\ClassMemberReflection;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParameterReflection;
use PHPStan\TrinaryLogic;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Square\Vermillion\PHPStan\ClassMemberReflection as VersioningClassMemberReflection;

class RouteApiVersionMethodReflection implements MethodReflection
{
    private ClassReflection $routeClass;

    private VersioningClassMemberReflection $classMember;

    private ParameterReflection $actionParameter;

    private Type $routeType;

    /**
     * @param ClassReflection $routeClass
     * @param VersioningClassMemberReflection $classMember
     */
    public function __construct(
        ClassReflection $routeClass,
        VersioningClassMemberReflection $classMember,
        ParameterReflection $actionParameter,
    ) {
        $this->routeClass = $routeClass;
        $this->classMember = $classMember;
        $this->actionParameter = $actionParameter;
        $this->routeType = new ObjectType(Route::class);
    }

    public function getDeclaringClass(): ClassReflection
    {
        return $this->routeClass;
    }

    public function isStatic(): bool
    {
        return true;
    }

    public function isPrivate(): bool
    {
        return false;
    }

    public function isPublic(): bool
    {
        return true;
    }

    public function getDocComment(): ?string
    {
        return null;
    }

    public function getName(): string
    {
        return 'apiVersion';
    }

    public function getPrototype(): ClassMemberReflection
    {
        return $this->classMember;
    }

    public function getVariants(): array
    {
        return [
            new RouteApiVersionParametersAcceptor($this->actionParameter, $this->routeType),
        ];
    }

    public function isDeprecated(): TrinaryLogic
    {
        return TrinaryLogic::createNo();
    }

    public function getDeprecatedDescription(): ?string
    {
        return null;
    }

    public function isFinal(): TrinaryLogic
    {
        return TrinaryLogic::createYes();
    }

    public function isInternal(): TrinaryLogic
    {
        return TrinaryLogic::createNo();
    }

    public function getThrowType(): ?Type
    {
        return null;
    }

    public function hasSideEffects(): TrinaryLogic
    {
        return TrinaryLogic::createYes();
    }
}