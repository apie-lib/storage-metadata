<?php
namespace Apie\Tests\StorageMetadata\Fixtures;

use Apie\Fixtures\Entities\ImageFile;
use Apie\StorageMetadata\Attributes\GetMethodAttribute;
use Apie\StorageMetadata\Attributes\PropertyAttribute;
use Apie\StorageMetadata\Interfaces\StorageDtoInterface;
use ReflectionClass;

class FileStorage implements StorageDtoInterface
{
    public static function getClassReference(): ReflectionClass
    {
        return new ReflectionClass(ImageFile::class);
    }

    public function __construct(
        #[GetMethodAttribute('getId')]
        private string $id,
        #[PropertyAttribute('id')]
        public ?string $apieId,
        #[PropertyAttribute('alternativeText', allowLargeStrings: true)]
        public ?string $apieAlternativeText,
        #[PropertyAttribute('file', allowLargeStrings: true)]
        public ?string $apieFile
    ) {
    }
}
