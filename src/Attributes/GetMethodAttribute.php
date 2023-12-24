<?php
namespace Apie\StorageMetadata\Attributes;

use Attribute;
use ReflectionClass;
use ReflectionMethod;

#[Attribute(Attribute::TARGET_PROPERTY)]
class GetMethodAttribute
{
    /**
     * @param class-string<object>|null $declaredClass
     */
    public function __construct(
        public readonly string $methodName,
        public readonly ?string $declaredClass = null
    ) {
    }

    /**
     * @template T of object
     * @param ReflectionClass<T> $targetClass
     * @param T $instance
     */
    public function getReflectionMethod(ReflectionClass $targetClass, object $instance): ?ReflectionMethod
    {
        return ($this->declaredClass ? new ReflectionClass($this->declaredClass) : $targetClass)->getMethod($this->methodName);
    }
}
