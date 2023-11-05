<?php
namespace Apie\StorageMetadata\Interfaces;

use Apie\Core\Dto\DtoInterface;
use Apie\StorageMetadata\Mediators\DomainToStorageContext;

interface PropertyConverterInterface extends DtoInterface
{
    public function applyToDomain(
        DomainToStorageContext $domainToStorageContext
    ): void;
}
