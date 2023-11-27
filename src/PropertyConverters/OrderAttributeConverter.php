<?php
namespace Apie\StorageMetadata\PropertyConverters;

use Apie\StorageMetadata\Attributes\OrderAttribute;
use Apie\StorageMetadata\Interfaces\PropertyConverterInterface;
use Apie\StorageMetadata\Mediators\DomainToStorageContext;

class OrderAttributeConverter implements PropertyConverterInterface
{
    public function applyToDomain(
        DomainToStorageContext $context
    ): void {
        // no-op
    }

    public function applyToStorage(
        DomainToStorageContext $context
    ): void {
        foreach ($context->storageProperty->getAttributes(OrderAttribute::class) as $propertyAttribute) {
            $context->setStoragePropertyValue($context->arrayKey);
        }
    }
}
