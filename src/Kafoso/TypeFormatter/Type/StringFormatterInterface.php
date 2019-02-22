<?php
declare(strict_types = 1);

namespace Kafoso\TypeFormatter\Type;

interface StringFormatterInterface extends FormatterInterface
{
    /**
     * Apply logic, which handles strings. E.g. by samplifying strings, presenting only a substring, which is useful for
     * long strings, which may otherwise clutter the output.
     *
     * When `null` is returned, the next custom string formatter is called. If all custom string formatters return
     * `null`, the default string formatter logic (`DefaultStringFormatter->formatEnsureString(...)`) is applied.
     *
     * BEWARE: Calling `$this->getTypeFormatter()->cast(...)` or `$this->getTypeFormatter()->typeCast(...)` on the
     * argument string within this method will cause an endless loop.
     */
    public function format(string $string): ?string;
}
