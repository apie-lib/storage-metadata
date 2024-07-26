<?php
namespace Apie\StorageMetadata\ClassInstantiators;

use Apie\Core\FileStorage\StoredFile;
use Apie\StorageMetadata\Interfaces\ClassInstantiatorInterface;
use Apie\StorageMetadata\Interfaces\StorageDtoInterface;
use Psr\Http\Message\UploadedFileInterface;
use ReflectionClass;

final class FromStoredFile implements ClassInstantiatorInterface
{
    public function supports(ReflectionClass $class, ?StorageDtoInterface $storageObject = null): bool
    {
        if ($class->name === UploadedFileInterface::class) {
            return true;
        }
        if ($storageObject === null) {
            return false;
        }
        return in_array(UploadedFileInterface::class, $class->getInterfaceNames());
    }

    public function create(ReflectionClass $class, ?StorageDtoInterface $storageObject = null): object
    {
        assert($storageObject !== null);
        $classReference = $storageObject->getClassReference();
        if ($classReference->name === UploadedFileInterface::class) {
            $classReference = new ReflectionClass(StoredFile::class);
        }
        return $classReference->getMethod('createFromDto')
            ->invoke(null, $storageObject);
    }
}
