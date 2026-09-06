<?php
namespace Apie\StorageMetadata\Converters;

use Apie\Core\Utils\ConverterUtils;
use Apie\TypeConverter\ConverterInterface;
use ReflectionType;

/**
 * @implements ConverterInterface<?string, \SimpleXMLElement|null>
 */
class StringToSimpleXml implements ConverterInterface
{
    public function convert(?string $input, ?ReflectionType $wantedType): \SimpleXMLElement|null
    {
        if ($input === null && $wantedType?->allowsNull()) {
            return null;
        }
        $class = ConverterUtils::toReflectionClass($wantedType);
        assert($class !== null);
        return simplexml_load_string($input, $class->name, LIBXML_NONET);
    }
}
