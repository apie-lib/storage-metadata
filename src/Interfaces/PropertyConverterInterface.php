<?php
namespace Apie\StorageMetadata\Interfaces;

use Apie\StorageMetadata\Mediators\DomainToStorageContext;

interface PropertyConverterInterface
{
    public function applyToDomain(
        DomainToStorageContext $domainToStorageContext
    ): void;
    public function applyToStorage(
        DomainToStorageContext $domainToStorageContext
    ): void;
}
