<?php
namespace Apie\StorageMetadata\PropertyConverters;

use Apie\Core\Entities\PolymorphicEntityInterface;
use Apie\Core\Utils\EntityUtils;
use Apie\StorageMetadata\Attributes\DiscriminatorMappingAttribute;
use Apie\StorageMetadata\Interfaces\PropertyConverterInterface;
use Apie\StorageMetadata\Mediators\DomainToStorageContext;

class DiscriminatorMappingAttributeConverter implements PropertyConverterInterface
{
    public function applyToDomain(
        DomainToStorageContext $context
    ): void {
        // no-op
    }

    public function applyToStorage(
        DomainToStorageContext $context
    ): void {
        if ($context->domainObject instanceof PolymorphicEntityInterface) {
            $mapping = EntityUtils::getDiscriminatorValues($context->domainObject, $context->domainClass);
            foreach ($context->storageProperty->getAttributes(DiscriminatorMappingAttribute::class) as $propertyAttribute) {
                $context->setStoragePropertyValue($mapping);
            }
        }
    }
}
