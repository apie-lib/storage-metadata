<?php
namespace Apie\StorageMetadata\Converters;

use Apie\TypeConverter\ConverterInterface;

/**
 * @implements ConverterInterface<\SimpleXMLElement|null, ?string>
 */
class SimpleXmlToString implements ConverterInterface
{
    public function convert(\SimpleXMLElement|null $input): ?string
    {
        if ($input === null) {
            return null;
        }

        return $input->asXML();
    }
}
