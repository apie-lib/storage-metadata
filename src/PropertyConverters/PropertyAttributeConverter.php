<?php
namespace Apie\StorageMetadata\PropertyConverters;

use Apie\Core\Attributes\Optional;
use Apie\StorageMetadata\Attributes\GetMethodOrPropertyAttribute;
use Apie\StorageMetadata\Attributes\PropertyAttribute;
use Apie\StorageMetadata\Interfaces\PropertyConverterInterface;
use Apie\StorageMetadata\Mediators\DomainToStorageContext;

class PropertyAttributeConverter implements PropertyConverterInterface
{
    public function applyToDomain(
        DomainToStorageContext $context
    ): void {
        $storageProperty = $context->storageProperty;
        $propertyAttributes = [
            ...$storageProperty->getAttributes(PropertyAttribute::class),
            ...$storageProperty->getAttributes(GetMethodOrPropertyAttribute::class),
        ];
        foreach ($propertyAttributes as $propertyAttribute) {
            $domainProperty = $propertyAttribute->newInstance()->getReflectionProperty($context->domainClass, $context->domainObject);
            if ($domainProperty && (!$domainProperty->isInitialized($context->domainObject) || !$domainProperty->isReadOnly())) {
                $domainPropertyType = $domainProperty->getType();
                $domainPropertyValue = $context->dynamicCast($context->getStoragePropertyValue(), $domainPropertyType);
                if (!$domainPropertyType->allowsNull() && $domainPropertyValue === null && $domainProperty->getAttributes(Optional::class)) {
                    continue;
                }
                $domainProperty->setValue($context->domainObject, $domainPropertyValue);
            }
        }
    }

    public function applyToStorage(
        DomainToStorageContext $context
    ): void {
        $storageProperty = $context->storageProperty;

        foreach ($storageProperty->getAttributes(PropertyAttribute::class) as $propertyAttribute) {
            $domainProperty = $propertyAttribute->newInstance()->getReflectionProperty($context->domainClass, $context->domainObject);
            if ($domainProperty) {
                $storagePropertyType = $storageProperty->getType();
                $domainPropertyValue = $domainProperty->getValue($context->domainObject);
                $storagePropertyValue = $context->dynamicCast($domainPropertyValue, $storagePropertyType);
                $context->setStoragePropertyValue($storagePropertyValue);
            }
        }
    }
}
