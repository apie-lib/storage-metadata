<?php
namespace Apie\StorageMetadata\PropertyConverters;

use Apie\Core\Attributes\Optional;
use Apie\StorageMetadata\Attributes\OneToOneAttribute;
use Apie\StorageMetadata\Interfaces\PropertyConverterInterface;
use Apie\StorageMetadata\Interfaces\StorageDtoInterface;
use Apie\StorageMetadata\Mediators\DomainToStorageContext;

class OneToOneAttributeConverter implements PropertyConverterInterface
{
    public function applyToDomain(
        DomainToStorageContext $context
    ): void {
        foreach ($context->storageProperty->getAttributes(OneToOneAttribute::class) as $oneToOneAttribute) {
            $domainProperty = $oneToOneAttribute->newInstance()->getReflectionProperty($context->domainClass, $context->domainObject);
            if ($domainProperty) {
                $storagePropertyValue = $context->getStoragePropertyValue();
                $domainPropertyType = $domainProperty->getType();
                if ($domainProperty->isInitialized($context->domainObject) && $storagePropertyValue instanceof StorageDtoInterface) {
                    $context->domainToStorageConverter->injectExistingDomainObject(
                        $context->domainObject,
                        $storagePropertyValue
                    );
                } else {
                    $domainPropertyValue = $storagePropertyValue instanceof StorageDtoInterface
                        ? $context->domainToStorageConverter->createDomainObject($storagePropertyValue)
                        : $context->dynamicCast($storagePropertyValue, $domainPropertyType);
                    if (!$domainPropertyType->allowsNull() && $domainPropertyValue === null && $domainProperty->getAttributes(Optional::class)) {
                        continue;
                    }
                    $domainProperty->setValue($context->domainObject, $domainPropertyValue);
                }
            }
        }
    }
}
