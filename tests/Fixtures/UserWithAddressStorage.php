<?php
namespace Apie\Tests\StorageMetadata\Fixtures;

use Apie\Fixtures\Entities\UserWithAddress;
use Apie\StorageMetadata\Attributes\OneToOneAttribute;
use Apie\StorageMetadata\Attributes\PropertyAttribute;
use Apie\StorageMetadata\Interfaces\StorageDtoInterface;
use ReflectionClass;
use SensitiveParameter;

class UserWithAddressStorage implements StorageDtoInterface
{
    public static function getClassReference(): ReflectionClass
    {
        return new ReflectionClass(UserWithAddress::class);
    }
    public function __construct(
        #[PropertyAttribute('id')]
        public string $apieId,
        #[OneToOneAttribute('address')]
        public AddressStorage $apieAddress,
        #[PropertyAttribute('password')]
        #[SensitiveParameter]
        public ?string $apiePassword = null,
    ) {
        $this->apieAddress->parent = $this;
    }
}
