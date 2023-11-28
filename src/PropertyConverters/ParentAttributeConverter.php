<?php
namespace Apie\StorageMetadata\PropertyConverters;

use Apie\StorageMetadata\Attributes\ParentAttribute;
use Apie\StorageMetadata\Interfaces\PropertyConverterInterface;
use Apie\StorageMetadata\Mediators\DomainToStorageContext;

class ParentAttributeConverter implements PropertyConverterInterface
{
    public function applyToDomain(
        DomainToStorageContext $context
    ): void {
        // no-op
    }

    public function applyToStorage(
        DomainToStorageContext $context
    ): void {
        if (!isset($context->parentContext)) {
            return;
        }
        foreach ($context->storageProperty->getAttributes(ParentAttribute::class) as $propertyAttribute) {
            $context->setStoragePropertyValue($context->parentContext->storageObject);
        }
    }
}
