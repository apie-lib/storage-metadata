<?php
namespace Apie\StorageMetadata\Converters;

use Apie\Core\Utils\ConverterUtils;
use Apie\Core\ValueObjects\Interfaces\ValueObjectInterface;
use Apie\TypeConverter\ConverterInterface;
use ReflectionType;

/**
 * @implements ConverterInterface<ValueObjectInterface, string>
 */
class ValueObjectToString implements ConverterInterface
{
    public function convert(ValueObjectInterface $input, ?ReflectionType $wantedType): string
    {
        $class = ConverterUtils::toReflectionClass($wantedType);
        assert(null !== $class);
        return $input->toNative();
    }
}
