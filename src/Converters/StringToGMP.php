<?php
namespace Apie\StorageMetadata\Converters;

use Apie\TypeConverter\ConverterInterface;
use GMP;

/**
 * @implements ConverterInterface<string, GMP>
 */
class StringToGMP implements ConverterInterface
{
    public function convert(string $input): GMP
    {
        return new GMP($input);
    }
}
