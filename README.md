<img src="https://raw.githubusercontent.com/apie-lib/apie-lib-monorepo/main/docs/apie-logo.svg" width="100px" align="left" />
<h1>storage-metadata</h1>






 [![Latest Stable Version](https://poser.pugx.org/apie/storage-metadata/v)](https://packagist.org/packages/apie/storage-metadata) [![Total Downloads](https://poser.pugx.org/apie/storage-metadata/downloads)](https://packagist.org/packages/apie/storage-metadata) [![Latest Unstable Version](https://poser.pugx.org/apie/storage-metadata/v/unstable)](https://packagist.org/packages/apie/storage-metadata) [![License](https://poser.pugx.org/apie/storage-metadata/license)](https://packagist.org/packages/apie/storage-metadata) [![PHP Composer](https://apie-lib.github.io/projectCoverage/coverage-storage-metadata.svg)](https://apie-lib.github.io/projectCoverage/storage-metadata/index.html)  

[![PHP Composer](https://github.com/apie-lib/storage-metadata/actions/workflows/php.yml/badge.svg?event=push)](https://github.com/apie-lib/storage-metadata/actions/workflows/php.yml)

This package is part of the [Apie](https://github.com/apie-lib) library.
The code is maintained in a monorepo, so PR's need to be sent to the [monorepo](https://github.com/apie-lib/apie-lib-monorepo/pulls)

## Documentation
This package is used to convert an Apie domain object to a storage DTO that can be used by data mapper ORM's. The creation of a storage DTO can be done by hand or created automatically by the package [apie/storage-metadata-builder](https://packagist.org/packages/apie/storage-metadata-builder).

### Usage
The simplest usage is using the simple static create method. In case you want to customize it you can create the object yourself.

```php
use Apie\Core\FileStorage\FileStorageFactory;
use Apie\StorageMetadata\DomainToStorageConverter;

DomainToStorageConverter::create(FileStorageFactory::create());
```
A file storage service is required to store properties with typehints of UploadedFileInterface or StoredFile, since you do not want to store your files in the database (this is still possible with InlineStorage, but that should only be used for testing).

An Indexer service can also be provided for how indexing search indexes and columns.

You need a domain object and a storage DTO. For example this could be how your storage DTO looks like for an Address object that is part of an User object:

```php
use Apie\StorageMetadata\Attributes\ParentAttribute;
use Apie\StorageMetadata\Attributes\PropertyAttribute;
use Apie\StorageMetadata\Interfaces\StorageDtoInterface;
use ReflectionClass;

class AddressStorage implements StorageDtoInterface
{
    public static function getClassReference(): ReflectionClass
    {
        return new ReflectionClass(Address::class);
    }

    #[ParentAttribute]
    public User $parent;
    public function __construct(
        #[PropertyAttribute('street')]
        public ?string $apieStreet,
        #[PropertyAttribute('streetNumber')]
        public ?string $apieStreetNumber,
        #[PropertyAttribute('zipcode')]
        public ?string $apieZipcode,
        #[PropertyAttribute('city')]
        public ?string $apieCity,
        #[PropertyAttribute('manual')]
        public ?bool $apieManual,
    ) {
    }
}
```

Then you can convert a domain object to a storage object or vice versa. It's also possible to inject in existing records.

```php
use Apie\Core\FileStorage\FileStorageFactory;
use Apie\StorageMetadata\DomainToStorageConverter;

$converter = DomainToStorageConverter::create(FileStorageFactory::create());
$domainObject = $converter->createDomainObject(
    new AddressStorage(
        'Evergreen Terrace',
        '742',
        '11111',
        'Springfield',
        false
    )
);
```
This example creates a Address domain object with street, zipcode, city and manual property filled in.
### Polymorphic resources
Polymorphic resources are mapped as well, but you need to provide a second argument to PropertyAttribute  to define the mapped class. Basically if the property is not mapped to this domain object null will be stored in the column.

```php
use Apie\Core\Utils\EntityUtils;
use Apie\StorageMetadata\Attributes\DiscriminatorMappingAttribute;
use Apie\StorageMetadata\Attributes\GetMethodAttribute;
use Apie\StorageMetadata\Attributes\PropertyAttribute;
use Apie\StorageMetadata\Interfaces\StorageClassInstantiatorInterface;

class AnimalStorage implements StorageClassInstantiatorInterface
{
    public function __construct(
        #[DiscriminatorMappingAttribute]
        private array $discriminatorMapping,
        #[GetMethodAttribute('getId')]
        private string $id,
        #[PropertyAttribute('id')]
        private ?string $apieId,
        #[PropertyAttribute('hasMilk', Cow::class)]
        private ?bool $apieHasMilk = null,
        #[PropertyAttribute('starving', Elephant::class)]
        private ?bool $apieStarving = null,
        #[PropertyAttribute('poisonous', Fish::class)]
        private ?bool $apiePoisonous = null
    ) {
    }

    public static function getClassReference(): ReflectionClass
    {
        return new ReflectionClass(Animal::class);
    }

    public function createDomainObject(ReflectionClass $class): object
    {
        $class = EntityUtils::findClass($this->discriminatorMapping, $class);
        assert(null !== $class);
        return $class->newInstanceWithoutConstructor();
    }
}
```
This example also shows how to make the domain object first as you can not instantiate a base abstract class like without knowing how to map it (this works with the DiscriminatorMappingAttribute attribute).