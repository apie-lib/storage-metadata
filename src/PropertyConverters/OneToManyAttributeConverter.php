<?php
namespace Apie\StorageMetadata\PropertyConverters;

use Apie\Core\ValueObjects\Utils;
use Apie\StorageMetadata\Attributes\OneToManyAttribute;
use Apie\StorageMetadata\Interfaces\PropertyConverterInterface;
use Apie\StorageMetadata\Interfaces\StorageDtoInterface;
use Apie\StorageMetadata\Mediators\DomainToStorageContext;
use Apie\TypeConverter\ReflectionTypeFactory;
use ReflectionClass;

class OneToManyAttributeConverter implements PropertyConverterInterface
{
    public function applyToDomain(
        DomainToStorageContext $context
    ): void {
        
        foreach ($context->storageProperty->getAttributes(OneToManyAttribute::class) as $oneToManyAttribute) {
            $domainProperty = $oneToManyAttribute->newInstance()->getReflectionProperty($context->domainClass, $context->domainObject);
            if ($domainProperty) {
                $storagePropertyValue = Utils::toArray($context->getStoragePropertyValue());
                $domainPropertyType = $domainProperty->getType();
                $domainProperties = $domainProperty->isInitialized($context->domainObject)
                    ? Utils::toArray($domainProperty->getValue($context->domainObject))
                    : [];
                foreach ($storagePropertyValue as $arrayKey => $arrayValue) {
                    if ($arrayValue instanceof StorageDtoInterface && isset($domainProperties[$arrayKey])) {
                        $context->domainToStorageConverter->injectExistingDomainObject(
                            $domainProperties[$arrayKey],
                            $arrayValue,
                            $context
                        );
                    } else {
                        $domainProperties[$arrayKey] = $arrayValue instanceof StorageDtoInterface
                            ? $context->domainToStorageConverter->createDomainObject($arrayValue, $context)
                            : $context->dynamicCast($arrayValue, ReflectionTypeFactory::createReflectionType($oneToManyAttribute->newInstance()->declaredClass));
                    }
                }
                $domainProperty->setValue($context->domainObject, $context->dynamicCast($domainProperties, $domainPropertyType));
            }
        }
    }

    private function toReflClass(string $className): ReflectionClass
    {
        if (str_starts_with($className, 'apie_')) {
            return new ReflectionClass('Generated\\ApieEntities\\' . $className);
        }
        return new ReflectionClass($className);
    }

    public function applyToStorage(
        DomainToStorageContext $context
    ): void {
        foreach ($context->storageProperty->getAttributes(OneToManyAttribute::class) as $oneToManyAttribute) {
            $domainProperty = $oneToManyAttribute->newInstance()->getReflectionProperty($context->domainClass, $context->domainObject);
            if ($domainProperty) {
                $domainPropertyValue = Utils::toArray($domainProperty->getValue($context->domainObject));
                $storageProperties = $context->storageProperty->isInitialized($context->storageObject)
                    ? Utils::toArray($context->storageProperty->getValue($context->storageObject))
                    : [];
                foreach ($domainPropertyValue as $arrayKey => $arrayValue) {
                    $arrayContext = $context->withArrayKey($arrayKey);
                    if (is_object($arrayValue) && isset($storageProperties[$arrayKey]) && $storageProperties[$arrayKey] instanceof StorageDtoInterface) {
                        $arrayContext->domainToStorageConverter->injectExistingStorageObject(
                            $arrayValue,
                            $storageProperties[$arrayKey],
                            $arrayContext
                        );
                    } else {
                        $storageProperties[$arrayKey] = $arrayContext->domainToStorageConverter->createStorageObject(
                            $domainPropertyValue[$arrayKey],
                            $this->toReflClass($oneToManyAttribute->newInstance()->storageClass),
                            $arrayContext
                        );
                    }
                }
                $context->storageProperty->setValue($context->storageObject, $context->dynamicCast($storageProperties, $context->storageProperty->getType()));
            }
        }
    }
}
