<?php
namespace Apie\StorageMetadata\Interfaces;

use ReflectionClass;

interface ClassInstantiatorInterface
{
    /**
     * @param ReflectionClass<covariant object> $class
     */
    public function supports(ReflectionClass $class, ?StorageDtoInterface $storageObject = null): bool;

    /**
     * @template T of object
     * @param ReflectionClass<covariant T> $class
     * @return T
     */
    public function create(ReflectionClass $class, ?StorageDtoInterface $storageObject = null): object;
}
