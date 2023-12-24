<?php
namespace Apie\StorageMetadata\Converters;

use Apie\Core\ValueObjects\Interfaces\ValueObjectInterface;
use Apie\StorageMetadata\Interfaces\AutoIncrementTableInterface;
use Apie\TypeConverter\ConverterInterface;
use Apie\TypeConverter\TypeConverter;
use ReflectionType;

/**
 * @implements ConverterInterface<?ValueObjectInterface, AutoIncrementTableInterface>
 */
class ValueObjectToAutoIncrementTable implements ConverterInterface
{
    public function convert(?ValueObjectInterface $input, ?ReflectionType $wantedType, ?TypeConverter $typeConverter = null): AutoIncrementTableInterface
    {
        assert($typeConverter !== null);
        $input = $input->toNative();
        return $typeConverter->convertTo($input, $wantedType);
    }
}
