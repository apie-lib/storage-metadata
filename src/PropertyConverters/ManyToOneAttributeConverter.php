<?php
namespace Apie\StorageMetadata\PropertyConverters;

use Apie\Core\TypeUtils;
use Apie\StorageMetadata\Attributes\ManyToOneAttribute;
use Apie\StorageMetadata\Interfaces\PropertyConverterInterface;
use Apie\StorageMetadata\Mediators\DomainToStorageContext;
use ReflectionProperty;

class ManyToOneAttributeConverter implements PropertyConverterInterface
{
    public function applyToDomain(
        DomainToStorageContext $context
    ): void {
        // no-op
    }

    public static function applyToProperty(ReflectionProperty $property, object $object, object $parentObject): void
    {
        foreach ($property->getAttributes(ManyToOneAttribute::class) as $propertyAttribute) {
            if (TypeUtils::matchesType(
                $property->getType(),
                $parentObject
            )) {
                $property->setValue($object, $parentObject);
            } else {
                $property->setValue($object, null);
            }
        }
    }

    public function applyToStorage(
        DomainToStorageContext $context
    ): void {
        if (!isset($context->parentContext)) {
            return;
        }
        self::applyToProperty($context->storageProperty, $context->storageObject, $context->parentContext->storageObject);
    }
}
