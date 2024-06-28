<?php
namespace Apie\StorageMetadata\ClassInstantiators;

use Apie\Core\ValueObjects\Utils;
use Apie\StorageMetadata\Interfaces\ClassInstantiatorInterface;
use Apie\StorageMetadata\Interfaces\StorageDtoInterface;
use ReflectionClass;
use RuntimeException;

final class ChainedClassInstantiator implements ClassInstantiatorInterface
{
    /** @var array<int, ClassInstantiatorInterface> */
    private array $classInstantiators;

    public function __construct(ClassInstantiatorInterface... $classInstantiators)
    {
        $this->classInstantiators = $classInstantiators;
    }


    public function supports(ReflectionClass $class, ?StorageDtoInterface $storageObject = null): bool
    {
        foreach ($this->classInstantiators as $classInstantiator) {
            if ($classInstantiator->supports($class, $storageObject)) {
                return true;
            }
        }

        return false;
    }

    public function create(ReflectionClass $class, ?StorageDtoInterface $storageObject = null): object
    {
        foreach ($this->classInstantiators as $classInstantiator) {
            if ($classInstantiator->supports($class, $storageObject)) {
                return $classInstantiator->create($class, $storageObject);
            }
        }
        throw new RuntimeException(sprintf(
            'Class "%s" for storage object "%s" not supported for instantiation',
            Utils::displayMixedAsString($class->name),
            Utils::displayMixedAsString($storageObject),
        ));
    }
}
