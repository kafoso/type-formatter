<?php
declare(strict_types = 1);

namespace Kafoso\TypeFormatter\Test\Unit\Kafoso\TypeFormatter\Type\Objects;

use Kafoso\TypeFormatter\Collection\Type\ObjectFormatterCollection;
use Kafoso\TypeFormatter\Type\DefaultObjectFormatter;
use Kafoso\TypeFormatter\Type\Objects\ThrowableFormatter;
use Kafoso\TypeFormatter\TypeFormatter;
use PHPUnit\Framework\TestCase;

class ThrowableFormatterTest extends TestCase
{
    public function testItWorksWithNonThrowables()
    {
        $throwableFormatter = new ThrowableFormatter;
        $typeFormatter = TypeFormatter::create();
        $typeFormatter = $typeFormatter->withCustomObjectFormatterCollection(new ObjectFormatterCollection([
            $throwableFormatter,
        ]));
        $this->assertNull($throwableFormatter->format(new \stdClass));
    }

    public function testItWorksWithAnExceptionWithNoPrevious()
    {
        $throwableFormatter = new ThrowableFormatter;
        $typeFormatter = TypeFormatter::create();
        $typeFormatter = $typeFormatter->withCustomObjectFormatterCollection(new ObjectFormatterCollection([
            $throwableFormatter,
        ]));
        $expected = '/^\\\\Exception \{\$code = 0, \$file = "(.+)", \$line = (\d+), \$message = "foo", \$previous = null\}/';
        $this->assertRegExp($expected, $throwableFormatter->format(new \Exception("foo")));
    }

    public function testItWorksWithAnExceptionWithMultiplePreviousFullyPrinted()
    {
        $throwableFormatter = new ThrowableFormatter;
        $typeFormatter = TypeFormatter::create();
        $typeFormatter = $typeFormatter->withObjectDepthMaximum(3);
        $typeFormatter = $typeFormatter->withCustomObjectFormatterCollection(new ObjectFormatterCollection([
            $throwableFormatter,
        ]));
        $third = new \LogicException("baz", 2);
        $second = new \RuntimeException("bar", 1, $third);
        $expected = (
            '/^\\\\Exception \{\$code = 0, \$file = "(.+)", \$line = (\d+), \$message = "foo", \$previous = '
            . '\\\\RuntimeException \{\$code = 1, \$file = "(.+)", \$line = (\d+), \$message = "bar", \$previous = '
            . '\\\\LogicException \{\$code = 2, \$file = "(.+)", \$line = (\d+), \$message = "baz", \$previous = '
            . 'null\}\}\}$/'
        );
        $this->assertRegExp($expected, $throwableFormatter->format(new \Exception("foo", 0, $second)));
    }

    public function testItWorksWithAnExceptionWithMultiplePreviousButLimitedToOne()
    {
        $throwableFormatter = new ThrowableFormatter;
        $typeFormatter = TypeFormatter::create();
        $typeFormatter = $typeFormatter->withObjectDepthMaximum(1);
        $typeFormatter = $typeFormatter->withCustomObjectFormatterCollection(new ObjectFormatterCollection([
            $throwableFormatter,
        ]));
        $third = new \LogicException("baz", 2);
        $second = new \RuntimeException("bar", 1, $third);
        $expected = (
            '/^\\\\Exception \{\$code = 0, \$file = "(.+)", \$line = (\d+), \$message = "foo", \$previous = '
            . '\\\\RuntimeException \{\$code = 1, \$file = "(.+)", \$line = (\d+), \$message = "bar", \$previous = '
            . '\\\\LogicException \{...\}\}\}$/'
        );
        $this->assertRegExp($expected, $throwableFormatter->format(new \Exception("foo", 0, $second)));
    }
}
