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
    public function convert(ValueObjectInterface $input, ?ReflectionType $wantedType): ?float
    {
        $native = $input->toNative();
        if (null === $native && $wantedType?->allowsNull()) {
            return null;
        }
        return Utils::toFloat($native);
    }
}
