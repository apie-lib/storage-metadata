<?php
namespace Apie\StorageMetadata\Attributes;

use Apie\Core\Entities\EntityInterface;
use Attribute;
use ReflectionClass;

#[Attribute(Attribute::TARGET_PROPERTY)]
class GetSearchIndexAttribute
{
    /**
     * @param class-string<object>|null $declaredClass
     */
    public function __construct(
        public readonly string $methodName,
        public readonly ?string $declaredClass = null,
        public readonly ?string $arrayValueType = null,
    ) {
    }

    /**
     * @param ReflectionClass<EntityInterface> $targetClass
     */
    public function getValue(ReflectionClass $targetClass, object $domainObject): mixed
    {
        $refl = ($this->declaredClass ? new ReflectionClass($this->declaredClass) : $targetClass);
        if ($refl->hasMethod($this->methodName)) {
            return $refl->getMethod($this->methodName)->invoke($domainObject);
        }
        return $refl->getProperty($this->methodName)->getValue($domainObject);
    }
}
