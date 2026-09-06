<?php
namespace Apie\StorageMetadata\Converters;

use Apie\TypeConverter\ConverterInterface;
use Uri\Rfc3986\Uri;

/**
 * @implements ConverterInterface<Uri, string>
 */
class UriToString implements ConverterInterface
{
    public function convert(Uri $input): string
    {
        return $input->toString();
    }
}
