<?php
declare(strict_types = 1);

namespace Kafoso\TypeFormatter\Type;

use Kafoso\TypeFormatter\TypeFormatter;

interface FormatterInterface
{
    public function setTypeFormatter(TypeFormatter $typeFormatter): void;

    public function getTypeFormatter(): TypeFormatter;

    public function isPrependingType(): bool;

    public function isSamplifying(): bool;
}
