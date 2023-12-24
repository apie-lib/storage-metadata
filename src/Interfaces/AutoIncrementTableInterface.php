<?php
namespace Apie\StorageMetadata\Interfaces;

interface AutoIncrementTableInterface
{
    public function getKey(): ?int;
}
