<?php
namespace Apie\StorageMetadata\Converters;

use Apie\Core\Utils\ConverterUtils;
use Apie\StorageMetadata\Interfaces\AutoIncrementTableInterface;
use Apie\TypeConverter\ConverterInterface;
use ReflectionType;

/**
 * @implements ConverterInterface<?int, AutoIncrementTableInterface>
 */
class IntToAutoIncrementTable implements ConverterInterface
{
    public function convert(?int $input, ?ReflectionType $wantedType): AutoIncrementTableInterface
    {
        $class = ConverterUtils::toReflectionClass($wantedType);
        assert($class !== null);
        $instance = $class->newInstanceWithoutConstructor();
        $instance->id = $input;
        return $instance;
    }
}
