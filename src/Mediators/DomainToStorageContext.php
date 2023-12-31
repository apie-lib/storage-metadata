<?php
namespace Apie\StorageMetadata\Mediators;

use Apie\Core\Utils\ConverterUtils;
use Apie\StorageMetadata\DomainToStorageConverter;
use Apie\StorageMetadata\Interfaces\StorageDtoInterface;
use Apie\TypeConverter\TypeConverter;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionType;

final class DomainToStorageContext
{
    /**
     * @var ReflectionClass<object>
     */
    public readonly ?ReflectionClass $domainClass;

    public readonly ReflectionProperty $storageProperty;

    /**
     * @template T of object
     * @param T $domainObject
     * @param ReflectionClass<T>|null $domainClass
     */
    public function __construct(
        public readonly DomainToStorageConverter $domainToStorageConverter,
        public readonly TypeConverter $typeConverter,
        public readonly StorageDtoInterface $storageObject,
        public readonly object $domainObject,
        ?ReflectionClass $domainClass = null
    ) {
        $this->domainClass = $domainClass ?? new ReflectionClass($domainObject);
    }

    public function withStorageProperty(ReflectionProperty $storageProperty): self
    {
        // we do this to throw an exception in case an incorrect property is entered here.
        $storageProperty->isInitialized($this->storageObject);
        return $this->clone(['storageProperty' => $storageProperty]);
    }

    public function getStoragePropertyValue(): mixed
    {
        if (!$this->storageProperty->isInitialized($this->storageObject)) {
            return $this->storageProperty->hasDefaultValue() ? $this->storageProperty->getDefaultValue() : null;
        }
        return $this->storageProperty->getValue($this->storageObject);
    }

    public function dynamicCast(mixed $input, ?ReflectionType $wantedType): mixed
    {
        if (!$wantedType) {
            return $input;
        }
        if ($input === null && $wantedType->allowsNull()) {
            return null;
        }
        if (is_object($input)) {
            $class = ConverterUtils::toReflectionClass($wantedType);
            if ($class->isInstance($input)) {
                return $input;
            }
        } elseif ($wantedType instanceof ReflectionNamedType && $wantedType->getName() === get_debug_type($input)) {
            return $input;
        }
        return $this->typeConverter->convertTo($input, $wantedType);
    }

    /**
     * @param array<string, mixed> $altered
     */
    private function clone(array $altered): self
    {
        $properties = get_object_vars($this) + $altered;
        $clone = (new ReflectionClass($this))->newInstanceWithoutConstructor();
        foreach ($properties as $propertyName => $propertyValue) {
            $clone->$propertyName = $propertyValue;
        }

        return $clone;
    }
}
