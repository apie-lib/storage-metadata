<?php
namespace Apie\StorageMetadata\PropertyConverters;

use Apie\Core\Attributes\Optional;
use Apie\StorageMetadata\Attributes\PropertyAttribute;
use Apie\StorageMetadata\Interfaces\PropertyConverterInterface;
use Apie\StorageMetadata\Mediators\DomainToStorageContext;

class PropertyAttributeConverter implements PropertyConverterInterface
{
    public function applyToDomain(
        DomainToStorageContext $context
    ): void {
        $storageProperty = $context->storageProperty;

        foreach ($storageProperty->getAttributes(PropertyAttribute::class) as $propertyAttribute) {
            $domainProperty = $propertyAttribute->newInstance()->getReflectionProperty($context->domainClass, $context->domainObject);
            if ($domainProperty) {
                $domainPropertyType = $domainProperty->getType();
                $domainPropertyValue = $context->dynamicCast($context->getStoragePropertyValue(), $domainPropertyType);
                if (!$domainPropertyType->allowsNull() && $domainPropertyValue === null && $domainProperty->getAttributes(Optional::class)) {
                    continue;
                }
                $domainProperty->setValue($context->domainObject, $domainPropertyValue);
            }
        }
    }
}
