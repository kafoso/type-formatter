<?php
declare(strict_types = 1);

namespace Kafoso\TypeFormatter\Type;

interface ResourceFormatterInterface extends FormatterInterface
{
    /**
     * Apply logic, which handles resource-to-string conversion.
     *
     * If a non-resource is provided, this method must return `null` or alternatively throw an
     * `\InvalidArgumentException`, containing a suitable message.
     *
     * When `null` is returned, the next custom string formatter is called. If all custom string formatters return
     * `null`, the default string formatter logic (`DefaultResourceFormatter->formatEnsureString(...)`) is applied.
     *
     * BEWARE: Calling `$this->getTypeFormatter()->cast(...)` or `$this->getTypeFormatter()->typeCast(...)` on the
     * argument resource within this method will cause an endless loop.
     *
     * @throws \InvalidArgumentException
     */
    public function format($resource): ?string;
}
