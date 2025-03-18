<?php
namespace Apie\StorageMetadata\PropertyConverters;

use Apie\Core\ValueObjects\Utils;
use Apie\StorageMetadata\Attributes\OneToManyAttribute;
use Apie\StorageMetadata\Interfaces\PropertyConverterInterface;
use Apie\StorageMetadata\Interfaces\StorageDtoInterface;
use Apie\StorageMetadata\Mediators\DomainToStorageContext;
use Apie\StorageMetadataBuilder\Interfaces\MixedStorageInterface;
use Apie\TypeConverter\ReflectionTypeFactory;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\PersistentCollection;
use ReflectionClass;
use Throwable;

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
    private function toReflClass(string $className, mixed $contextStorageObject): ReflectionClass
    {
        if (str_starts_with($className, 'apie_') && is_object($contextStorageObject)) {
            $refl = new ReflectionClass($contextStorageObject);
            return new ReflectionClass($refl->getNamespaceName() . '\\' . $className);
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
                $storageShouldBeReplaced = !$context->storageProperty->isInitialized($context->storageObject);
                $storageProperties = $storageShouldBeReplaced
                    ? []
                    : $context->storageProperty->getValue($context->storageObject);
                $keysToRemove = array_diff(
                    array_keys(Utils::toArray($storageProperties)),
                    array_keys($domainPropertyValue),
                );
                try {
                    foreach ($keysToRemove as $keyToRemove) {
                        unset($storageProperties[$keyToRemove]);
                        // this is an edge case where we have some item list that can not unset values
                        if (isset($storageProperties[$keyToRemove])) {
                            $storageProperties = $context->dynamicCast([], $context->storageProperty->getType());
                            $storageShouldBeReplaced = true;
                            break;
                        }
                    }
                } catch (Throwable) {
                    // another edge case where an array object class throws an exception.
                    $storageProperties = $context->dynamicCast([], $context->storageProperty->getType());
                    $storageShouldBeReplaced = true;
                }
                foreach ($domainPropertyValue as $arrayKey => $arrayValue) {
                    $arrayContext = $context->withArrayKey($arrayKey);
                    $storageClassRefl = $this->toReflClass($oneToManyAttribute->newInstance()->storageClass, $context->storageObject);
                    if (is_object($arrayValue) && in_array(StorageDtoInterface::class, $storageClassRefl->getInterfaceNames())) {
                        if (isset($storageProperties[$arrayKey]) && $storageProperties[$arrayKey] instanceof StorageDtoInterface && !$storageShouldBeReplaced) {
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
                        //  if we do not do this hack we get cloned objects.
                        if ($storageProperties instanceof PersistentCollection) {
                            $storageProperties = new ArrayCollection(
                                $storageProperties
                                    ->map(function ($t) { return clone $t; })
                                    ->toArray()
                            );
                            $storageShouldBeReplaced = true;
                        }
                        $storageProperties[$arrayKey] = $storageClassRefl->newInstance(Utils::toString($arrayValue));
                        // @phpstan-ignore-next-line
                        $storageProperties[$arrayKey]->listOrder = $arrayKey;
                        // @phpstan-ignore-next-line
                        $storageProperties[$arrayKey]->parent = $context->storageObject;
                    }
                }
                if ($storageShouldBeReplaced) {
                    $context->storageProperty->setValue($context->storageObject, $context->dynamicCast($storageProperties, $context->storageProperty->getType()));
                }
            }
        }
    }
}
