<?php
declare(strict_types = 1);

namespace Kafoso\TypeFormatter\Type\Objects;

use Kafoso\TypeFormatter\Abstraction\Type\AbstractFormatter;
use Kafoso\TypeFormatter\Contract\TextuallyIdentifiableInterface;
use Kafoso\TypeFormatter\Type\DefaultObjectFormatter;
use Kafoso\TypeFormatter\Type\ObjectFormatterInterface;
use Kafoso\TypeFormatter\TypeFormatter;

/**
 * Handles classes, which implement `TextuallyIdentifiableInterface`. Useful for e.g. Doctrine entities.
 */
class TextuallyIdentifiableInterfaceFormatter extends AbstractFormatter implements ObjectFormatterInterface
{
    /**
     * @inheritDoc
     */
    public function format($object): ?string
    {
        if (false == $this->isQualified($object)) {
            return null;
        }
        return $object->toTextualIdentifier();
    }

    public function isQualified($object): bool
    {
        if (false == is_object($object)) {
            return false;
        }
        return boolval($object instanceof TextuallyIdentifiableInterface);
    }
}
