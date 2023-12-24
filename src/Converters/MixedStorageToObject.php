<?php
namespace Apie\StorageMetadata\Converters;

use Apie\StorageMetadataBuilder\Interfaces\MixedStorageInterface;
use Apie\TypeConverter\ConverterInterface;
use ReflectionType;

/**
 * @implements ConverterInterface<mixed, MixedStorageInterface>
 */
class MixedStorageToObject implements ConverterInterface
{
    public function convert(MixedStorageInterface $input, ?ReflectionType $wantedType): mixed
    {
        return $input->toOriginalObject();
    }
}
