<?php
namespace Apie\StorageMetadata\Interfaces;

use ReflectionClass;

interface ClassInstantiatorInterface
{
    /**
     * @param ReflectionClass<object> $class
     */
    public function supports(ReflectionClass $class, ?StorageDtoInterface $storageObject = null): bool;

    /**
     * @template T of object
     * @param ReflectionClass<T> $class
     * @return T
     */
    public function create(ReflectionClass $class, ?StorageDtoInterface $storageObject = null): object;
}
