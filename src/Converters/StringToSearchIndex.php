<?php
namespace Apie\StorageMetadata\Converters;

use Apie\Core\Utils\ConverterUtils;
use Apie\DoctrineEntityConverter\Entities\SearchIndex;
use Apie\TypeConverter\ConverterInterface;
use ReflectionType;

/**
 * @implements ConverterInterface<string, SearchIndex>
 */
class StringToSearchIndex implements ConverterInterface
{
    public function convert(string $input, ?ReflectionType $wantedType): SearchIndex
    {
        $class = ConverterUtils::toReflectionClass($wantedType);
        assert(null !== $class);
        $className = $class->name;
        $instance = new $className;
        $instance->value = $input;
        return $instance;
    }
}