<?php

namespace Square\Vermillion\PHPStan\MethodReflections;

use PHPStan\Reflection\ClassMemberReflection;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\TrinaryLogic;
use PHPStan\Type\Type;

class RouteFacadeVersionedMethodReflection implements MethodReflection
{
    private ClassReflection $facadeClass;

    private ClassMemberReflection $classMember;

    public function __construct(ClassReflection $facadeClass, ClassMemberReflection $classMember)
    {
        $this->facadeClass = $facadeClass;
        $this->classMember = $classMember;
    }
    public function getDeclaringClass(): ClassReflection
    {
        return $this->facadeClass;
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
        return false;
    }

    public function getDocComment(): ?string
    {
        return null;
    }

    public function getName(): string
    {
        return 'versioned';
    }

    public function getPrototype(): ClassMemberReflection
    {
        return $this->classMember;
    }

    public function getVariants(): array
    {
        return [
            new RouterFacadeVersionedParametersAcceptor(),
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
        return TrinaryLogic::createNo();
    }
}