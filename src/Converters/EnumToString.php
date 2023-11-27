<?php
namespace Apie\StorageMetadata\Converters;

use Apie\Core\Utils\ConverterUtils;
use Apie\Core\ValueObjects\Interfaces\ValueObjectInterface;
use Apie\TypeConverter\ConverterInterface;
use BackedEnum;
use ReflectionType;

/**
 * @implements ConverterInterface<BackedEnum, string>
 */
class EnumToString implements ConverterInterface
{
    public function convert(BackedEnum $input, ?ReflectionType $wantedType): string
    {
        return $input->value;
    }
}
