<?php
namespace Apie\StorageMetadata\Converters;

use Apie\TypeConverter\ConverterInterface;
use BcMath\Number;

/**
 * @implements ConverterInterface<string, Number>
 */
class StringToBcMath implements ConverterInterface
{
    public function convert(string $input): Number
    {
        return new Number($input);
    }
}
