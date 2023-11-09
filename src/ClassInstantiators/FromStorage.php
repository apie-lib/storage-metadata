<?php
namespace Apie\StorageMetadata\ClassInstantiators;

use Apie\StorageMetadata\Interfaces\ClassInstantiatorInterface;
use Apie\StorageMetadata\Interfaces\StorageClassInstantiatorInterface;
use Apie\StorageMetadata\Interfaces\StorageDtoInterface;
use ReflectionClass;

final class FromStorage implements ClassInstantiatorInterface
{
    public function supports(ReflectionClass $class, ?StorageDtoInterface $storageObject = null): bool
    {
        return $storageObject instanceof StorageClassInstantiatorInterface;
    }

    public function create(ReflectionClass $class, ?StorageDtoInterface $storageObject = null): object
    {
        assert($storageObject instanceof StorageClassInstantiatorInterface);
        return $storageObject->createDomainObject($class);
    }
}
