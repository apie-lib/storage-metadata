<?php
namespace Apie\StorageMetadata;

use Apie\Core\Indexing\Indexer;
use Apie\Core\TypeConverters\ArrayToDoctrineCollection;
use Apie\Core\TypeConverters\DoctrineCollectionToArray;
use Apie\StorageMetadata\ClassInstantiators\ChainedClassInstantiator;
use Apie\StorageMetadata\ClassInstantiators\FromReflection;
use Apie\StorageMetadata\ClassInstantiators\FromStorage;
use Apie\StorageMetadata\Converters\ArrayToItemHashmap;
use Apie\StorageMetadata\Converters\ArrayToItemList;
use Apie\StorageMetadata\Converters\AutoIncrementTableToInt;
use Apie\StorageMetadata\Converters\AutoIncrementTableToValueObject;
use Apie\StorageMetadata\Converters\DateTimeToString;
use Apie\StorageMetadata\Converters\EnumToString;
use Apie\StorageMetadata\Converters\IntToAutoIncrementTable;
use Apie\StorageMetadata\Converters\IntToValueObject;
use Apie\StorageMetadata\Converters\MixedStorageToObject;
use Apie\StorageMetadata\Converters\MixedToMixedStorage;
use Apie\StorageMetadata\Converters\StringToDateTime;
use Apie\StorageMetadata\Converters\StringToEnum;
use Apie\StorageMetadata\Converters\StringToSearchIndex;
use Apie\StorageMetadata\Converters\StringToValueObject;
use Apie\StorageMetadata\Converters\ValueObjectToAutoIncrementTable;
use Apie\StorageMetadata\Converters\ValueObjectToFloat;
use Apie\StorageMetadata\Converters\ValueObjectToInt;
use Apie\StorageMetadata\Converters\ValueObjectToString;
use Apie\StorageMetadata\Interfaces\ClassInstantiatorInterface;
use Apie\StorageMetadata\Interfaces\PropertyConverterInterface;
use Apie\StorageMetadata\Interfaces\StorageDtoInterface;
use Apie\StorageMetadata\Mediators\DomainToStorageContext;
use Apie\StorageMetadata\PropertyConverters\DefaultValueAttributeConverter;
use Apie\StorageMetadata\PropertyConverters\DiscriminatorMappingAttributeConverter;
use Apie\StorageMetadata\PropertyConverters\GetSearchIndexAttributeConverter;
use Apie\StorageMetadata\PropertyConverters\ManyToOneAttributeConverter;
use Apie\StorageMetadata\PropertyConverters\MethodAttributeConverter;
use Apie\StorageMetadata\PropertyConverters\OneToManyAttributeConverter;
use Apie\StorageMetadata\PropertyConverters\OneToOneAttributeConverter;
use Apie\StorageMetadata\PropertyConverters\OrderAttributeConverter;
use Apie\StorageMetadata\PropertyConverters\ParentAttributeConverter;
use Apie\StorageMetadata\PropertyConverters\PropertyAttributeConverter;
use Apie\TypeConverter\Converters\ObjectToObjectConverter;
use Apie\TypeConverter\DefaultConvertersFactory;
use Apie\TypeConverter\TypeConverter;
use ReflectionClass;
use ReflectionProperty;

class DomainToStorageConverter
{
    /** @var array<int, PropertyConverterInterface> */
    private array $propertyConverters;

    public function __construct(
        private readonly ClassInstantiatorInterface $classInstantiator,
        PropertyConverterInterface... $propertyConverters
    ) {
        $this->propertyConverters = $propertyConverters;
    }

    private function createTypeConverter(): TypeConverter
    {
        return new TypeConverter(
            new ObjectToObjectConverter(),
            ...DefaultConvertersFactory::create(
                new ArrayToDoctrineCollection(),
                new StringToSearchIndex(),
                new DoctrineCollectionToArray(),
                new AutoIncrementTableToInt(),
                new AutoIncrementTableToValueObject(),
                new IntToAutoIncrementTable(),
                new ValueObjectToAutoIncrementTable(),
                new ValueObjectToInt(),
                new IntToValueObject(),
                new ValueObjectToFloat(),
                new MixedStorageToObject(),
                new MixedToMixedStorage(),
                new ValueObjectToString(),
                new EnumToString(),
                new StringToDateTime(),
                new DateTimeToString(),
                new StringToValueObject(),
                new StringToEnum(),
                new ArrayToItemHashmap(),
                new ArrayToItemList(),
            )
        );
    }

