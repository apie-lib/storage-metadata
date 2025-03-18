<?php
namespace Apie\StorageMetadata\Attributes;

use Attribute;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ManyToOneAttribute
{
    public function __construct(
        private string $propertyName
    ) {
    }

    /**
     * @template T of object
     * @param ReflectionClass<T> $targetClass
     * @param T $instance
     */
    public function getReflectionProperty(ReflectionClass $targetClass, object $instance): ?ReflectionProperty
    {
        $property = $targetClass->getProperty($this->propertyName);
        try {
            $property->isInitialized($instance);
            return $property;
        } catch (ReflectionException) {
            return null;
        }
    }
}
