<?php
declare(strict_types = 1);

namespace Kafoso\TypeFormatter\Type;

use Kafoso\TypeFormatter\Abstraction\Type\AbstractFormatter;
use Kafoso\TypeFormatter\Contract\TextuallyIdentifiableInterface;
use Kafoso\TypeFormatter\Type\Objects\TextuallyIdentifiableInterfaceFormatter;
use Kafoso\TypeFormatter\TypeFormatter;

class DefaultObjectFormatter extends AbstractFormatter implements ObjectFormatterInterface
{
    /**
     * @inheritDoc
     */
    public function format($object): ?string
    {
        if (false == is_object($object)) {
            throw new \InvalidArgumentException(sprintf(
                "Expects argument \$object to be an object. Found: %s",
                TypeFormatter::create()->typeCast($object)
            ));
        }

        if ($object instanceof TextuallyIdentifiableInterface) {
            $formatter = new TextuallyIdentifiableInterfaceFormatter;
            $formatter->setTypeFormatter($this->typeFormatter);
            $return = $formatter->format($object);
            if (is_string($return)) {
                if ($this->isPrependingType()) {
                    $return = sprintf(
                        "(object) %s",
                        $return
                    );
                }
                return $return;
            }
        }

        $className = static::getClassName($object);
        $return = sprintf(
            "\\%s",
            $className
        );
        if ($this->isPrependingType()) {
            $return = sprintf(
                "(object) %s",
                $return
            );
        }
        return $return;
    }

    public function formatEnsureString($object): string
    {
        return strval($this->format($object));
    }

    /**
     * @throws \InvalidArgumentException
     */
    public static function getAnonymousClassName($object): ?string
    {
        if (false == is_object($object)) {
            throw new \InvalidArgumentException(sprintf(
                "Expects argument \$object to be an object. Found: %s",
                TypeFormatter::create()->typeCast($object)
            ));
        }
        $className = strval(get_class($object));
        preg_match('/^class@anonymous.(\/|\\\\)[^\1]+/', $className, $match); // . = Evil nasty byte of doom
        if ($match) {
            // Rebuild string. Otherwise, data would be binary for anonymous classes
            return sprintf(
                "%s%s",
                substr($className, 0, 15),
                substr($className, 16)
            );
        }
        return null;
    }

    /**
     * @throws \InvalidArgumentException
     */
    public static function getClassName($object): string
    {
        if (false == is_object($object)) {
            throw new \InvalidArgumentException(sprintf(
                "Expects argument \$object to be an object. Found: %s",
                TypeFormatter::create()->typeCast($object)
            ));
        }
        $className = static::getAnonymousClassName($object);
        if (is_null($className)) {
            $className = get_class($object);
        }
        return $className;
    }
}
