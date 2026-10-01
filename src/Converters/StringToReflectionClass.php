<?php
namespace Apie\StorageMetadata\Converters;

use Apie\TypeConverter\ConverterInterface;
use ReflectionClass;

/**
 * @template T of object
 * @implements ConverterInterface<class-string<T>, ReflectionClass<T>>
 */
class StringToReflectionClass implements ConverterInterface
{
    /**
     * @template U of object
     * @param class-string<U> $input
     * @return ReflectionClass<U>
     */
    public function convert(string $input): ReflectionClass
    {
        return new ReflectionClass($input);
    }
}
