<?php
namespace Apie\StorageMetadata\PropertyConverters;

use Apie\Core\Attributes\Optional;
use Apie\Core\Utils\ConverterUtils;
use Apie\StorageMetadata\Attributes\OneToOneAttribute;
use Apie\StorageMetadata\Interfaces\PropertyConverterInterface;
use Apie\StorageMetadata\Interfaces\StorageDtoInterface;
use Apie\StorageMetadata\Mediators\DomainToStorageContext;
use ReflectionClass;

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
                    $domainPropertyValue = $domainProperty->getValue($context->domainObject);
                    if ($domainPropertyValue) {
                        $context->domainToStorageConverter->injectExistingDomainObject(
                            $domainPropertyValue,
                            $storagePropertyValue
                        );
                        continue;
                    }
                }
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

    public function applyToStorage(
        DomainToStorageContext $context
    ): void {
        foreach ($context->storageProperty->getAttributes(OneToOneAttribute::class) as $oneToOneAttribute) {
            $domainProperty = $oneToOneAttribute->newInstance()->getReflectionProperty($context->domainClass, $context->domainObject);
            if ($domainProperty) {
                $storageProperty = $context->storageProperty;
                if ($storageProperty->isInitialized($context->storageObject)) {
                    $storagePropertyValue = $storageProperty->getValue($context->storageObject);
                    if ($storagePropertyValue instanceof StorageDtoInterface) {
                        $context->domainToStorageConverter->injectExistingStorageObject(
                            $context->domainObject,
                            $storagePropertyValue,
                            $context
                        );
                        continue;
                    }
                }
                $domainPropertyValue = $domainProperty->isInitialized($context->domainObject) ? $domainProperty->getValue($context->domainObject) : null;
                /** @var ReflectionClass<StorageDtoInterface>|null $storageClass */
                $storageClass = ConverterUtils::toReflectionClass($storageProperty->getType());
                if ($storageClass && in_array(StorageDtoInterface::class, $storageClass->getInterfaceNames())) {
                    $storagePropertyValue = $context->domainToStorageConverter->createStorageObject($domainPropertyValue, $storageClass, $context);
                } else {
                    $storagePropertyValue = $context->dynamicCast($domainPropertyValue, $storageProperty->getType());
                }
                $storageProperty->setValue($context->storageObject, $storagePropertyValue);
            }
        }
    }
}
