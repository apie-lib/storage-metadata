<?php
namespace Apie\StorageMetadata\Converters;

use Apie\Core\Lists\ItemList;
use Apie\Core\TypeUtils;
use Apie\Core\Utils\ConverterUtils;
use Apie\Core\Utils\HashmapUtils;
use Apie\TypeConverter\ConverterInterface;
use Apie\TypeConverter\TypeConverter;
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
    public function convert(array $input, ?ReflectionType $wantedType, ?TypeConverter $typeConverter = null): ItemList
    {
        $class = ConverterUtils::toReflectionClass($wantedType);
        assert(null !== $class);
        $className = $class->name;
        $arrayType = HashmapUtils::getArrayType($class);
        if ($typeConverter !== null) {
            $input = array_map(
                function ($val) use ($typeConverter, $arrayType) {
                    if (TypeUtils::matchesType($arrayType, $val)) {
                        return $val;
                    }
                    return $typeConverter->convertTo($val, $arrayType);
                },
                $input
            );
        }
        return new $className($input);
    }
}
