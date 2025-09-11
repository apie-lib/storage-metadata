<?php
namespace Apie\Tests\StorageMetadata\Fixtures;

use Apie\DoctrineEntityConverter\Entities\SearchIndex;

class MockSearchIndex extends SearchIndex
{
    public static function create(string $value)
    {
        $res = new self();
        $res->value = $value;
        return $res;
    }
}