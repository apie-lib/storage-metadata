<?php
namespace Apie\StorageMetadata\PropertyConverters;

use Apie\Core\ValueObjects\Utils;
use Apie\StorageMetadata\Attributes\OneToManyAttribute;
use Apie\StorageMetadata\Interfaces\PropertyConverterInterface;
use Apie\StorageMetadata\Interfaces\StorageDtoInterface;
use Apie\StorageMetadata\Mediators\DomainToStorageContext;
use Apie\StorageMetadataBuilder\Interfaces\MixedStorageInterface;
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
                    if ($arrayValue instanceof MixedStorageInterface) {
                        $domainProperties[$arrayKey] = $arrayValue->toOriginalObject();
                    } elseif ($arrayValue instanceof StorageDtoInterface && isset($domainProperties[$arrayKey])) {
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

    /**
     * @template T of object
     * @param class-string<T> $className
     * @return ReflectionClass<T>
     */
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
                    $storageClassRefl = $this->toReflClass($oneToManyAttribute->newInstance()->storageClass);
                    if (is_object($arrayValue) && in_array(StorageDtoInterface::class, $storageClassRefl->getInterfaceNames())) {
                        if (isset($storageProperties[$arrayKey]) && $storageProperties[$arrayKey] instanceof StorageDtoInterface) {
                            $arrayContext->domainToStorageConverter->injectExistingStorageObject(
                                $arrayValue,
                                $storageProperties[$arrayKey],
                                $arrayContext
                            );
                        } else {
                            $storageProperties[$arrayKey] = $arrayContext->domainToStorageConverter->createStorageObject(
                                $arrayValue,
                                $storageClassRefl,
                                $arrayContext
                            );
                        }
                    } else {
                        $storageProperties[$arrayKey] = $storageClassRefl->newInstance(Utils::toString($arrayValue));
                        // @phpstan-ignore-next-line
                        $storageProperties[$arrayKey]->listOrder = $arrayKey;
                        // @phpstan-ignore-next-line
                        $storageProperties[$arrayKey]->parent = $context->storageObject;
                    }
                }
                $context->storageProperty->setValue($context->storageObject, $context->dynamicCast($storageProperties, $context->storageProperty->getType()));
            }
        }
    }
}
