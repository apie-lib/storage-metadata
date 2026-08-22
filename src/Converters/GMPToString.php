<?php
namespace Apie\StorageMetadata\Converters;

use Apie\TypeConverter\ConverterInterface;
use GMP;

/**
 * @implements ConverterInterface<GMP, string>
 */
class GMPToString implements ConverterInterface
{
    public function convert(GMP $input): string
    {
        return gmp_strval($input);
    }
}
