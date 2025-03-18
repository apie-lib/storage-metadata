<?php
namespace Apie\Tests\StorageMetadata\Fixtures;

use Apie\Core\FileStorage\FileStorageInterface;
use Apie\Core\FileStorage\StoredFile;
use Apie\StorageMetadata\Attributes\GetMethodOrPropertyAttribute;
use Apie\StorageMetadata\Attributes\StorageMappingAttribute;
use Apie\StorageMetadata\Interfaces\StorageDtoInterface;
use ReflectionClass;

class UploadedFileStorage implements StorageDtoInterface
{
    public static function getClassReference(): ReflectionClass
    {
        return new ReflectionClass(StoredFile::class);
    }

    public function __construct(
        #[StorageMappingAttribute]
        public ?FileStorageInterface $storage,
        #[GetMethodOrPropertyAttribute('getStoragePath', 'storagePath', allowLargeStrings: true)]
        public ?string $storagePath = null,
        #[GetMethodOrPropertyAttribute('getClientMediaType', 'clientMimeType')]
        public ?string $clientMimeType = null,
        #[GetMethodOrPropertyAttribute('getClientFilename', 'clientOriginalFile')]
        public ?string $clientOriginalFile = null,
        #[GetMethodOrPropertyAttribute('getSize', 'fileSize')]
        public ?int $fileSize = null,
        #[GetMethodOrPropertyAttribute('getServerMimeType', 'serverMimeType')]
        public ?string $serverMimeType = null,
        #[GetMethodOrPropertyAttribute('getServerPath', 'serverPath')]
        public ?string $serverPath = null,
        #[GetMethodOrPropertyAttribute('getIndexing', 'indexing')]
        public array $indexing = [],
        // TODO: remove this by changing the tests
        #[GetMethodOrPropertyAttribute('getContent', 'content')]
        public ?string $content = null,
    ) {
    }
}
