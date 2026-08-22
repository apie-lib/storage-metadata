<?php
namespace Apie\StorageMetadata\Converters;

use Apie\TypeConverter\ConverterInterface;
use BcMath\Number;

/**
 * @implements ConverterInterface<Number, string>
 */
class BcMathToString implements ConverterInterface
{
    public function convert(Number $input): string
    {
        return $input->__toString();
    }
}
