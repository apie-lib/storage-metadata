<?php
namespace Apie\StorageMetadata\Converters;

use Apie\Core\Utils\ConverterUtils;
use Apie\Core\ValueObjects\Interfaces\ValueObjectInterface;
use Apie\Core\ValueObjects\Utils;
use Apie\TypeConverter\ConverterInterface;
use ReflectionType;

/**
 * @implements ConverterInterface<ValueObjectInterface, int|null>
 */
class ValueObjectToInt implements ConverterInterface
{
    public function convert(ValueObjectInterface $input, ?ReflectionType $wantedType): ?int
    {
        if (null === $native && $wantedType?->allowsNull()) {
            return null;
        }
        return Utils::toInt($native);
    }
}
