<?php
namespace Apie\Tests\StorageMetadata;

use Apie\Core\FileStorage\ChainedFileStorage;
use Apie\Core\FileStorage\InlineStorage;
use Apie\Core\FileStorage\StoredFile;
use Apie\Core\Lists\ItemList;
use Apie\Core\Lists\ItemSet;
use Apie\Core\Lists\StringHashmap;
use Apie\Core\ValueObjects\NonEmptyString;
use Apie\DoctrineEntityConverter\Entities\SearchIndex;
use Apie\Fixtures\Enums\OrderStatus;
use Apie\Fixtures\Identifiers\UserAutoincrementIdentifier;
use Apie\StorageMetadata\Interfaces\AutoIncrementTableInterface;
use Apie\StorageMetadata\TypeConverterFactory;
use Apie\Tests\StorageMetadata\Fixtures\MockSearchIndex;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class TypeConverterFactoryTest extends TestCase
{
    #[Test]
    #[DataProvider('typesProvider')]
    public function it_creates_a_factory_for_converting_types_for_storage_metadata_package(mixed $expected, mixed $input, string $wantedType)
    {
        $fileStorage = new ChainedFileStorage([new InlineStorage()], [new InlineStorage()]);
        $converter = TypeConverterFactory::create($fileStorage);
        $actual = $converter->convertTo($input, $wantedType);
        if ($expected instanceof StoredFile && $actual instanceof StoredFile) {
            $this->assertEquals($expected->getContent(), $actual->getContent());
            $this->assertEquals($expected->getClientFilename(), $actual->getClientFilename());
            $this->assertEquals($expected->getClientMediatype(), $actual->getClientMediatype());
            $this->assertEquals($expected->getSize(), $actual->getSize());

            return;
        }
        $this->assertEquals($expected, $actual);
    }

    public static function typesProvider(): \Generator
    {
        yield 'string to DateTime' => [new \DateTime('2020-01-01'), '2020-01-01', \DateTime::class];
        yield 'DateTime to string' => ['2020-01-01T00:00:00+00:00', new \DateTime('2020-01-01'), 'string'];
        yield 'string to enum' =>  [OrderStatus::DRAFT, Orderstatus::DRAFT->value, OrderStatus::class];
        yield 'enum to string' =>  [OrderStatus::DRAFT->value, OrderStatus::DRAFT, 'string'];

        yield 'string to value object' => [
            NonEmptyString::fromNative('user-123'),
            'user-123',
            NonEmptyString::class,
        ];
        yield 'value object to string' => [
            'user-123',
            NonEmptyString::fromNative('user-123'),
            'string',  
        ];

        yield 'int to value object' => [
            UserAutoincrementIdentifier::fromNative(42),
            42,
            UserAutoincrementIdentifier::class
        ];

        yield 'value object to int' => [
            42,
            UserAutoincrementIdentifier::fromNative(42),
            'int'
        ];

        yield 'float to value object' => [
            UserAutoincrementIdentifier::fromNative(42),
            42,
            UserAutoincrementIdentifier::class
        ];

        yield 'value object to float' => [
            42,
            UserAutoincrementIdentifier::fromNative(42),
            'float'
        ];

        yield 'list to array' => [
            ['a', 'b', 'c'],
            new ItemList(['a', 'b', 'c']),
            'array'
        ];
        yield 'array to list' => [
            new ItemList(['a', 'b', 'c']),
            ['a', 'b', 'c'],
            ItemList::class
        ];
        yield 'array to set' => [
            new ItemSet(['a', 'b', 'c']),
            ['a', 'b', 'c'],
            ItemSet::class
        ];
        yield 'array to hashmap' => [
            new StringHashmap(['a', 'b', 'c']),
            ['a', 'b', 'c'],
            StringHashmap::class
        ];

        if (class_exists(SearchIndex::class)) {
            $searchIndex = MockSearchIndex::create('search term');
            yield 'string to SearchIndex' => [
                $searchIndex,
                'search term',
                MockSearchIndex::class
            ];
        }

        yield 'autoincrement table to int' => [
            42,
            new class implements AutoIncrementTableInterface {
                public function getKey(): int
                {
                    return 42;
                }
            },
            'int'
        ];
        
        yield 'autoincrement table to value object' => [
            UserAutoincrementIdentifier::fromNative(42),
            new class implements AutoIncrementTableInterface {
                public function getKey(): int
                {
                    return 42;
                }
            },
            UserAutoincrementIdentifier::class
        ];

        $file = StoredFile::createFromString('file contents', 'example.txt');
        yield 'file to string' => [
            'example.txt||ZmlsZSBjb250ZW50cw==',
            $file,
            'string'
        ];
        yield 'string to file' => [
            $file,
            'example.txt||ZmlsZSBjb250ZW50cw==',
            StoredFile::class
        ];

        // conversions to the same type throw an error in the current definition and are handled differently.
        /*yield 'string to string' => ['hello', 'hello', 'string'];
        yield 'int to int' => [42, 42, 'int'];
        yield 'float to float' => [3.14, 3.14, 'float'];
        yield 'bool to bool' => [true, true, 'bool'];
        yield 'array to array' => [[1, 2, 3], [1, 2, 3], 'array'];
        yield 'null to null' => [null, null, 'null'];
        yield 'mixed to mixed' => ['anything', 'anything', 'mixed'];*/
    }
}