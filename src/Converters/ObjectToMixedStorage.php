<?php
namespace Apie\StorageMetadata\Converters;

use Apie\Core\Utils\ConverterUtils;
use Apie\StorageMetadataBuilder\Interfaces\MixedStorageInterface;
use Apie\TypeConverter\ConverterInterface;
use ReflectionType;

/**
 * @implements ConverterInterface<object, MixedStorageInterface>
 */
class ObjectToMixedStorage implements ConverterInterface
{
    public function convert(object $input, ?ReflectionType $wantedType): MixedStorageInterface
    {
        $class = ConverterUtils::toReflectionClass($wantedType);
        assert(null !== $class);
        $className = $class->name;
        return new $className($input);
    }
}
