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
        if ($storageObject === null) {
            return false;
        }
        if (in_array($class->name, [UploadedFileInterface::class, StoredFile::class])) {
            return true;
        }
        while ($class) {
            if ($class->name === StoredFile::class) {
                return true;
            }
            $class = $class->getParentClass();
        }
        return false;
    }

    public function create(ReflectionClass $class, ?StorageDtoInterface $storageObject = null): object
    {
        assert($storageObject !== null);
        return $storageObject->getClassReference()->getMethod('createFromDto')
            ->invoke(null, $storageObject);
    }
}
