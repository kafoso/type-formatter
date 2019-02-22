<?php
declare(strict_types = 1);

namespace Kafoso\TypeFormatter\Type\Objects;

use Kafoso\TypeFormatter\Abstraction\Type\AbstractFormatter;
use Kafoso\TypeFormatter\Type\DefaultObjectFormatter;
use Kafoso\TypeFormatter\Type\ObjectFormatterInterface;
use Kafoso\TypeFormatter\TypeFormatter;

/**
 * Prints class name and ISO 8601 datetime in parenthesis. Example: \DateTimeImmutable ("2019-01-01T00:00:00+00:00")
 */
class DateTimeInterfaceFormatter extends AbstractFormatter implements ObjectFormatterInterface
{
    /**
     * @inheritDoc
     */
    public function format($object): ?string
    {
        if (false == is_object($object)) {
            return null; // Pass on
        }
        if (false == $this->isQualified($object)) {
            return null;
        }
        return sprintf(
            "\\%s (%s)",
            DefaultObjectFormatter::getClassName($object),
            $this->getTypeFormatter()->getDefaultStringFormatter()->formatEnsureString($object->format("c"))
        );
    }

    public function isQualified($object): bool
    {
        if (false == is_object($object)) {
            return false;
        }
        return boolval($object instanceof \DateTimeInterface);
    }
}
