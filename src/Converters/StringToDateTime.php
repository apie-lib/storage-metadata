<?php
namespace Apie\StorageMetadata\Converters;

use Apie\Core\Utils\ConverterUtils;
use Apie\TypeConverter\ConverterInterface;
use DateTime;
use DateTimeInterface;
use ReflectionType;

/**
 * @implements ConverterInterface<string, DateTimeInterface>
 */
class StringToDateTime implements ConverterInterface
{
    public function convert(string $input, ?ReflectionType $wantedType): ?DateTimeInterface
    {
        $class = ConverterUtils::toReflectionClass($wantedType);
        assert(null !== $class);
        $className = $class->name;
        return match($className) {
            DateTimeInterface::class => new DateTime($input),
            default => new $className($input),
        };
    }
}
