<?php
declare(strict_types = 1);

namespace Kafoso\TypeFormatter\Test\Unit\Kafoso\TypeFormatter\Type\Objects;

use Kafoso\TypeFormatter\Collection\Type\ObjectFormatterCollection;
use Kafoso\TypeFormatter\Type\Objects\DateTimeInterfaceFormatter;
use Kafoso\TypeFormatter\TypeFormatter;
use PHPUnit\Framework\TestCase;

class DateTimeInterfaceFormatterTest extends TestCase
{
    public function testReturnsNullWhenObjectIsNotQualified()
    {
        $typeFormatter = TypeFormatter::create();
        $dateTimeInterfaceFormatter = new DateTimeInterfaceFormatter;
        $typeFormatter = $typeFormatter->withCustomObjectFormatterCollection(new ObjectFormatterCollection([
            $dateTimeInterfaceFormatter,
        ]));
        $this->assertNull($dateTimeInterfaceFormatter->format(new \stdClass));
    }

    public function testItWorks()
    {
        $typeFormatter = TypeFormatter::create();
        $dateTimeInterfaceFormatter = new DateTimeInterfaceFormatter;
        $typeFormatter = $typeFormatter->withCustomObjectFormatterCollection(new ObjectFormatterCollection([
            $dateTimeInterfaceFormatter,
        ]));
        $object = new \DateTimeImmutable("2019-01-01T00:00:00+00:00");
        $expected = '\\DateTimeImmutable ("2019-01-01T00:00:00+00:00")';
        $this->assertSame($expected, $dateTimeInterfaceFormatter->format($object));
    }
}
