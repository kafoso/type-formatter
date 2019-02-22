<?php
declare(strict_types = 1);

namespace Kafoso\TypeFormatter\Collection\Type;

use Kafoso\TypeFormatter\Abstraction\AbstractObjectCollection;
use Kafoso\TypeFormatter\Type\ResourceFormatterInterface;

class ResourceFormatterCollection extends AbstractObjectCollection
{
    public static function getClassName(): string
    {
        return ResourceFormatterInterface::class;
    }
}
