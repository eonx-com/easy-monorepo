<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Helpers;

use EonX\EasyAsync\Interfaces\DateTimeGeneratorInterface;
use Nette\Utils\Strings;

final class PropertyHelper
{
    /**
     * Get setter name for given property.
     *
     * @param string $property
     *
     * @return string
     */
    public static function getSetterName(string $property): string
    {
        return \sprintf('set%s', Strings::capitalize($property));
    }

    /**
     * Set datetime properties on given object.
     *
     * @param object $object
     * @param mixed[] $data
     * @param mixed[] $properties
     * @param \EonX\EasyAsync\Interfaces\DateTimeGeneratorInterface $datetime
     *
     * @return void
     *
     * @throws \EonX\EasyAsync\Exceptions\UnableToGenerateDateTimeException
     */
    public static function setDatetimeProperties(
        $object,
        array $data,
        array $properties,
        DateTimeGeneratorInterface $datetime
    ): void {
        foreach ($properties as $property) {
            if (empty($data[$property]) === false) {
                $setter = static::getSetterName($property);
                $object->$setter($datetime->fromString($data[$property]));
            }
        }
    }

    /**
     * Set integer properties on given object.
     *
     * @param object $object
     * @param mixed[] $data
     * @param mixed[] $properties
     *
     * @return void
     */
    public static function setIntProperties($object, array $data, array $properties): void
    {
        foreach ($properties as $property) {
            $setter = static::getSetterName($property);
            $object->$setter((int)($data[$property] ?? 0));
        }
    }

    /**
     * Set json properties on given object.
     *
     * @param object $object
     * @param mixed[] $data
     * @param mixed[] $properties
     *
     * @return void
     *
     * @throws \Nette\Utils\JsonException
     */
    public static function setJsonProperties($object, array $data, array $properties): void
    {
        foreach ($properties as $property) {
            $setter = static::getSetterName($property);
            $object->$setter(JsonHelper::decode($data[$property] ?? null));
        }
    }

    /**
     * Set optional properties on given object.
     *
     * @param object $object
     * @param mixed[] $data
     * @param mixed[] $properties
     *
     * @return void
     */
    public static function setOptionalProperties($object, array $data, array $properties): void
    {
        foreach ($properties as $property) {
            if (isset($data[$property])) {
                $setter = static::getSetterName($property);
                $object->$setter($data[$property]);
            }
        }
    }
}
