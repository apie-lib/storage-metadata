<?php
namespace Apie\StorageMetadata\ClassInstantiators;

use Apie\Core\TypeUtils;
use Apie\StorageMetadata\Interfaces\ClassInstantiatorInterface;
use Apie\StorageMetadata\Interfaces\StorageDtoInterface;
use Apie\StorageMetadataBuilder\Interfaces\MixedStorageInterface;
use Apie\TypeConverter\ReflectionTypeFactory;
use ReflectionClass;

final class FromMixedStorage implements ClassInstantiatorInterface
{
    public function supports(ReflectionClass $class, ?StorageDtoInterface $storageObject = null): bool
    {
        return $storageObject instanceof MixedStorageInterface
            && TypeUtils::matchesType(
                ReflectionTypeFactory::createReflectionType($class->name),
                $storageObject->toOriginalObject()
            );
    }

    /**
     * @param MixedStorageInterface&StorageDtoInterface $storageObject
     */
    public function create(ReflectionClass $class, ?StorageDtoInterface $storageObject = null): object
    {
        return $storageObject->toOriginalObject();
    }
}
