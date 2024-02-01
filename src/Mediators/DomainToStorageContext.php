<?php
namespace Apie\StorageMetadata\Mediators;

use Apie\Core\TypeUtils;
use Apie\Core\Utils\ConverterUtils;
use Apie\StorageMetadata\DomainToStorageConverter;
use Apie\StorageMetadata\Exceptions\CouldNotCastPropertyException;
use Apie\StorageMetadata\Interfaces\StorageDtoInterface;
use Apie\StorageMetadataBuilder\Interfaces\MixedStorageInterface;
use Apie\TypeConverter\TypeConverter;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionType;
use Throwable;

final class DomainToStorageContext
{
    /**
     * @var ReflectionClass<object>
     */
    public readonly ?ReflectionClass $domainClass;

    public readonly ReflectionProperty $storageProperty;

    public readonly int|string $arrayKey;

    public readonly ?DomainToStorageContext $parentContext;

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

    /**
     * @template T of object
     * @param T $domainObject
     * @param ReflectionClass<T>|null $domainClass
     */
    public static function createFromContext(
        DomainToStorageConverter $domainToStorageConverter,
        TypeConverter $typeConverter,
        StorageDtoInterface $storageObject,
        object $domainObject,
        ?ReflectionClass $domainClass = null,
        ?DomainToStorageContext $context = null,
    ): self {
        $res = new self($domainToStorageConverter, $typeConverter, $storageObject, $domainObject, $domainClass);
        $fields = [
            'parentContext' => $context,
        ];

        if (isset($context->arrayKey)) {
            $fields['arrayKey'] = $context->arrayKey;
        }

        return $res->clone($fields);
    }

    public function withStorageProperty(ReflectionProperty $storageProperty): self
    {
        // we do this to throw an exception in case an incorrect property is entered here.
        $storageProperty->isInitialized($this->storageObject);
        return $this->clone(['storageProperty' => $storageProperty]);
    }

    public function withArrayKey(string|int $key): self
    {
        return $this->clone(['arrayKey' => $key]);
    }

    public function setStoragePropertyValue(mixed $value): void
    {
        $this->storageProperty->setValue($this->storageObject, $value);
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
        if ($input instanceof MixedStorageInterface) {
            $input = $input->toOriginalObject();
        }
        if (!$wantedType || ('mixed' === (string) $wantedType)) {
            return $input;
        }
        if ($input === null && $wantedType->allowsNull()) {
            return null;
        }
        if (is_object($input)) {
            $class = ConverterUtils::toReflectionClass($wantedType);
            if ($class && $class->isInstance($input)) {
                return $input;
            }
        } elseif ($wantedType instanceof ReflectionNamedType && $wantedType->getName() === get_debug_type($input)) {
            return $input;
        } elseif (TypeUtils::matchesType($wantedType, $input)) {
            return $input;
        }
        try {
            return $this->typeConverter->convertTo($input, $wantedType);
        } catch (Throwable $error) {
            throw new CouldNotCastPropertyException(
                $this->storageProperty ?? null,
                $this->domainObject,
                $wantedType,
                $error
            );
        }
    }

    /**
     * @param array<string, mixed> $altered
     */
    private function clone(array $altered): self
    {
        $properties = get_object_vars($this) + $altered;
        if (!isset($properties['parentContext'])) {
            $properties['parentContext'] = $this;
        }
        $clone = (new ReflectionClass($this))->newInstanceWithoutConstructor();
        foreach ($properties as $propertyName => $propertyValue) {
            $clone->$propertyName = $propertyValue;
        }

        return $clone;
    }
}
