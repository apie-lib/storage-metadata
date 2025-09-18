<?php
namespace Apie\Tests\StorageMetadata\Fixtures;

use Apie\StorageMetadataBuilder\Interfaces\MixedStorageInterface;

class MockMixedStorage implements MixedStorageInterface
{
    public function __construct(private object $originalObject)
    {
    }

    public function toOriginalObject(): object
    {
        return $this->originalObject;
    }
}
