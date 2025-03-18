<?php
namespace Apie\StorageMetadata\PropertyConverters;

use Apie\Core\FileStorage\StoredFile;
use Apie\StorageMetadata\Attributes\GetMethodAttribute;
use Apie\StorageMetadata\Attributes\GetMethodOrPropertyAttribute;
use Apie\StorageMetadata\Interfaces\PropertyConverterInterface;
use Apie\StorageMetadata\Mediators\DomainToStorageContext;
use Psr\Http\Message\UploadedFileInterface;
use ReflectionMethod;

class MethodAttributeConverter implements PropertyConverterInterface
{
    public function applyToDomain(
        DomainToStorageContext $context
    ): void {
        // no-op
    }

    public function applyToStorage(
        DomainToStorageContext $context
    ): void {
        $storageProperty = $context->storageProperty;

        $propertyAttributes = [
            ...$storageProperty->getAttributes(GetMethodAttribute::class),
            ...$storageProperty->getAttributes(GetMethodOrPropertyAttribute::class),
        ];

        foreach ($propertyAttributes as $propertyAttribute) {
            $instance = $propertyAttribute->newInstance();
            $domainMethod = $instance->getReflectionMethod($context->domainClass, $context->domainObject);
            if ($domainMethod) {
                $storagePropertyType = $storageProperty->getType();
                $domainPropertyValue = $domainMethod->invoke($context->domainObject);
                if ($this->isFileStoragePath($domainMethod)
                    && $instance instanceof GetMethodOrPropertyAttribute
                    && $domainPropertyValue === null
                    && $context->domainObject instanceof UploadedFileInterface) {
                    $storedFile = $context->fileStorage->createNewUpload(
                        $context->domainObject,
                        get_debug_type($context->domainObject)
                    );
                    $domainPropertyValue = $storedFile->getStoragePath();
                    $instance->getReflectionProperty($context->domainClass, $context->domainObject)
                        ->setValue($context->domainObject, $domainPropertyValue);
                }
                $storagePropertyValue = $context->dynamicCast($domainPropertyValue, $storagePropertyType);
                $context->setStoragePropertyValue($storagePropertyValue);
            }
        }
    }

    private function isFileStoragePath(ReflectionMethod $method): bool
    {
        return $method->name === 'getStoragePath' && $method->getDeclaringClass()->name === StoredFile::class;
    }
}
