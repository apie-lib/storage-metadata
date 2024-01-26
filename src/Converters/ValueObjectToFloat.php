<?php
namespace Apie\StorageMetadata\Converters;

use Apie\Core\Utils\ConverterUtils;
use Apie\Core\ValueObjects\Interfaces\ValueObjectInterface;
use Apie\Core\ValueObjects\Utils;
use Apie\TypeConverter\ConverterInterface;
use ReflectionType;

/**
 * @implements ConverterInterface<ValueObjectInterface, float>
 */
class ValueObjectToFloat implements ConverterInterface
{
    public function convert(ValueObjectInterface $input, ?ReflectionType $wantedType): float
    {
        $class = ConverterUtils::toReflectionClass($wantedType);
        return Utils::toFloat($input->toNative());
    }
}
