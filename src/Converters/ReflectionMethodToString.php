<?php
namespace Apie\StorageMetadata\Converters;

use Apie\TypeConverter\ConverterInterface;
use ReflectionMethod;

/**
 * @implements ConverterInterface<ReflectionMethod|null, string|null>
 */
class ReflectionMethodToString implements ConverterInterface
{
    public function convert(?ReflectionMethod $input): ?string
    {
        if ($input === null) {
            return null;
        }
        return $input->getDeclaringClass()->name . '::' . $input->getName();
    }
}
