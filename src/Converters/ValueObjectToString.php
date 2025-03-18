<?php
namespace Apie\StorageMetadata\Converters;

use Apie\Core\ValueObjects\Interfaces\ValueObjectInterface;
use Apie\Core\ValueObjects\Utils;
use Apie\TypeConverter\ConverterInterface;
use ReflectionType;

/**
 * @implements ConverterInterface<ValueObjectInterface, string>
 */
class ValueObjectToString implements ConverterInterface
{
    public function convert(ValueObjectInterface $input, ?ReflectionType $wantedType): ?string
    {
        $native = $input->toNative();
        if (null === $native && $wantedType?->allowsNull()) {
            return null;
        }
        return Utils::toString($native);
    }
}
