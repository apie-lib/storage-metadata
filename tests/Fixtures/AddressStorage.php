<?php
namespace Apie\Tests\StorageMetadata\Fixtures;

use Apie\Fixtures\ValueObjects\AddressWithZipcodeCheck;
use Apie\StorageMetadata\Attributes\ParentAttribute;
use Apie\StorageMetadata\Attributes\PropertyAttribute;
use Apie\StorageMetadata\Interfaces\StorageDtoInterface;
use ReflectionClass;

class AddressStorage implements StorageDtoInterface
{
    public static function getClassReference(): ReflectionClass
    {
        return new ReflectionClass(AddressWithZipcodeCheck::class);
    }

    #[ParentAttribute]
    public UserWithAddressStorage $parent;
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
