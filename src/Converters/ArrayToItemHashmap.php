<?php
namespace Apie\StorageMetadata\Converters;

use Apie\Core\Lists\ItemHashmap;
use Apie\Core\Utils\ConverterUtils;
use Apie\TypeConverter\ConverterInterface;
use ReflectionType;

/**
 * @template T
 * @implements ConverterInterface<array<string, T>, ItemHashmap<T>>
 */
class ArrayToItemHashmap implements ConverterInterface
{
    /**
     * @param array<string, T> $input
     */
    public function convert(array $input, ?ReflectionType $wantedType): ItemHashmap
    {
        $class = ConverterUtils::toReflectionClass($wantedType);
        assert(null !== $class);
        $className = $class->name;
        return new $className($input);
    }
}
