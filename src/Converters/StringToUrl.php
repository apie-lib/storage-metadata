<?php
namespace Apie\StorageMetadata\Converters;

use Apie\TypeConverter\ConverterInterface;
use Uri\Rfc3986\Uri;

/**
 * @implements ConverterInterface<string, Uri>
 */
class StringToUrl implements ConverterInterface
{
    public function convert(string $input): Uri
    {
        return new Uri($input);
    }
}
