<?php
namespace Apie\StorageMetadata\Converters;

use Apie\TypeConverter\ConverterInterface;
use ReflectionClass;

/**
 * @template T of object
 * @implements ConverterInterface<ReflectionClass<T>|null, class-string<T>|null>
 */
class ReflectionClassToString implements ConverterInterface
{
    /**
     * @template U of object
     * @param ReflectionClass<U>|null $input
     * @return class-string<U>|null
     */
    public function convert(?ReflectionClass $input): ?string
    {
        return $input?->name;
    }
}
