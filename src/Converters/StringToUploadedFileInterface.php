<?php
namespace Apie\StorageMetadata\Converters;

use Apie\Core\FileStorage\FileStorageInterface;
use Apie\Core\FileStorage\StoredFile;
use Apie\Core\Utils\ConverterUtils;
use Apie\TypeConverter\ConverterInterface;
use Psr\Http\Message\UploadedFileInterface;
use ReflectionClass;
use ReflectionType;

/**
 * @implements ConverterInterface<?string, ?UploadedFileInterface>
 */
class StringToUploadedFileInterface implements ConverterInterface
{
    public function __construct(
        private readonly FileStorageInterface $fileStorage
    ) {
    }

    public function convert(?string $input, ?ReflectionType $wantedType): ?UploadedFileInterface
    {
        if ($input === null) {
            return null;
        }
        /** @var ReflectionClass<StoredFile>|null $class */
        $class = ConverterUtils::toReflectionClass($wantedType);
        $res = $this->fileStorage->getProxy(
            $input,
            in_array($class?->name, [UploadedFileInterface::class, null]) ? StoredFile::class : $class->name
        );

        return $res;
    }
}
