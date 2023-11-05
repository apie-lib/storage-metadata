<?php
namespace Apie\StorageMetadata\Converters;

use Apie\Core\Utils\ConverterUtils;
use Apie\TypeConverter\ConverterInterface;
use BackedEnum;
use ReflectionType;

/**
 * @implements ConverterInterface<string, BackedEnum>
 */
class StringToEnum implements ConverterInterface
{
    public function convert(string $input, ?ReflectionType $wantedType): ?BackedEnum
    {
        $class = ConverterUtils::toReflectionClass($wantedType);
        assert(null !== $class);
        $className = $class->name;
        if ($wantedType->allowsNull()) {
            // @phpstan-ignore-next-line
            return $className::tryFrom($input);
        }
        // @phpstan-ignore-next-line
        return $className::from($input);
    }
}
