<?php
namespace Apie\StorageMetadata\Exceptions;

use Apie\Core\Exceptions\ApieException;
use ReflectionProperty;
use ReflectionType;
use Throwable;

class CouldNotCastPropertyException extends ApieException
{
    public function __construct(
        ?ReflectionProperty $storageProperty,
        object $domainObject,
        ?ReflectionType $wantedType,
        Throwable $previous
    ) {
        parent::__construct(
            sprintf(
                'Could not cast to type %s from storage property "%s" from domain object "%s"',
                (string) $wantedType,
                $storageProperty ? $storageProperty->name : '(null)',
                get_debug_type($domainObject)
            ),
            0,
            $previous
        );
    }
}
