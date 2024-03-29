<?php
namespace Apie\StorageMetadata\PropertyConverters;

use Apie\Core\Permissions\RequiresPermissionsInterface;
use Apie\Core\ValueObjects\Utils;
use Apie\StorageMetadata\Attributes\AclLinkAttribute;
use Apie\StorageMetadata\Interfaces\PropertyConverterInterface;
use Apie\StorageMetadata\Interfaces\StorageDtoInterface;
use Apie\StorageMetadata\Mediators\DomainToStorageContext;
use ReflectionClass;
use ReflectionProperty;

class AccessControlListAttributeConverter implements PropertyConverterInterface
{
    public function applyToDomain(
        DomainToStorageContext $context
    ): void {
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
        foreach ($context->storageProperty->getAttributes(AclLinkAttribute::class) as $oneToManyAttribute) {
            $domainPropertyValue = $context->domainObject instanceof RequiresPermissionsInterface
                ? $context->domainObject->getRequiredPermissions()->toStringList()->toArray()
                : [];
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
                    $properties = $storageClassRefl->getProperties(ReflectionProperty::IS_PUBLIC);
                    foreach ($properties as $property) {
                        ManyToOneAttributeConverter::applyToProperty(
                            $property,
                            $storageProperties[$arrayKey],
                            $context->storageObject
                        );
                    }
                }
            }
            $context->storageProperty->setValue($context->storageObject, $context->dynamicCast($storageProperties, $context->storageProperty->getType()));
        }
    }
}
