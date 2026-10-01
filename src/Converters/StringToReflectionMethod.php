<?php
namespace Apie\StorageMetadata\Converters;

use Apie\TypeConverter\ConverterInterface;
use ReflectionMethod;

/**
 * @implements ConverterInterface<string, ReflectionMethod>
 */
class StringToReflectionMethod implements ConverterInterface
{
    public function convert(string $input): ReflectionMethod
    {
        return ReflectionMethod::createFromMethodName($input);
    }
}
