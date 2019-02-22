<?php
declare(strict_types = 1);

namespace Kafoso\TypeFormatter\Contract;

interface TextuallyIdentifiableInterface
{
    /**
     * Converts a class to a string respresentation.
     *
     * Example:
     *
     *    sprintf(
     *        "\\%s (USER.ID = %s)",
     *        get_class($this),
     *        TypeFormatter::getDefault()->cast($this->id)
     *    )
     *
     * Which will output something like:
     *
     * \MyUserClass (USER.ID = 22)
     */
    public function getTextualIdentifier(): string;
}
