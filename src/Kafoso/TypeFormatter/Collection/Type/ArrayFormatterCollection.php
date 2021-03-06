<?php
declare(strict_types = 1);

namespace Kafoso\TypeFormatter\Collection\Type;

use Kafoso\TypeFormatter\Abstraction\AbstractObjectCollection;
use Kafoso\TypeFormatter\Type\ArrayFormatterInterface;

class ArrayFormatterCollection extends AbstractObjectCollection
{
    public static function getClassName(): string
    {
        return ArrayFormatterInterface::class;
    }
}
