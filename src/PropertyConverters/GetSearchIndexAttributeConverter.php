<?php
namespace Apie\StorageMetadata\PropertyConverters;

use Apie\Core\Context\ApieContext;
use Apie\Core\Indexing\Indexer;
use Apie\StorageMetadata\Attributes\GetSearchIndexAttribute;
use Apie\StorageMetadata\Interfaces\PropertyConverterInterface;
use Apie\StorageMetadata\Mediators\DomainToStorageContext;
use Apie\TypeConverter\ReflectionTypeFactory;

class GetSearchIndexAttributeConverter implements PropertyConverterInterface
{
    public function __construct(private readonly Indexer $indexer)
    {
    }

    public function applyToDomain(
        DomainToStorageContext $context
    ): void {
        // no-op
    }

    public function applyToStorage(
        DomainToStorageContext $context
    ): void {
        $storageProperty = $context->storageProperty;

        foreach ($storageProperty->getAttributes(GetSearchIndexAttribute::class) as $propertyAttribute) {
            $domainPropertyValue = $propertyAttribute->newInstance()->getValue($context->domainClass, $context->domainObject);
            $storagePropertyType = $storageProperty->getType();
            if (is_bool($domainPropertyValue) || $domainPropertyValue === null) {
                $indexes = [$domainPropertyValue ? '1' : '0'];
            } elseif (is_object($domainPropertyValue)) {
                $indexes = array_keys($this->indexer->getIndexesForObject($domainPropertyValue, new ApieContext()));
            } else {
                $indexes = [$context->dynamicCast($domainPropertyValue, ReflectionTypeFactory::createReflectionType('string'))];
            }
            $storagePropertyValue = $context->dynamicCast($indexes, $storagePropertyType);
            $context->setStoragePropertyValue($storagePropertyValue);
        }
    }
}
