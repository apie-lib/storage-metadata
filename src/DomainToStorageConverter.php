<?php
namespace Apie\StorageMetadata;

use Apie\StorageMetadata\ClassInstantiators\ChainedClassInstantiator;
use Apie\StorageMetadata\ClassInstantiators\FromReflection;
use Apie\StorageMetadata\ClassInstantiators\FromStorage;
use Apie\StorageMetadata\Converters\ArrayToItemHashmap;
use Apie\StorageMetadata\Converters\ArrayToItemList;
use Apie\StorageMetadata\Converters\StringToEnum;
use Apie\StorageMetadata\Converters\StringToValueObject;
use Apie\StorageMetadata\Interfaces\ClassInstantiatorInterface;
use Apie\StorageMetadata\Interfaces\PropertyConverterInterface;
use Apie\StorageMetadata\Interfaces\StorageDtoInterface;
use Apie\StorageMetadata\Mediators\DomainToStorageContext;
use Apie\StorageMetadata\PropertyConverters\OneToManyAttributeConverter;
use Apie\StorageMetadata\PropertyConverters\OneToOneAttributeConverter;
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

    /**
     * @template T of object
     * @param T $domainObject
     * @return T
     */
    public function injectExistingDomainObject(
        object $domainObject,
        StorageDtoInterface $storageObject
    ): object {
        $domainClass = $storageObject::getClassReference();
        $typeConverter = new TypeConverter(
            new ObjectToObjectConverter(),
            ...DefaultConvertersFactory::create(
                new StringToValueObject(),
                new StringToEnum(),
                new ArrayToItemHashmap(),
                new ArrayToItemList(),
            )
        );
        $context = new DomainToStorageContext(
            $this,
            $typeConverter,
            $storageObject,
            $domainObject,
            $domainClass
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

    public function createDomainObject(StorageDtoInterface $storageObject): object
    {
        $domainClass = $storageObject::getClassReference();
        
        return $this->injectExistingDomainObject(
            $this->classInstantiator->create($domainClass, $storageObject),
            $storageObject
        );
    }

    public static function create(): self
    {
        return new self(
            new ChainedClassInstantiator(
                new FromStorage(),
                new FromReflection(),
            ),
            new OneToOneAttributeConverter(),
            new OneToManyAttributeConverter(),
            new PropertyAttributeConverter(),
        );
    }
}
