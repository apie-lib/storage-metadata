<?php
namespace Apie\StorageMetadata\Converters;

use Apie\StorageMetadataBuilder\Interfaces\MixedStorageInterface;
use Apie\TypeConverter\ConverterInterface;

/**
 * @implements ConverterInterface<MixedStorageInterface|null, mixed>
 */
class MixedStorageToObject implements ConverterInterface
{
    public function convert(?MixedStorageInterface $input): mixed
    {
        return $input?->toOriginalObject();
    }
}
