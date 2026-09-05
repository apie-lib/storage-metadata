<?php
namespace Apie\StorageMetadata\Converters;

use Apie\Core\Utils\ConverterUtils;
use Apie\TypeConverter\ConverterInterface;
use DOMAttr;
use DOMDocument;
use DOMElement;
use ReflectionType;

/**
 * @implements ConverterInterface<?string, DOMAttr|DOMElement|null>
 */
class StringToDom implements ConverterInterface
{
    public function convert(?string $input, ?ReflectionType $wantedType): DOMAttr|DOMElement|null
    {
        if ($input === null && $wantedType?->allowsNull()) {
            return null;
        }
        $class = ConverterUtils::toReflectionClass($wantedType);
        assert($class !== null);
        if ($class->name === DOMAttr::class) {
            if (preg_match('/^([A-Za-z_][A-Za-z0-9_.:-]*)="(.*)"$/s', $input, $matches)) {
                return new DOMAttr($matches[1], htmlspecialchars_decode($matches[2], ENT_XML1));
            }
            return new DOMAttr('value', $input);
        }

        $document = new DOMDocument();
        $document->loadXML($input, LIBXML_NONET);
        return $document->documentElement;
    }
}
