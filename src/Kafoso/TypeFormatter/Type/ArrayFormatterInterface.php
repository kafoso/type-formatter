<?php
declare(strict_types = 1);

namespace Kafoso\TypeFormatter\Type;

interface ArrayFormatterInterface extends FormatterInterface
{
    /**
     * Apply logic, which converts an array's contents into a human readable string. Employed methods may include custom
     * conversion, `json_encode`, `serialize`, and more.
     *
     * When `null` is returned, the next custom array formatter is called. If all custom array formatters return `null`,
     * the default array-to-string logic (`DefaultArrayFormatter->formatEnsureString(...)`) is applied.
     *
     * BEWARE: Calling `$this->getTypeFormatter()->cast(...)` or `$this->getTypeFormatter()->typeCast(...)` on the
     * argument array within this method will cause an endless loop.
     */
    public function format(array $array): ?string;
}
