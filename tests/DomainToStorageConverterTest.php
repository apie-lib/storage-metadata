<?php
namespace Apie\Tests\StorageMetadata;

use Apie\Core\Entities\EntityInterface;
use Apie\Core\FileStorage\FileStorageFactory;
use Apie\Core\FileStorage\StoredFile;
use Apie\Core\ValueObjects\DatabaseText;
use Apie\Fixtures\Entities\ImageFile;
use Apie\Fixtures\Entities\Order;
use Apie\Fixtures\Entities\OrderLine;
use Apie\Fixtures\Entities\Polymorphic\Animal;
use Apie\Fixtures\Entities\Polymorphic\AnimalIdentifier;
use Apie\Fixtures\Entities\Polymorphic\Elephant;
use Apie\Fixtures\Entities\UserWithAddress;
use Apie\Fixtures\Enums\OrderStatus;
use Apie\Fixtures\Identifiers\ImageFileIdentifier;
use Apie\Fixtures\Identifiers\OrderIdentifier;
use Apie\Fixtures\Identifiers\OrderLineIdentifier;
use Apie\Fixtures\Identifiers\UserWithAddressIdentifier;
use Apie\Fixtures\Lists\OrderLineList;
use Apie\Fixtures\ValueObjects\AddressWithZipcodeCheck;
use Apie\Fixtures\ValueObjects\Password;
use Apie\StorageMetadata\DomainToStorageConverter;
use Apie\StorageMetadata\Interfaces\StorageDtoInterface;
use Apie\Tests\StorageMetadata\Fixtures\AddressStorage;
use Apie\Tests\StorageMetadata\Fixtures\AnimalStorage;
use Apie\Tests\StorageMetadata\Fixtures\FileStorage;
use Apie\Tests\StorageMetadata\Fixtures\OrderLineStorage;
use Apie\Tests\StorageMetadata\Fixtures\OrderStorage;
use Apie\Tests\StorageMetadata\Fixtures\UploadedFileStorage;
use Apie\Tests\StorageMetadata\Fixtures\UserWithAddressStorage;
use Generator;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class DomainToStorageConverterTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_inject_values_in_an_existing_domain_object()
    {
        $domainObject = $this->createUserForDomainObject();
        $domainObject->setPassword(new Password('Aa1234!'));
        $this->assertTrue($domainObject->hasPassword(), 'hasPassword() should return true if it has a password');
        $addressHash = spl_object_hash($domainObject->getAddress());
        $testItem = DomainToStorageConverter::create(FileStorageFactory::create());
        $testItem->injectExistingDomainObject(
            $domainObject,
            $this->createUserForStorage()
        );
        $this->assertSame($addressHash, spl_object_hash($domainObject->getAddress()), 'I expect to have the same pointer');
        $this->assertFalse($domainObject->hasPassword(), 'hasPassword() should return false after update from storage object');
    }

    /**
     * @test
     * @dataProvider provideDomainObjects
     */
    public function it_can_convert_a_storage_object_to_domain_object(EntityInterface $domainObject, StorageDtoInterface $storageObject)
    {
        $testItem = DomainToStorageConverter::create(FileStorageFactory::create());
        $actual = $testItem->createDomainObject($storageObject);
        $this->assertEquals(
            $domainObject,
            $actual
        );
    }

    /**
     * @test
     * @dataProvider provideDomainObjects
     */
    public function it_can_convert_a_domain_object_to_a_storage_object(EntityInterface $domainObject, StorageDtoInterface $storageObject)
    {
        $testItem = DomainToStorageConverter::create(FileStorageFactory::create());
        $actual = $testItem->createStorageObject($domainObject, new ReflectionClass($storageObject));
        $this->assertEquals(
            $storageObject,
            $actual
        );
    }

    public function provideDomainObjects(): Generator
    {
        yield 'object with composite' => [$this->createUserForDomainObject(), $this->createUserForStorage()];
        yield 'object with one to many' => [$this->createOrderForDomainObject(), $this->createOrderForStorage()];
        yield 'polymorphic object' => [$this->createElephantForDomainObject(), $this->createElephantForStorage()];
        yield 'file storage' => [$this->createFileStorageForDomainObject(), $this->createFileStorageForStorage()];
    }

    private function createFileStorageForDomainObject(): ImageFile
    {
        $file = StoredFile::createFromString('<svg></svg>', 'image/svg', 'example.svg')
            ->markBeingStored(FileStorageFactory::create(), 'image/svg|example.svg|PHN2Zz48L3N2Zz4=');
        $file->getIndexing();
        $file->getServerMimeType();
        $file->getSize();
        $file->getContent();
        return new ImageFile(
            ImageFileIdentifier::fromNative('550e8400-e29b-41d4-a716-446655440001'),
            $file,
            "Image metadata"
        );
    }

    private function createFileStorageForStorage()
    {
        return new FileStorage(
            '550e8400-e29b-41d4-a716-446655440001',
            '550e8400-e29b-41d4-a716-446655440001',
            "Image metadata",
            new UploadedFileStorage(
                FileStorageFactory::create(),
                clientMimeType: 'image/svg',
                clientOriginalFile: 'example.svg',
                storagePath: 'image/svg|example.svg|PHN2Zz48L3N2Zz4=',
                fileSize: 11,
                serverMimeType: 'image/svg+xml',
                indexing: [],
                content: '<svg></svg>',
            )
        );
    }

    private function createElephantForStorage(): AnimalStorage
    {
        return new AnimalStorage(
            discriminatorMapping: ['animalType' => 'elephant'],
            id: '550e8400-e29b-41d4-a716-446655440000',
            apieId: '550e8400-e29b-41d4-a716-446655440000',
            apieStarving: true
        );
    }

    private function createElephantForDomainObject(): Animal
    {
        $res = new Elephant(AnimalIdentifier::fromNative('550e8400-e29b-41d4-a716-446655440000'));
        $res->starving = true;
        return $res;
    }

    private function createOrderForStorage(): OrderStorage
    {
        $res = new OrderStorage(
            '550e8400-e29b-41d4-a716-446655440000',
            '550e8400-e29b-41d4-a716-446655440000',
            OrderStatus::DRAFT->value,
            [
                new OrderLineStorage('550e8400-e29b-41d4-a716-446655440001', 0),
                new OrderLineStorage('550e8400-e29b-41d4-a716-446655430001', 1),
            ]
        );
        $res->searchOrderLines = [
            '550e8400-e29b-41d4-a716-446655440001',
            '550e8400-e29b-41d4-a716-446655430001'
        ];
        return $res;
    }

    private function createOrderForDomainObject(): Order
    {
        return new Order(
            OrderIdentifier::fromNative('550e8400-e29b-41d4-a716-446655440000'),
            new OrderLineList([
                new OrderLine(OrderLineIdentifier::fromNative('550e8400-e29b-41d4-a716-446655440001')),
                new OrderLine(OrderLineIdentifier::fromNative('550e8400-e29b-41d4-a716-446655430001'))
            ])
        );
    }

    private function createUserForStorage(): UserWithAddressStorage
    {
        return new UserWithAddressStorage(
            id: '550e8400-e29b-41d4-a716-446655440000',
            apieId: '550e8400-e29b-41d4-a716-446655440000',
            apieAddress: new AddressStorage(
                'Evergreen Terrace',
                '743',
                '1111',
                'Springfield',
                true
            )
        );
    }

    private function createUserForDomainObject(): UserWithAddress
    {
        return new UserWithAddress(
            new AddressWithZipcodeCheck(
                new DatabaseText('Evergreen Terrace'),
                new DatabaseText('743'),
                new DatabaseText('1111'),
                new DatabaseText('Springfield'),
            ),
            UserWithAddressIdentifier::fromNative('550e8400-e29b-41d4-a716-446655440000')
        );
    }
}
