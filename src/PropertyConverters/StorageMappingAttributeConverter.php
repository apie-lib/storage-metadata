<?php
namespace Apie\StorageMetadata\PropertyConverters;

use Apie\StorageMetadata\Attributes\StorageMappingAttribute;
use Apie\StorageMetadata\Interfaces\PropertyConverterInterface;
use Apie\StorageMetadata\Mediators\DomainToStorageContext;

class StorageMappingAttributeConverter implements PropertyConverterInterface
{
    public function applyToDomain(
        DomainToStorageContext $context
    ): void {
    }

    public function applyToStorage(
        DomainToStorageContext $context
    ): void {
        foreach ($context->storageProperty->getAttributes(StorageMappingAttribute::class) as $attribute) {
            $context->storageProperty->setValue($context->storageObject, $context->fileStorage);
        }
    }
}
