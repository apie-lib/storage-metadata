<?php
namespace Apie\StorageMetadata;

use Apie\Core\FileStorage\ChainedFileStorage;
use Apie\Core\FileStorage\StoredFile;
use Apie\Core\Indexing\Indexer;
use Apie\StorageMetadata\ClassInstantiators\ChainedClassInstantiator;
use Apie\StorageMetadata\ClassInstantiators\FromReflection;
use Apie\StorageMetadata\ClassInstantiators\FromStorage;
use Apie\StorageMetadata\ClassInstantiators\FromStoredFile;
use Apie\StorageMetadata\Interfaces\ClassInstantiatorInterface;
use Apie\StorageMetadata\Interfaces\PropertyConverterInterface;
use Apie\StorageMetadata\Interfaces\StorageDtoInterface;
use Apie\StorageMetadata\Mediators\DomainToStorageContext;
use Apie\StorageMetadata\PropertyConverters\AccessControlListAttributeConverter;
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
use Apie\StorageMetadata\PropertyConverters\StorageMappingAttributeConverter;
use Apie\TypeConverter\TypeConverter;
use Psr\Http\Message\UploadedFileInterface;
use ReflectionClass;
use ReflectionProperty;

class DomainToStorageConverter
{
    /** @var array<int, PropertyConverterInterface> */
    private array $propertyConverters;

    public function __construct(
        private readonly ClassInstantiatorInterface $classInstantiator,
        private readonly ChainedFileStorage $fileStorage,
        PropertyConverterInterface... $propertyConverters
    ) {
        $this->propertyConverters = $propertyConverters;
    }

    private function createTypeConverter(): TypeConverter
    {
        return TypeConverterFactory::create($this->fileStorage);
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
            $this->fileStorage,
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

    /**
     * @template T of object
     * @param T $domainObject
     * @param ReflectionClass<T> $reflectionClass
     */

    private function fixFileUploads(object $domainObject, ReflectionClass $reflectionClass): void
    {
        foreach ($reflectionClass->getProperties() as $property) {
            if ($property->isStatic()) {
                continue;
            }
            $value = $property->getValue($domainObject);
            if ($value instanceof UploadedFileInterface && !($value instanceof StoredFile)) {
                $storedFile = StoredFile::createFromUploadedFile($value);
                $property->setValue($domainObject, $storedFile);
            }
        }
        $parentClass = $reflectionClass->getParentClass();
        if ($parentClass) {
            $this->fixFileUploads($domainObject, $parentClass);    
        }
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
            $this->fileStorage,
            $domainClass,
            $context
        );
        $this->fixFileUploads($domainObject, new ReflectionClass($domainObject));
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

    public static function create(ChainedFileStorage $fileStorage, ?Indexer $indexer = null): self
    {
        return new self(
            new ChainedClassInstantiator(
                new FromStoredFile(),
                new FromStorage(),
                new FromReflection(),
            ),
            $fileStorage,
            new DiscriminatorMappingAttributeConverter(),
            new StorageMappingAttributeConverter(),
            new ManyToOneAttributeConverter(),
            new OneToOneAttributeConverter(),
            new AccessControlListAttributeConverter(),
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
