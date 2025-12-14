<?php
namespace Apie\StorageMetadata\Converters;

use Apie\TypeConverter\ConverterInterface;
use DateTimeImmutable;
use DateTimeInterface;
use ReflectionType;

/**
 * @implements ConverterInterface<DateTimeInterface, DateTimeImmutable>
 */
class DateTimeToDateTimeImmutable implements ConverterInterface
{
    public function convert(?DateTimeInterface $input, ?ReflectionType $wantedType): ?DateTimeImmutable
    {
        if ($input === null && $wantedType->allowsNull()) {
            return null;
        }
        return DateTimeImmutable::createFromInterface($input);
    }
}
