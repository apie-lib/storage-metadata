<?php
namespace Apie\StorageMetadata\Converters;

use Apie\Core\Lists\ItemHashmap;
use Apie\Core\Lists\ItemList;
use Apie\Core\Lists\ItemSet;
use Apie\TypeConverter\ConverterInterface;

/**
 * @template T
 * @implements ConverterInterface<ItemHashmap<T>|ItemList<T>|ItemSet<T>, array<int, T>>
 */
class ApieListToArray implements ConverterInterface
{
    /**
     * @param ItemHashmap<T>|ItemList<T>|ItemSet<T> $input
     * @return array<int, T>
     */
    public function convert(ItemList|ItemHashmap|ItemSet $input): array
    {
        return $input->toArray();
    }
}
