<?php
namespace Apie\Tests\StorageMetadata\Fixtures;

use Apie\Core\Utils\EntityUtils;
use Apie\Fixtures\Entities\Polymorphic\Animal;
use Apie\Fixtures\Entities\Polymorphic\Cow;
use Apie\Fixtures\Entities\Polymorphic\Elephant;
use Apie\Fixtures\Entities\Polymorphic\Fish;
use Apie\StorageMetadata\Attributes\DiscriminatorMappingAttribute;
use Apie\StorageMetadata\Attributes\PropertyAttribute;
use Apie\StorageMetadata\Interfaces\StorageClassInstantiatorInterface;
use ReflectionClass;

class AnimalStorage implements StorageClassInstantiatorInterface
{
    public function __construct(
        #[DiscriminatorMappingAttribute]
        private array $discriminatorMapping,
        #[PropertyAttribute('id')]
        private string $apieId,
        #[PropertyAttribute('hasMilk', Cow::class)]
        private ?bool $apieHasMilk = null,
        #[PropertyAttribute('starving', Elephant::class)]
        private ?bool $apieStarving = null,
        #[PropertyAttribute('poisonous', Fish::class)]
        private ?bool $apiePoisonous = null
    ) {
    }

    public static function getClassReference(): ReflectionClass
    {
        return new ReflectionClass(Animal::class);
    }

    public function createDomainObject(ReflectionClass $class): object
    {
        $class = EntityUtils::findClass($this->discriminatorMapping, $class);
        assert(null !== $class);
        return $class->newInstanceWithoutConstructor();
    }
}
