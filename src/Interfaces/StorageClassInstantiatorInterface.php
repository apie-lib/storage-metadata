<?php
namespace Apie\StorageMetadata\Interfaces;

use ReflectionClass;

interface StorageClassInstantiatorInterface extends StorageDtoInterface
{
    /**
     * @param ReflectionClass<object> $class
     */
    public function createDomainObject(ReflectionClass $class): object;
}
