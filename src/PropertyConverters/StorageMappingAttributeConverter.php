<?php
namespace Apie\StorageMetadata\PropertyConverters;

use Apie\Core\FileStorage\StoredFile;
use Apie\StorageMetadata\Attributes\StorageMappingAttribute;
use Apie\StorageMetadata\Interfaces\PropertyConverterInterface;
use Apie\StorageMetadata\Mediators\DomainToStorageContext;
use ReflectionProperty;

class StorageMappingAttributeConverter implements PropertyConverterInterface
{
    public function applyToDomain(
        DomainToStorageContext $context
    ): void {
        foreach ($context->storageProperty->getAttributes(StorageMappingAttribute::class) as $attribute) {
            (new ReflectionProperty(StoredFile::class, 'storage'))->setValue(
                $context->domainObject,
                $context->fileStorage
            );
        }
    }

    public function applyToStorage(
        DomainToStorageContext $context
    ): void {
        foreach ($context->storageProperty->getAttributes(StorageMappingAttribute::class) as $attribute) {
            $context->storageProperty->setValue($context->storageObject, $context->fileStorage);
        }
    }
}
