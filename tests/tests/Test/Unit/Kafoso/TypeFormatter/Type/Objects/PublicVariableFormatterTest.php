<?php
declare(strict_types = 1);

namespace Kafoso\TypeFormatter\Test\Unit\Kafoso\TypeFormatter\Type\Objects;

use Kafoso\TypeFormatter\Collection\Type\ObjectFormatterCollection;
use Kafoso\TypeFormatter\Type\Objects\PublicVariableFormatter;
use Kafoso\TypeFormatter\TypeFormatter;
use PHPUnit\Framework\TestCase;

class PublicVariableFormatterTest extends TestCase
{
    public function testIsSkippedWhenObjectHasNoPublicVariables()
    {
        $typeFormatter = TypeFormatter::create();
        $publicVariableFormatter = new PublicVariableFormatter;
        $typeFormatter = $typeFormatter->withCustomObjectFormatterCollection(new ObjectFormatterCollection([
            $publicVariableFormatter,
        ]));
        $this->assertNull($publicVariableFormatter->format(new \stdClass));
    }

    public function testWorksWhenObjectHasOnePublicInjectedVariable()
    {
        $typeFormatter = TypeFormatter::create();
        $publicVariableFormatter = new PublicVariableFormatter;
        $object = new \stdClass;
        $object->foo = "bar";
        $typeFormatter = $typeFormatter->withCustomObjectFormatterCollection(new ObjectFormatterCollection([
            $publicVariableFormatter,
        ]));
        $this->assertSame('\stdClass {foo = "bar"}', $publicVariableFormatter->format($object));
    }

    public function testWorksWhenObjectHasMultiplePublicInjectedVariables()
    {
        $typeFormatter = TypeFormatter::create();
        $publicVariableFormatter = new PublicVariableFormatter;
        $object = new \stdClass;
        $object->foo = 1;
        $object->bar = null;
        $object->baz = "hmm";
        $typeFormatter = $typeFormatter->withCustomObjectFormatterCollection(new ObjectFormatterCollection([
            $publicVariableFormatter,
        ]));
        $this->assertSame('\stdClass {foo = 1, bar = null, baz = "hmm"}', $publicVariableFormatter->format($object));
    }

    public function testWorksWhenObjectHasOnePublicVariable()
    {
        $typeFormatter = TypeFormatter::create();
        $publicVariableFormatter = new PublicVariableFormatter;
        $object = new class
        {
            public $foo = "bar";
        };
        $typeFormatter = $typeFormatter->withCustomObjectFormatterCollection(new ObjectFormatterCollection([
            $publicVariableFormatter,
        ]));
        $this->assertRegExp('/^\\\\class@anonymous(.+) \{foo = "bar"\}$/', $publicVariableFormatter->format($object));
    }

    public function testWorksWhenObjectHasMultiplePublicVariables()
    {
        $typeFormatter = TypeFormatter::create();
        $publicVariableFormatter = new PublicVariableFormatter;
        $object = new class
        {
            public $foo = 1;
            public $bar = null;
            public $baz = "hmm";
            private $private = null;
            protected $protected = null;
        };
        $typeFormatter = $typeFormatter->withCustomObjectFormatterCollection(new ObjectFormatterCollection([
            $publicVariableFormatter,
        ]));
        $this->assertRegExp('/^\\\\class@anonymous(.+) \{foo = 1, bar = null, baz = "hmm"\}$/', $publicVariableFormatter->format($object));
    }
}
