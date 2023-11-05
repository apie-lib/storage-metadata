<?php
namespace Apie\StorageMetadata\Converters;

use Apie\Core\Utils\ConverterUtils;
use Apie\Core\ValueObjects\Interfaces\ValueObjectInterface;
use Apie\TypeConverter\ConverterInterface;
use ReflectionType;

/**
 * @implements ConverterInterface<string, ValueObjectInterface>
 */
class StringToValueObject implements ConverterInterface
{
    public function convert(string $input, ?ReflectionType $wantedType): ValueObjectInterface
    {
        $class = ConverterUtils::toReflectionClass($wantedType);
        assert(null !== $class);
        $className = $class->name;
        // @phpstan-ignore-next-line
        return $className::fromNative($input);
    }
}
