<?php
declare(strict_types = 1);

namespace Kafoso\TypeFormatter\Type\Objects;

use Kafoso\TypeFormatter\Abstraction\Type\AbstractFormatter;
use Kafoso\TypeFormatter\Type\DefaultObjectFormatter;
use Kafoso\TypeFormatter\Type\ObjectFormatterInterface;
use Kafoso\TypeFormatter\TypeFormatter;

/**
 * Handles instances of `\Directory`.
 */
class DirectoryFormatter extends AbstractFormatter implements ObjectFormatterInterface
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
            "\\%s {\$path = %s}",
            DefaultObjectFormatter::getClassName($object),
            $this->getTypeFormatter()->getDefaultStringFormatter()->formatEnsureString($object->path)
        );
    }

    public function isQualified($object): bool
    {
        if (false == is_object($object)) {
            return false;
        }
        return boolval($object instanceof \Directory);
    }
}
