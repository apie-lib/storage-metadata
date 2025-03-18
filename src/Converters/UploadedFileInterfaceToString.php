<?php
namespace Apie\StorageMetadata\Converters;

use Apie\Core\Enums\UploadedFileStatus;
use Apie\Core\FileStorage\FileStorageInterface;
use Apie\Core\FileStorage\StoredFile;
use Apie\Core\Utils\ConverterUtils;
use Apie\TypeConverter\ConverterInterface;
use Psr\Http\Message\UploadedFileInterface;
use ReflectionClass;
use ReflectionType;

/**
 * @implements ConverterInterface<?UploadedFileInterface, ?string>
 */
class UploadedFileInterfaceToString implements ConverterInterface
{
    public function __construct(
        private readonly FileStorageInterface $fileStorage
    ) {
    }

    public function convert(?UploadedFileInterface $input, ?ReflectionType $wantedType): ?string
    {
        if ($input === null) {
            return null;
        }
        if ($input instanceof StoredFile && $input->getStoragePath() !== null && $input->getStatus() === UploadedFileStatus::StoredInStorage) {
            return $input->getStoragePath();
        }
        /** @var ReflectionClass<StoredFile>|null $class */
        $class = ConverterUtils::toReflectionClass($wantedType);
        $input = $this->fileStorage->createNewUpload(
            $input,
            in_array($class?->name, [UploadedFileInterface::class, null]) ? StoredFile::class : $class->name
        );
        return $input->getStoragePath();
    }
}
