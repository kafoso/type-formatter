<?php
declare(strict_types = 1);

namespace Kafoso\TypeFormatter\Test\Unit\Kafoso\TypeFormatter\Type\Objects;

use Kafoso\TypeFormatter\Collection\Type\ObjectFormatterCollection;
use Kafoso\TypeFormatter\Contract\TextuallyIdentifiableInterface;
use Kafoso\TypeFormatter\Type\DefaultObjectFormatter;
use Kafoso\TypeFormatter\Type\Objects\TextuallyIdentifiableInterfaceFormatter;
use Kafoso\TypeFormatter\TypeFormatter;
use PHPUnit\Framework\TestCase;

class TextuallyIdentifiableInterfaceFormatterTest extends TestCase
{
    public function testReturnsNullWhenObjectIsNotQualified()
    {
        $typeFormatter = TypeFormatter::create();
        $textuallyIdentifiableInterfaceFormatter = new TextuallyIdentifiableInterfaceFormatter;
        $typeFormatter = $typeFormatter->withCustomObjectFormatterCollection(new ObjectFormatterCollection([
            $textuallyIdentifiableInterfaceFormatter,
        ]));
        $this->assertNull($textuallyIdentifiableInterfaceFormatter->format(new \stdClass));
    }

    public function testItWorks()
    {
        $typeFormatter = TypeFormatter::create();
        $textuallyIdentifiableInterfaceFormatter = new TextuallyIdentifiableInterfaceFormatter;
        $typeFormatter = $typeFormatter->withCustomObjectFormatterCollection(new ObjectFormatterCollection([
            $textuallyIdentifiableInterfaceFormatter,
        ]));
        $object = new class ($typeFormatter) implements TextuallyIdentifiableInterface
        {
            /**
             * @var TypeFormatter
             */
            private $typeFormatter;

            /**
             * @var null|int
             */
            private $id = null;

            public function __construct(TypeFormatter $typeFormatter)
            {
                $this->typeFormatter = $typeFormatter;
                $this->id = 22;
            }

            public function toTextualIdentifier(): string
            {
                return sprintf(
                    "\\%s {id = %s}",
                    DefaultObjectFormatter::getClassName($this),
                    $this->typeFormatter->cast($this->id)
                );
            }

            /**
             * @deprecated To be removed in 2.0.0. Instead, use: `toTextualIdentifier`.
             */
            public function getTextualIdentifier(): string
            {
                return $this->toTextualIdentifier();
            }
        };
        $expected = '/^\\\\class@anonymous(.+) \{id = 22\}$/';
        $this->assertRegExp($expected, $textuallyIdentifiableInterfaceFormatter->format($object));
    }
}
