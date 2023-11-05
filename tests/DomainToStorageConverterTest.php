<?php
namespace Apie\Tests\StorageMetadata;

use Apie\Core\Entities\EntityInterface;
use Apie\Fixtures\Entities\Order;
use Apie\Fixtures\Entities\OrderLine;
use Apie\Fixtures\Entities\UserWithAddress;
use Apie\Fixtures\Enums\OrderStatus;
use Apie\Fixtures\Identifiers\OrderIdentifier;
use Apie\Fixtures\Identifiers\OrderLineIdentifier;
use Apie\Fixtures\Identifiers\UserWithAddressIdentifier;
use Apie\Fixtures\Lists\OrderLineList;
use Apie\Fixtures\ValueObjects\AddressWithZipcodeCheck;
use Apie\Fixtures\ValueObjects\Password;
use Apie\StorageMetadata\DomainToStorageConverter;
use Apie\StorageMetadata\Interfaces\StorageDtoInterface;
use Apie\Tests\StorageMetadata\Fixtures\AddressStorage;
use Apie\Tests\StorageMetadata\Fixtures\OrderLineStorage;
use Apie\Tests\StorageMetadata\Fixtures\OrderStorage;
use Apie\Tests\StorageMetadata\Fixtures\UserWithAddressStorage;
use Apie\TextValueObjects\DatabaseText;
use Generator;
use PHPUnit\Framework\TestCase;

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
        $testItem = DomainToStorageConverter::create();
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
        $testItem = DomainToStorageConverter::create();
        $actual = $testItem->createDomainObject($storageObject);
        $this->assertEquals(
            $domainObject,
            $actual
        );
    }

    public function provideDomainObjects(): Generator
    {
        yield 'object with composite' => [$this->createUserForDomainObject(), $this->createUserForStorage()];
        yield 'object with one to many' => [$this->createOrderForDomainObject(), $this->createOrderForStorage()];
    }

    private function createOrderForStorage(): OrderStorage
    {
        return new OrderStorage(
            '550e8400-e29b-41d4-a716-446655440000',
            OrderStatus::DRAFT->value,
            [
                new OrderLineStorage('550e8400-e29b-41d4-a716-446655440001', 0),
                new OrderLineStorage('550e8400-e29b-41d4-a716-446655430001', 1),
            ]
        );
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
