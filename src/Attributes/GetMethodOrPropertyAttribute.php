<?php
namespace Apie\StorageMetadata\Attributes;

use Attribute;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

#[Attribute(Attribute::TARGET_PROPERTY)]
class GetMethodOrPropertyAttribute extends PropertyAttribute
{
    /**
     * @param class-string<object>|null $declaredClass
     */
    public function __construct(
        public readonly string $methodName,
        string $propertyName,
        ?string $declaredClass = null,
        bool $allowLargeStrings = false
    ) {
        parent::__construct($propertyName, $declaredClass, $allowLargeStrings);
    }

    /**
     * @template T of object
     * @param ReflectionClass<T> $targetClass
     * @param T $instance
     */
    public function getReflectionMethod(ReflectionClass $targetClass, object $instance): ?ReflectionMethod
    {
        return (new GetMethodAttribute($this->methodName, $this->declaredClass, $this->allowLargeStrings))
            ->getReflectionMethod($targetClass, $instance);
    }

    /**
     * @template T of object
     * @param ReflectionClass<T> $targetClass
     * @param T $instance
     */
    public function getReflectionProperty(
        ReflectionClass $targetClass,
        object $instance
    ): ?ReflectionProperty {
        $method = $this->getReflectionMethod($targetClass, $instance);
        if ($method) {
            $method->invoke($instance);
        }
        return parent::getReflectionProperty($targetClass, $instance);
    }
}
