<?php
namespace Apie\StorageMetadata\Converters;

use Apie\TypeConverter\ConverterInterface;
use DateTimeInterface;
use ReflectionType;

/**
 * @implements ConverterInterface<DateTimeInterface, string>
 */
class DateTimeToString implements ConverterInterface
{
    public function convert(?DateTimeInterface $input, ?ReflectionType $wantedType): ?string
    {
        if ($input === null && $wantedType->allowsNull()) {
            return null;
        }
        return $input->format(DateTimeInterface::ATOM);
    }
}
