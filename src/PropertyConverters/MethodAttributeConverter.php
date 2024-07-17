<?php
namespace Apie\StorageMetadata\PropertyConverters;

use Apie\StorageMetadata\Attributes\GetMethodAttribute;
use Apie\StorageMetadata\Attributes\GetMethodOrPropertyAttribute;
use Apie\StorageMetadata\Interfaces\PropertyConverterInterface;
use Apie\StorageMetadata\Mediators\DomainToStorageContext;

class MethodAttributeConverter implements PropertyConverterInterface
{
    public function applyToDomain(
        DomainToStorageContext $context
    ): void {
        // no-op
    }

    public function applyToStorage(
        DomainToStorageContext $context
    ): void {
        $storageProperty = $context->storageProperty;

        $propertyAttributes = [
            ...$storageProperty->getAttributes(GetMethodAttribute::class),
            ...$storageProperty->getAttributes(GetMethodOrPropertyAttribute::class),
        ];

        foreach ($propertyAttributes as $propertyAttribute) {
            $domainMethod = $propertyAttribute->newInstance()->getReflectionMethod($context->domainClass, $context->domainObject);
            if ($domainMethod) {
                $storagePropertyType = $storageProperty->getType();
                $domainPropertyValue = $domainMethod->invoke($context->domainObject);
                $storagePropertyValue = $context->dynamicCast($domainPropertyValue, $storagePropertyType);
                $context->setStoragePropertyValue($storagePropertyValue);
            }
        }
    }
}
