<?php
namespace Apie\Tests\StorageMetadata\Fixtures;

use Apie\Fixtures\Entities\Order;
use Apie\StorageMetadata\Attributes\GetMethodAttribute;
use Apie\StorageMetadata\Attributes\GetSearchIndexAttribute;
use Apie\StorageMetadata\Attributes\OneToManyAttribute;
use Apie\StorageMetadata\Attributes\PropertyAttribute;
use Apie\StorageMetadata\Interfaces\StorageDtoInterface;
use ReflectionClass;

class OrderStorage implements StorageDtoInterface
{
    #[GetSearchIndexAttribute('getOrderLines')]
    public array $searchOrderLines;

    public static function getClassReference(): ReflectionClass
    {
        return new ReflectionClass(Order::class);
    }

    public function __construct(
        #[GetMethodAttribute('getId')]
        private string $id,
        #[PropertyAttribute('id')]
        public string $apieId,
        #[PropertyAttribute('orderStatus')]
        public string $apieOrderStatus,
        #[OneToManyAttribute(propertyName: 'orderLines', storageClass: OrderLineStorage::class)]
        public array $apieOrderLines,
    ) {
        foreach ($apieOrderLines as $apieOrderLine) {
            $apieOrderLine->parent = $this;
        }
    }
}
