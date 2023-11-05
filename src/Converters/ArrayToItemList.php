<?php
namespace Apie\StorageMetadata\Converters;

use Apie\Core\Lists\ItemList;
use Apie\Core\Utils\ConverterUtils;
use Apie\TypeConverter\ConverterInterface;
use ReflectionType;

/**
 * @template T
 * @implements ConverterInterface<array<int, T>, ItemList<T>>
 */
class ArrayToItemList implements ConverterInterface
{
    /**
     * @param array<int, T> $input
     */
    public function convert(array $input, ?ReflectionType $wantedType): ItemList
    {
        $class = ConverterUtils::toReflectionClass($wantedType);
        assert(null !== $class);
        $className = $class->name;
        return new $className($input);
    }
}
