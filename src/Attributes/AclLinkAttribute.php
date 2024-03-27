<?php
namespace Apie\StorageMetadata\Attributes;

use Apie\StorageMetadata\Interfaces\StorageDtoInterface;
use Attribute;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;

#[Attribute(Attribute::TARGET_PROPERTY)]
class AclLinkAttribute
{
    /**
     * @param class-string<StorageDtoInterface> $storageClass
     * @param class-string<object>|null $declaredClass
     */
    public function __construct(
        public readonly string $storageClass,
        public readonly ?string $declaredClass = null
    ) {
    }

    /**
     * @template T of object
     * @param ReflectionClass<T> $targetClass
     * @param T $instance
     */
    public function getReflectionProperty(ReflectionClass $targetClass, object $instance): ?ReflectionProperty
    {
        return null;
    }
}
