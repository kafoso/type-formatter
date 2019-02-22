<?php
declare(strict_types = 1);

namespace Kafoso\TypeFormatter\Collection;

use Kafoso\TypeFormatter\Abstraction\AbstractObjectCollection;
use Kafoso\TypeFormatter\EncryptedString;

class EncryptedStringCollection extends AbstractObjectCollection
{
    public static function getClassName(): string
    {
        return EncryptedString::class;
    }
}
