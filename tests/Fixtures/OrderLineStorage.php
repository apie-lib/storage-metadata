<?php
namespace Apie\Tests\StorageMetadata\Fixtures;

use Apie\Fixtures\Entities\OrderLine;
use Apie\StorageMetadata\Attributes\OrderAttribute;
use Apie\StorageMetadata\Attributes\ParentAttribute;
use Apie\StorageMetadata\Attributes\PropertyAttribute;
use Apie\StorageMetadata\Interfaces\StorageDtoInterface;
use ReflectionClass;

class OrderLineStorage implements StorageDtoInterface
{
    public static function getClassReference(): ReflectionClass
    {
        return new ReflectionClass(OrderLine::class);
    }

    #[ParentAttribute]
    public OrderStorage $parent;

    public function __construct(
        #[PropertyAttribute('id')]
        public ?string $apieId,
        #[OrderAttribute]
        public int $order
    ) {
    }
}
