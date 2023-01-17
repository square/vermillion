<?php
declare(strict_types=1);

namespace Square\Vermillion\PHPStan;
use PHPStan\Reflection\ClassMemberReflection as ClassMemberReflectionContract;
use PHPStan\Reflection\ClassReflection;

class ClassMemberReflection implements ClassMemberReflectionContract
{
    private ClassReflection $class;
    private bool $isStatic;

    public function __construct(ClassReflection $class, bool $isStatic)
    {
        $this->class = $class;
        $this->isStatic = $isStatic;
    }

    public function getDeclaringClass(): ClassReflection
    {
        return $this->class;
    }

    public function isStatic(): bool
    {
        return $this->isStatic;
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
}