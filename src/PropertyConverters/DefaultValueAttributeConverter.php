<?php
namespace Apie\StorageMetadata\PropertyConverters;

use Apie\StorageMetadata\Interfaces\PropertyConverterInterface;
use Apie\StorageMetadata\Mediators\DomainToStorageContext;

class DefaultValueAttributeConverter implements PropertyConverterInterface
{
    public function applyToDomain(
        DomainToStorageContext $context
    ): void {
    }

    public function applyToStorage(
        DomainToStorageContext $context
    ): void {
        if (!$context->storageProperty->isInitialized($context->storageObject)) {
            if ($context->storageProperty->hasDefaultValue()) {
                $context->storageProperty->setValue($context->storageObject, $context->storageProperty->getDefaultValue());
            } elseif (!$context->storageProperty->getType() || $context->storageProperty->getType()->allowsNull()) {
                $context->storageProperty->setValue($context->storageObject, null);
            }
        }
    }
}
