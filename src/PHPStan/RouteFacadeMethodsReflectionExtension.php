<?php

declare(strict_types=1);

namespace Square\Vermillion\PHPStan;

use Illuminate\Support\Facades\Route as RouteFacade;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\MethodsClassReflectionExtension;
use Square\Vermillion\PHPStan\MethodReflections\RouteFacadeVersionedMethodReflection;

class RouteFacadeMethodsReflectionExtension implements MethodsClassReflectionExtension
{
    public function hasMethod(ClassReflection $classReflection, string $methodName): bool
    {
        return is_a($classReflection->getName(), RouteFacade::class, true)
            && $methodName === 'versioned';
    }

    public function getMethod(ClassReflection $classReflection, string $methodName): MethodReflection
    {
        return new RouteFacadeVersionedMethodReflection(
            $classReflection,
            new ClassMemberReflection($classReflection, true),
        );
    }
}