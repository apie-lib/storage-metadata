<?php
namespace Apie\StorageMetadata\Converters;

use Apie\Core\Utils\ConverterUtils;
use Apie\StorageMetadataBuilder\Interfaces\MixedStorageInterface;
use Apie\TypeConverter\ConverterInterface;
use ReflectionType;

/**
 * @implements ConverterInterface<mixed, MixedStorageInterface>
 */
class MixedToMixedStorage implements ConverterInterface
{
    public function convert(mixed $input, ?ReflectionType $wantedType): ?MixedStorageInterface
    {
        $class = ConverterUtils::toReflectionClass($wantedType);
        assert(null !== $class);
        $className = $class->name;
        if ($input === null && $wantedType?->allowsNull()) {
            return null;
        }
        return new $className($input);
    }
}
