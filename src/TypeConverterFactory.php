<?php
namespace Apie\StorageMetadata;

use Apie\Core\FileStorage\ChainedFileStorage;
use Apie\Core\TypeConverters\ArrayToDoctrineCollection;
use Apie\Core\TypeConverters\DoctrineCollectionToArray;
use Apie\StorageMetadata\Converters\ApieListToArray;
use Apie\StorageMetadata\Converters\ArrayToItemHashmap;
use Apie\StorageMetadata\Converters\ArrayToItemList;
use Apie\StorageMetadata\Converters\ArrayToItemSet;
use Apie\StorageMetadata\Converters\AutoIncrementTableToInt;
use Apie\StorageMetadata\Converters\AutoIncrementTableToValueObject;
use Apie\StorageMetadata\Converters\BcMathToString;
use Apie\StorageMetadata\Converters\DateTimeToDateTimeImmutable;
use Apie\StorageMetadata\Converters\DateTimeToString;
use Apie\StorageMetadata\Converters\DomToString;
use Apie\StorageMetadata\Converters\GMPToString;
use Apie\StorageMetadata\Converters\IntToAutoIncrementTable;
use Apie\StorageMetadata\Converters\IntToValueObject;
use Apie\StorageMetadata\Converters\MixedStorageToObject;
use Apie\StorageMetadata\Converters\MixedToMixedStorage;
use Apie\StorageMetadata\Converters\SimpleXmlToString;
use Apie\StorageMetadata\Converters\StringToBcMath;
use Apie\StorageMetadata\Converters\StringToDateTime;
use Apie\StorageMetadata\Converters\StringToDom;
use Apie\StorageMetadata\Converters\StringToEnum;
use Apie\StorageMetadata\Converters\StringToGMP;
use Apie\StorageMetadata\Converters\StringToSearchIndex;
use Apie\StorageMetadata\Converters\StringToSimpleXml;
use Apie\StorageMetadata\Converters\StringToUploadedFileInterface;
use Apie\StorageMetadata\Converters\StringToUrl;
use Apie\StorageMetadata\Converters\StringToValueObject;
use Apie\StorageMetadata\Converters\UploadedFileInterfaceToString;
use Apie\StorageMetadata\Converters\UriToString;
use Apie\StorageMetadata\Converters\ValueObjectToAutoIncrementTable;
use Apie\StorageMetadata\Converters\ValueObjectToFloat;
use Apie\StorageMetadata\Converters\ValueObjectToInt;
use Apie\StorageMetadata\Converters\ValueObjectToString;
use Apie\TypeConverter\Converters\ObjectToObjectConverter;
use Apie\TypeConverter\DefaultConvertersFactory;
use Apie\TypeConverter\TypeConverter;

final class TypeConverterFactory
{
    /**
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }

    public static function create(ChainedFileStorage $fileStorage): TypeConverter
    {
        return new TypeConverter(
            new ObjectToObjectConverter(),
            ...DefaultConvertersFactory::create(
                new StringToUploadedFileInterface($fileStorage),
                new UploadedFileInterfaceToString($fileStorage),
                new ArrayToDoctrineCollection(),
                new StringToSearchIndex(),
                new DoctrineCollectionToArray(),
                new ApieListToArray(),
                new AutoIncrementTableToInt(),
                new AutoIncrementTableToValueObject(),
                new IntToAutoIncrementTable(),
                new ValueObjectToAutoIncrementTable(),
                new ValueObjectToInt(),
                new IntToValueObject(),
                new ValueObjectToFloat(),
                new MixedStorageToObject(),
                new MixedToMixedStorage(),
                new ValueObjectToString(),
                new StringToDateTime(),
                new DateTimeToDateTimeImmutable(),
                new DateTimeToString(),
                new DomToString(),
                new SimpleXmlToString(),
                new StringToDom(),
                new StringToSimpleXml(),
                new StringToUrl(),
                new UriToString(),
                new StringToValueObject(),
                new StringToEnum(),
                new StringToGMP(),
                new GMPToString(),
                new StringToBcMath(),
                new BcMathToString(),
                new ArrayToItemHashmap(),
                new ArrayToItemList(),
                new ArrayToItemSet(),
            )
        );
    }
}
