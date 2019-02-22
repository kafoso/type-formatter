<?php
declare(strict_types = 1);

namespace Kafoso\TypeFormatter\Type\Objects;

use Doctrine\Common\Persistence\Proxy;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping as ORM;
use Kafoso\TypeFormatter\Abstraction\Type\AbstractFormatter;
use Kafoso\TypeFormatter\Type\ObjectFormatterInterface;
use Kafoso\TypeFormatter\TypeFormatter;

/**
 * Handles Doctrine entities. The respective @ORM\Id columns will automatically be found and presented using the
 * Reflection API. Composite IDs, where the primary keys exist in foreign tables, are also handled.
 */
class DoctrineEntityFormatter extends AbstractFormatter implements ObjectFormatterInterface
{
    const UUID_LENGTH = 40;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @inheritDoc
     */
    public function format($object): ?string
    {
        if (false == $this->isQualified($object)) {
            return null;
        }
        $className = static::getOriginalClassName($object);
        $returnBase = sprintf(
            "\\%s",
            $className
        );
        try {
            $return = $returnBase;
            $identifiers = $this->_generateIdentifierArray($object);
            if ($identifiers) {
                $return .= " {" . $this->_renderIdentifiers($identifiers) . "}";
            }
        } catch (\Throwable $t) {
            $typeFormatter = TypeFormatter::create();
            $typeFormatter = $typeFormatter->withStringSampleSize(1000);
            $typeFormatter = $typeFormatter->withObjectDepthCurrent(0);
            $typeFormatter = $typeFormatter->withObjectDepthMaximum(0);
            $return = $returnBase . sprintf(
                " (cannot show @ORM\Id fields: Failure during formatting: \\%s {\$code = %s, \$file = %s, \$line = %s,"
                    . " \$message = %s, \$previous = %s})",
                get_class($t),
                $typeFormatter->cast($t->getCode()),
                $typeFormatter->cast($t->getFile(), false),
                $typeFormatter->cast($t->getLine()),
                $typeFormatter->cast($t->getMessage()),
                $typeFormatter->cast($t->getPrevious())
            );
        }
        return $return;
    }

    /**
     * @var object $object
     */
    public function isObjectDoctrineEntity($object): bool
    {
        if (false == is_object($object)) {
            return false;
        }
        $className = static::getOriginalClassName($object);
        if ($className) {
            return (false == $this->entityManager->getMetadataFactory()->isTransient($className));
        }
        return false;
    }

    /**
     * @var object $object
     */
    public function isQualified($object): bool
    {
        if (false == class_exists(EntityManager::class)) {
            return false;
        }
        if (false == is_object($object)) {
            return false;
        }
        return $this->isObjectDoctrineEntity($object);
    }

    /**
     * @var object $object
     */
    protected function _generateIdentifierArray($object): array
    {
        $className = get_class($object);
        $classMetadata = $this->entityManager->getClassMetadata($className);
        if ($classMetadata) {
            $array = [];
            foreach ($classMetadata->getIdentifierFieldNames() as $propertyName) {
                $reflectionObject = new \ReflectionObject($object);
                do {
                    if ($reflectionObject->implementsInterface(Proxy::class)) {
                        if (false === $reflectionObject->getMethod('__isInitialized')->invoke($object)) {
                            $reflectionObject->getMethod('__load')->invoke($object);
                        }
                        $reflectionObject = $reflectionObject->getParentClass();
                    }
                    if (!$reflectionObject) {
                        break;
                    }
                    if ($reflectionObject->hasProperty($propertyName)) {
                        $reflectionProperty = $reflectionObject->getProperty($propertyName);
                        $reflectionProperty->setAccessible(true);
                        $value = $reflectionProperty->getValue($object);
                        if (is_null($value) || is_scalar($value)) {
                            $array[$reflectionProperty->getName()] = $value;
                        } elseif ($this->isObjectDoctrineEntity($value)) {
                            $fields = $this->_generateIdentifierArray($value);
                            if ($fields) {
                                $array[$reflectionProperty->getName()] = [
                                    "className" => self::getOriginalClassName($value),
                                    "fields" => $fields,
                                ];
                            }
                        }
                    }
                    $reflectionObject = $reflectionObject->getParentClass();
                } while ($reflectionObject);
            }
            return $array;
        }
        return [];
    }

    protected function _renderIdentifiers(array $identifiers): string
    {
        $segments = [];
        $subTypeFormatter = clone $this->getTypeFormatter();
        if ($subTypeFormatter->getStringSampleSize() < static::UUID_LENGTH) {
            // Ensure we can at least display UUIDs
            $subTypeFormatter = $subTypeFormatter->withStringSampleSize(static::UUID_LENGTH);
        }
        foreach ($identifiers as $key => $value) {
            $segment = "\${$key} = ";
            if (is_array($value) && $value["fields"]) {
                $segment .= sprintf(
                    "\\%s {%s}",
                    $value["className"],
                    $this->_renderIdentifiers($value["fields"])
                );
            } else {
                $segment .= $subTypeFormatter->cast($value, false);
            }
            $segments[] = $segment;
        }
        if ($segments) {
            return implode(", ", $segments);
        }
        return "";
    }

    /**
     * @var object $object
     */
    public static function getOriginalClassName($object): ?string
    {
        if (false == is_object($object)) {
            return null;
        }
        return ($object instanceof Proxy ? get_parent_class($object) : get_class($object));
    }
}
