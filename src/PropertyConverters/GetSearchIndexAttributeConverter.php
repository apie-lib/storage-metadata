<?php
namespace Apie\StorageMetadata\PropertyConverters;

use Apie\Core\Context\ApieContext;
use Apie\Core\Indexing\Indexer;
use Apie\DoctrineEntityConverter\Entities\SearchIndex;
use Apie\StorageMetadata\Attributes\GetSearchIndexAttribute;
use Apie\StorageMetadata\Interfaces\PropertyConverterInterface;
use Apie\StorageMetadata\Mediators\DomainToStorageContext;
use Apie\TypeConverter\ReflectionTypeFactory;
use ReflectionClass;

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
            $arrayValueType = $propertyAttribute->newInstance()->arrayValueType;
            if ($arrayValueType && str_starts_with($arrayValueType, 'apie_')) {
                $arrayValueType = (new ReflectionClass($context->storageObject))->getNamespaceName() . '\\' . $arrayValueType;
            }
            $domainPropertyValue = $propertyAttribute->newInstance()->getValue($context->domainClass, $context->domainObject);
            $storagePropertyType = $storageProperty->getType();
            if ($domainPropertyValue === null) {
                $indexes = [];
            } elseif (is_bool($domainPropertyValue)) {
                $indexes = [$domainPropertyValue ? '1' : '0'];
            } elseif (is_object($domainPropertyValue)) {
                $indexes = array_keys($this->indexer->getIndexesForObject($domainPropertyValue, new ApieContext()));
            } elseif (is_resource($domainPropertyValue)) {
                $indexes = [];
            } elseif (get_debug_type($domainPropertyValue) === 'resource (closed)') {
                $indexes = [];
            } else {
                $indexes = [$context->dynamicCast($domainPropertyValue, ReflectionTypeFactory::createReflectionType('string'))];
            }
            if ($arrayValueType) {
                $t = ReflectionTypeFactory::createReflectionType($arrayValueType);
                $indexes = array_map(function ($index) use ($context, $t) {
                    $result = $context->dynamicCast(substr((string) $index, 0, 255), $t);
                    if ($result instanceof SearchIndex) {
                        $result->parent = $context->storageObject;
                    }
                    return $result;
                }, $indexes);
            }
            $storagePropertyValue = $context->dynamicCast($indexes, $storagePropertyType);
            $context->setStoragePropertyValue($storagePropertyValue);
        }
    }
}
