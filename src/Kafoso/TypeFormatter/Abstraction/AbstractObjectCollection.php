<?php
declare(strict_types = 1);

namespace Kafoso\TypeFormatter\Abstraction;

use Kafoso\TypeFormatter\TypeFormatter;
use Doctrine\Common\Collections\ArrayCollection as DoctrineArrayCollection;

/**
 * An array collection which holds objects of a certain class, exclusively. The class is deterined by the method
 * `getClassName`.
 */
abstract class AbstractObjectCollection extends DoctrineArrayCollection
{
    /**
     * @inheritDoc
     * @throws \RuntimeException
     */
    public function __construct(array $elements = [])
    {
        if ($elements) {
            $invalids = [];
            $className = static::getClassName();
            foreach ($elements as $k => $element) {
                if (false == is_object($element) || false == ($element instanceof $className)) {
                    $invalids[$k] = $element;
                }
            }
            if ($invalids) {
                throw new \RuntimeException(sprintf(
                    "Argument \$elements contain %d/%d invalid values. Must contain objects, instance of \\%s,"
                        . " exclusively. Invalid values include: %s",
                    count($invalids),
                    count($elements),
                    $className,
                    TypeFormatter::create()->typeCast($invalids)
                ));
            }
        }
        parent::__construct($elements);
    }

    /**
     * @inheritDoc
     * @throws \InvalidArgumentException
     */
    public function add($element)
    {
        $exception = static::validateElement($element, '$element');
        if ($exception) {
            throw $exception;
        }
        return parent::add($element);
    }

    /**
     * @inheritDoc
     * @throws \InvalidArgumentException
     */
    public function removeElement($element)
    {
        $exception = static::validateElement($element, '$element');
        if ($exception) {
            throw $exception;
        }
        return parent::removeElement($element);
    }

    /**
     * @inheritDoc
     * @throws \InvalidArgumentException
     */
     public function offsetSet($offset, $value)
    {
        $exception = static::validateElement($value, '$value');
        if ($exception) {
            throw $exception;
        }
        return parent::offsetSet($key, $value);
    }

    /**
     * @inheritDoc
     * @throws \InvalidArgumentException
     */
    public function set($key, $value)
    {
        $exception = static::validateElement($value, '$value');
        if ($exception) {
            throw $exception;
        }
        return parent::set($key, $value);
    }

    /**
     * @throws \UnexpectedValueException
     * @throws \RuntimeException
     */
    public function merge(AbstractObjectCollection $objectCollection): AbstractObjectCollection
    {
        if ($this !== $objectCollection) {
            $className = get_class($this);
            if (false == ($objectCollection instanceof $className)) {
                throw new \UnexpectedValueException(sprintf(
                    "Argument \$objectCollection must be an instance of \\%s. Found: %s",
                    $className,
                    TypeFormatter::create()->typeCast($objectCollection)
                ));
            }
            try {
                $elementsBefore = $this->toArray();
                foreach ($objectCollection as $key => $element) {
                    $this->set($key, $element);
                }
            } catch (Throwable $t) {
                $this->clear();
                foreach ($elementsBefore as $key => $element) {
                    $this->set($key, $element);
                }
                $typeFormatter = TypeFormatter::create();
                throw new \RuntimeException(sprintf(
                    "Failed to merge %s into %s",
                    $typeFormatter->cast($objectCollection),
                    $typeFormatter->cast($this)
                ), 0, $t);
            }
        }
        return $this;
    }

    public static function assertIsElementValid($element): bool
    {
        $className = static::getClassName();
        return (
            is_object($element)
            && ($element instanceof $className)
        );
    }

    public static function validateElement($element, $argumentName): ?\InvalidArgumentException
    {
        if (false == static::assertIsElementValid($element)) {
            return new \InvalidArgumentException(sprintf(
                "Argument \$%s must be an object, instance of \\%s. Found: %s",
                $argumentName,
                static::getClassName(),
                TypeFormatter::create()->typeCast($element)
            ));
        }
        return null;
    }

    abstract public static function getClassName(): string;
}
