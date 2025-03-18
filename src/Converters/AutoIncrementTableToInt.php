<?php
namespace Apie\StorageMetadata\Converters;

use Apie\StorageMetadata\Interfaces\AutoIncrementTableInterface;
use Apie\TypeConverter\ConverterInterface;
use ReflectionType;

/**
 * @implements ConverterInterface<AutoIncrementTableInterface, ?int>
 */
class AutoIncrementTableToInt implements ConverterInterface
{
    public function convert(AutoIncrementTableInterface $input, ?ReflectionType $wantedType): ?int
    {
        return $input->getKey();
    }
}
