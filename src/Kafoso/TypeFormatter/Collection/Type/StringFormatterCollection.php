<?php
declare(strict_types = 1);

namespace Kafoso\TypeFormatter\Collection\Type;

use Kafoso\TypeFormatter\Abstraction\AbstractObjectCollection;
use Kafoso\TypeFormatter\Type\StringFormatterInterface;

class StringFormatterCollection extends AbstractObjectCollection
{
    public static function getClassName(): string
    {
        return StringFormatterInterface::class;
    }
}