    /**
     * @template T of object
     * @param T $domainObject
     * @return T
     */
    public function injectExistingDomainObject(
        object $domainObject,
        StorageDtoInterface $storageObject,
        ?DomainToStorageContext $context = null
    ): object {
        $domainClass = $storageObject::getClassReference();
        $typeConverter = $this->createTypeConverter();
        $context = DomainToStorageContext::createFromContext(
            $this,
            $typeConverter,
            $storageObject,
            $domainObject,
            $domainClass,
            $context
        );
        $ptr = new ReflectionClass($storageObject);
        $filters = null;
        while ($ptr) {
            foreach ($ptr->getProperties($filters) as $storageProperty) {
                if ($storageProperty->isStatic()) {
                    continue;
                }
                $propertyContext = $context->withStorageProperty($storageProperty);
                foreach ($this->propertyConverters as $propertyConverter) {
                    $propertyConverter->applyToDomain($propertyContext);
                }
            }
            $ptr = $ptr->getParentClass();
            // parent classes only add private properties
            $filters = ReflectionProperty::IS_PRIVATE;
        }

        return $domainObject;
    }

    public function createDomainObject(StorageDtoInterface $storageObject, ?DomainToStorageContext $context = null): object
    {
        $domainClass = $storageObject::getClassReference();
        
        return $this->injectExistingDomainObject(
            $this->classInstantiator->create($domainClass, $storageObject),
            $storageObject,
            $context
        );
    }

    /**
     * @template T of StorageDtoInterface
     * @param ReflectionClass<T> $targetClass
     * @return T
     */
    public function createStorageObject(
        object $input,
        ReflectionClass $targetClass,
        ?DomainToStorageContext $context = null
    ): StorageDtoInterface {
        return $this->injectExistingStorageObject(
            $input,
            $this->classInstantiator->create($targetClass),
            $context
        );
    }

    public function injectExistingStorageObject(
        object $domainObject,
        StorageDtoInterface $storageObject,
        ?DomainToStorageContext $context = null
    ): StorageDtoInterface {
        $domainClass = $storageObject::getClassReference();
        $filters = null;
        $ptr = new ReflectionClass($storageObject);
        $typeConverter = $this->createTypeConverter();
        $context = DomainToStorageContext::createFromContext(
            $this,
            $typeConverter,
            $storageObject,
            $domainObject,
            $domainClass,
            $context
        );
        while ($ptr) {
            foreach ($ptr->getProperties($filters) as $storageProperty) {
                if ($storageProperty->isStatic()) {
                    continue;
                }
                $propertyContext = $context->withStorageProperty($storageProperty);
                foreach ($this->propertyConverters as $propertyConverter) {
                    $propertyConverter->applyToStorage($propertyContext);
                }
            }
            $ptr = $ptr->getParentClass();
            // parent classes only add private properties
            $filters = ReflectionProperty::IS_PRIVATE;
        }
        return $storageObject;
    }

    public static function create(?Indexer $indexer = null): self
    {
        return new self(
            new ChainedClassInstantiator(
                new FromStorage(),
                new FromReflection(),
            ),
            new DiscriminatorMappingAttributeConverter(),
            new ManyToOneAttributeConverter(),
            new OneToOneAttributeConverter(),
            new OneToManyAttributeConverter(),
            new PropertyAttributeConverter(),
            new GetSearchIndexAttributeConverter($indexer ?? Indexer::create()),
            new MethodAttributeConverter(),
            new OrderAttributeConverter(),
            new ParentAttributeConverter(),
            new DefaultValueAttributeConverter(),
        );
    }
}
