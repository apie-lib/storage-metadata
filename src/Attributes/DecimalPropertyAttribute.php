<?php
namespace Apie\StorageMetadata\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class DecimalPropertyAttribute extends PropertyAttribute
{
    /**
     * @param class-string<object>|null $declaredClass
     */
    public function __construct(
        string $propertyName,
        ?string $declaredClass = null,
        public readonly int $decimals = 2
    ) {
        parent::__construct($propertyName, $declaredClass, false);
    }
}
