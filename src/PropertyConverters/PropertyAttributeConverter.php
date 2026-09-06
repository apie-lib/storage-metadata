<?php
namespace Apie\StorageMetadata\PropertyConverters;

use Apie\Core\Attributes\Optional;
use Apie\StorageMetadata\Attributes\DecimalPropertyAttribute;
use Apie\StorageMetadata\Attributes\PropertyAttribute;
use Apie\StorageMetadata\DomainToStorageConverter;
use Apie\StorageMetadata\Interfaces\PropertyConverterInterface;
use Apie\StorageMetadata\Mediators\DomainToStorageContext;

class PropertyAttributeConverter implements PropertyConverterInterface
{
    /**
     * @return array<\ReflectionAttribute<PropertyAttribute>>
     */
    private function getPropertyAttributes(DomainToStorageContext $context): array
    {
        $storageProperty = $context->storageProperty;
        return [
            ...$storageProperty->getAttributes(PropertyAttribute::class),
            ...$storageProperty->getAttributes(DecimalPropertyAttribute::class),
        ];
    }

    public function applyToDomain(
        DomainToStorageContext $context
    ): void {
        foreach ($this->getPropertyAttributes($context) as $propertyAttribute) {
            $domainProperty = $propertyAttribute->newInstance()->getReflectionProperty($context->domainClass, $context->domainObject);
            if ($domainProperty && (!$domainProperty->isInitialized($context->domainObject) || !$domainProperty->isReadOnly())) {
                $domainPropertyType = $domainProperty->getType();
                $domainPropertyValue = $context->dynamicCast($context->getStoragePropertyValue(), $domainPropertyType);
                if (!$domainPropertyType->allowsNull() && $domainPropertyValue === null && $domainProperty->getAttributes(Optional::class)) {
                    continue;
                }
                if (DomainToStorageConverter::isReallyWritable($context->domainObject, $domainProperty)) {
                    $domainProperty->setValue($context->domainObject, $domainPropertyValue);
                }
            }
        }
    }

    public function applyToStorage(
        DomainToStorageContext $context
    ): void {
        $storageProperty = $context->storageProperty;

        foreach ($this->getPropertyAttributes($context) as $propertyAttribute) {
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
