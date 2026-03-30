<?php
namespace Apie\StorageMetadata\ClassInstantiators;

use Apie\StorageMetadata\Interfaces\ClassInstantiatorInterface;
use Apie\StorageMetadata\Interfaces\StorageDtoInterface;
use ReflectionClass;
use WeakMap;

final class FromReflection implements ClassInstantiatorInterface
{
    public function supports(ReflectionClass $class, ?StorageDtoInterface $storageObject = null): bool
    {
        return $class->isInstantiable();
    }

    public function create(ReflectionClass $class, ?StorageDtoInterface $storageObject = null): object
    {
        if ($class->name === WeakMap::class) {
            return new WeakMap();
        }
        return $class->newInstanceWithoutConstructor();
    }
}
