<?php
namespace Apie\StorageMetadata\Interfaces;

use ReflectionClass;

interface StorageClassInstantiatorInterface extends StorageDtoInterface
{
    /**
     * @param ReflectionClass<covariant object> $class
     */
    public function createDomainObject(ReflectionClass $class): object;
}
