<?php
namespace Apie\StorageMetadata\Converters;

use Apie\Core\ValueObjects\Interfaces\ValueObjectInterface;
use Apie\StorageMetadata\Interfaces\AutoIncrementTableInterface;
use Apie\TypeConverter\ConverterInterface;
use Apie\TypeConverter\TypeConverter;
use ReflectionType;

/**
 * @implements ConverterInterface<AutoIncrementTableInterface, ?ValueObjectInterface>
 */
class AutoIncrementTableToValueObject implements ConverterInterface
{
    public function convert(AutoIncrementTableInterface $input, ?ReflectionType $wantedType, ?TypeConverter $typeConverter = null): ?ValueObjectInterface
    {
        assert($typeConverter !== null);
        assert($wantedType !== null);
        $key = $input->getKey();
        if ($key === null && $wantedType->allowsNull()) {
            return null;
        }
        return $typeConverter->convertTo($key, $wantedType);
    }
}
