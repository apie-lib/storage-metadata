<?php
namespace Apie\StorageMetadata\Converters;

use Apie\TypeConverter\ConverterInterface;
use DOMAttr;
use DOMDocument;
use DOMElement;
use ReflectionType;

/**
 * @implements ConverterInterface<DOMAttr|DOMElement|null, ?string>
 */
class DomToString implements ConverterInterface
{
    public function convert(DOMAttr|DOMElement|null $input, ?ReflectionType $wantedType): ?string
    {
        if ($input === null) {
            return null;
        }
        if ($input instanceof DOMAttr) {
            return $input->name . '="' . htmlspecialchars($input->value, ENT_XML1 | ENT_QUOTES, 'UTF-8') . '"';
        }

        $document = $input->ownerDocument ?? new DOMDocument();
        $node = $input;
        if ($input->ownerDocument === null) {
            $node = $document->importNode($input, true);
            $document->appendChild($node);
        }
        return (string) $document->saveXML($node);
    }
}
