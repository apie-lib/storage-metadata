<?php
namespace Apie\StorageMetadata\PropertyConverters;

use Apie\Core\TypeUtils;
use Apie\StorageMetadata\Attributes\ManyToOneAttribute;
use Apie\StorageMetadata\Interfaces\PropertyConverterInterface;
use Apie\StorageMetadata\Mediators\DomainToStorageContext;

class ManyToOneAttributeConverter implements PropertyConverterInterface
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
        foreach ($context->storageProperty->getAttributes(ManyToOneAttribute::class) as $propertyAttribute) {
            if (TypeUtils::matchesType(
                $context->storageProperty->getType(),
                $context->parentContext->storageObject
            )) {
                $context->setStoragePropertyValue($context->parentContext->storageObject);
            } else {
                $context->setStoragePropertyValue(null);
            }
        }
    }
}
