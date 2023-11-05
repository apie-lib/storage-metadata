<?php
namespace Apie\StorageMetadata\Interfaces;

use Apie\Core\Dto\DtoInterface;
use ReflectionClass;

interface StorageDtoInterface extends DtoInterface
{
    /**
     * @return ReflectionClass<object>
     */
    public static function getClassReference(): ReflectionClass;
}
