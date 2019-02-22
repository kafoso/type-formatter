<?php
declare(strict_types = 1);

namespace Kafoso\TypeFormatter\Test\Unit\Kafoso\TypeFormatter\Type\Objects;

use Kafoso\TypeFormatter\Collection\Type\ObjectFormatterCollection;
use Kafoso\TypeFormatter\Type\Objects\DirectoryFormatter;
use Kafoso\TypeFormatter\TypeFormatter;
use PHPUnit\Framework\TestCase;

class DirectoryFormatterTest extends TestCase
{
    public function testReturnsNullWhenObjectIsNotQualified()
    {
        $typeFormatter = TypeFormatter::create();
        $directoryFormatter = new DirectoryFormatter;
        $typeFormatter = $typeFormatter->withCustomObjectFormatterCollection(new ObjectFormatterCollection([
            $directoryFormatter,
        ]));
        $this->assertNull($directoryFormatter->format(new \stdClass));
    }

    public function testItWorks()
    {
        $typeFormatter = TypeFormatter::create();
        $directoryFormatter = new DirectoryFormatter;
        $typeFormatter = $typeFormatter->withCustomObjectFormatterCollection(new ObjectFormatterCollection([
            $directoryFormatter,
        ]));
        $object = dir(__DIR__);
        $expected = '/^\\\\Directory \{\$path = "(.+)"\}$/';
        $this->assertRegExp($expected, $directoryFormatter->format($object));
    }
}
