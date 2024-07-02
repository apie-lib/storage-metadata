<?php
namespace Apie\StorageMetadata\Converters;

use Apie\Core\FileStorage\PsrAwareStorageInterface;
use Apie\TypeConverter\ConverterInterface;
use Psr\Http\Message\UploadedFileInterface;
use ReflectionType;

/**
 * @implements ConverterInterface<?UploadedFileInterface, ?string>
 */
class UploadedFileInterfaceToString implements ConverterInterface
{
    public function __construct(
        private readonly PsrAwareStorageInterface $psrAwareStorage
    ) {
    }

    public function convert(?UploadedFileInterface $input, ?ReflectionType $wantedType): ?string
    {
        if ($input === null) {
            return null;
        }
        return $this->psrAwareStorage->psrToPath($input);
    }
}
