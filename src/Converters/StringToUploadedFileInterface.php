<?php
namespace Apie\StorageMetadata\Converters;

use Apie\Core\FileStorage\PsrAwareStorageInterface;
use Apie\TypeConverter\ConverterInterface;
use Psr\Http\Message\UploadedFileInterface;
use ReflectionType;

/**
 * @implements ConverterInterface<?string, ?UploadedFileInterface>
 */
class StringToUploadedFileInterface implements ConverterInterface
{
    public function __construct(
        private readonly PsrAwareStorageInterface $psrAwareStorage
    ) {
    }

    public function convert(?string $input, ?ReflectionType $wantedType): ?UploadedFileInterface
    {
        if ($input === null) {
            return null;
        }
        return $this->psrAwareStorage->pathToPsr($input);
    }
}
