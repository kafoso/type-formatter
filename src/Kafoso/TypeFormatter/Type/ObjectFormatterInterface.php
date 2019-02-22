<?php
declare(strict_types = 1);

namespace Kafoso\TypeFormatter\Type;

use Kafoso\TypeFormatter\TypeFormatter;

interface ObjectFormatterInterface extends FormatterInterface
{
    /**
     * Apply logic, which converts an object into a human readable string. Employed methods may include custom
     * conversion, `json_encode`, `serialize`, and more.
     *
     * If a non-object is provided, this method must return `null` or alternatively throw an
     * `\InvalidArgumentException`, containing a suitable message.
     *
     * When `null` is returned, the next custom object formatter is called. If all custom object formatters return `null`,
     * the default object-to-string logic (`DefaultObjectFormatter->formatEnsureString(...)`) is applied.
     *
     * BEWARE: Calling `$this->getTypeFormatter()->cast(...)` or `$this->getTypeFormatter()->typeCast(...)` on the
     * argument object within this method will cause an endless loop.
     *
     * @throws \InvalidArgumentException
     */
    public function format($object): ?string;
}
