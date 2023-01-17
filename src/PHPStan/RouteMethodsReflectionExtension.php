<?php

declare(strict_types=1);

namespace Square\Vermillion\PHPStan;

use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use PHPStan\Analyser\OutOfClassScope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\MethodsClassReflectionExtension;
use PHPStan\Reflection\MissingMethodFromReflectionException;
use PHPStan\Reflection\ReflectionProvider;
use Square\Vermillion\PHPStan\MethodReflections\RouteApiVersionMethodReflection;

class RouteMethodsReflectionExtension implements MethodsClassReflectionExtension
{
    private ReflectionProvider $reflectionProvider;

    /**
     * @param ReflectionProvider $reflectionProvider
     */
    public function __construct(ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
    }
    public function hasMethod(ClassReflection $classReflection, string $methodName): bool
    {
        return is_a($classReflection->getName(), Route::class, true)
            && $methodName === 'apiVersion';
    }

    /**
     * @param ClassReflection $classReflection
     * @param string $methodName
     * @return MethodReflection
     * @throws MissingMethodFromReflectionException
     */
    public function getMethod(ClassReflection $classReflection, string $methodName): MethodReflection
    {
        $routerClass = $this->reflectionProvider->getClass(Router::class);
        $getMethod = $routerClass->getMethod('get', new OutOfClassScope());
        return new RouteApiVersionMethodReflection(
            $classReflection,
            new ClassMemberReflection($classReflection, true),
            $getMethod->getVariants()[0]->getParameters()[1],
        );
    }
}
