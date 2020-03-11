<?php
declare(strict_types = 1);

namespace Kafoso\TypeFormatter\Test\Unit\Kafoso\TypeFormatter;

use Kafoso\TypeFormatter\Abstraction\Type\AbstractFormatter;
use Kafoso\TypeFormatter\Collection\Type\ArrayFormatterCollection;
use Kafoso\TypeFormatter\Collection\Type\ObjectFormatterCollection;
use Kafoso\TypeFormatter\Collection\Type\ResourceFormatterCollection;
use Kafoso\TypeFormatter\Collection\Type\StringFormatterCollection;
use Kafoso\TypeFormatter\Collection\EncryptedStringCollection;
use Kafoso\TypeFormatter\Type\DefaultArrayFormatter;
use Kafoso\TypeFormatter\Type\DefaultObjectFormatter;
use Kafoso\TypeFormatter\Type\DefaultResourceFormatter;
use Kafoso\TypeFormatter\Type\DefaultStringFormatter;
use Kafoso\TypeFormatter\Type\ArrayFormatterInterface;
use Kafoso\TypeFormatter\Type\ObjectFormatterInterface;
use Kafoso\TypeFormatter\Type\ResourceFormatterInterface;
use Kafoso\TypeFormatter\Type\StringFormatterInterface;
use Kafoso\TypeFormatter\Encoding;
use Kafoso\TypeFormatter\EncryptedString;
use Kafoso\TypeFormatter\TypeFormatter;
use PHPUnit\Framework\TestCase;

class TypeFormatterTest extends TestCase
{
    public function testBasics()
    {
        $encoding = Encoding::getInstance();
        $typeFormatter = new TypeFormatter($encoding);
        $this->assertInstanceOf(TypeFormatter::class, $typeFormatter);
        $this->assertSame($encoding, $typeFormatter->getEncoding());
        $this->assertSame(TypeFormatter::ARRAY_DEPTH_CURRENT_DEFAULT, $typeFormatter->getArrayDepthCurrent());
        $this->assertSame(TypeFormatter::ARRAY_DEPTH_MAXIMUM_DEFAULT, $typeFormatter->getArrayDepthMaximum());
        $this->assertSame(TypeFormatter::ARRAY_SAMPLE_SIZE_DEFAULT, $typeFormatter->getArraySampleSize());
        $this->assertSame(TypeFormatter::OBJECT_DEPTH_CURRENT_DEFAULT, $typeFormatter->getObjectDepthCurrent());
        $this->assertSame(TypeFormatter::OBJECT_DEPTH_MAXIMUM_DEFAULT, $typeFormatter->getObjectDepthMaximum());
        $this->assertSame(TypeFormatter::STRING_SAMPLE_SIZE_DEFAULT, $typeFormatter->getStringSampleSize());
        $this->assertSame(TypeFormatter::STRING_QUOTING_CHARACTER_DEFAULT, $typeFormatter->getStringQuotingCharacter());
        $this->assertInstanceOf(DefaultArrayFormatter::class, $typeFormatter->getDefaultArrayFormatter());
        $this->assertInstanceOf(DefaultObjectFormatter::class, $typeFormatter->getDefaultObjectFormatter());
        $this->assertInstanceOf(DefaultResourceFormatter::class, $typeFormatter->getDefaultResourceFormatter());
        $this->assertInstanceOf(DefaultStringFormatter::class, $typeFormatter->getDefaultStringFormatter());

        $clone = clone $typeFormatter;
        $this->assertNotSame($typeFormatter->getDefaultArrayFormatter(), $clone->getDefaultArrayFormatter());
        $this->assertNotSame($typeFormatter->getDefaultObjectFormatter(), $clone->getDefaultObjectFormatter());
        $this->assertNotSame($typeFormatter->getDefaultResourceFormatter(), $clone->getDefaultResourceFormatter());
        $this->assertNotSame($typeFormatter->getDefaultStringFormatter(), $clone->getDefaultStringFormatter());
    }

    public function testConstructingThrowsExceptionWhenCallingConstruct()
    {
        try {
            $typeFormatter = TypeFormatter::create();
            $typeFormatter->__construct(Encoding::getInstance());
        } catch (\Throwable $t) {
            $currentThrowable = $t;
            $this->assertSame('RuntimeException', get_class($currentThrowable));
            $this->assertSame('Failed to construct \Kafoso\TypeFormatter\TypeFormatter', $currentThrowable->getMessage());
            $currentThrowable = $currentThrowable->getPrevious();
            $this->assertTrue(is_object($currentThrowable));
            $this->assertSame('LogicException', get_class($currentThrowable));
            $this->assertSame('\Kafoso\TypeFormatter\TypeFormatter is immutable. You are not supposed to call `__construct` directly', $currentThrowable->getMessage());
            $currentThrowable = $currentThrowable->getPrevious();
            $this->assertTrue(is_null($currentThrowable));
            return;
        }
        $this->fail("Exception was never thrown");
    }

    public function testCreateWorks()
    {
        $typeFormatter = TypeFormatter::create();
        $this->assertInstanceOf(TypeFormatter::class, $typeFormatter);
    }

    /**
     * @dataProvider dataProvider_testCastWorks
     */
    public function testCastWorks(string $expected, $value)
    {
        $this->assertSame($expected, TypeFormatter::create()->cast($value));
    }

    public function dataProvider_testCastWorks(): array
    {
        return [
            ['null', null],
            ['true', true],
            ['false', false],
            ['42', 42],
            ['3.14', 3.14],
            ['"foo"', "foo"],
            ['\stdClass', new \stdClass],
            ['[0 => "foo", 1 => 42]', ["foo", 42]],
        ];
    }

    public function testCastWorksWithStringSample()
    {
        $str = str_repeat("a", TypeFormatter::STRING_SAMPLE_SIZE_DEFAULT+1);
        $expected = '"' . str_repeat("a", TypeFormatter::STRING_SAMPLE_SIZE_DEFAULT-4) . ' ..." (sample)';
        $this->assertSame($expected, TypeFormatter::create()->cast($str));
    }

    public function testCastWorksWithoutStringSample()
    {
        $str = str_repeat("a", TypeFormatter::STRING_SAMPLE_SIZE_DEFAULT+1);
        $expected = '"' . str_repeat("a", TypeFormatter::STRING_SAMPLE_SIZE_DEFAULT+1) . '"';
        $this->assertSame($expected, TypeFormatter::create()->cast($str, false));
    }

    public function testCastWorksWithAnonymousClass()
    {
        $class = new class {};
        $found = TypeFormatter::create()->cast($class);
        $this->assertStringStartsWith('\\class@anonymous', $found);
    }

    public function testCastWorksWithResource()
    {
        $found = TypeFormatter::create()->cast(fopen(__FILE__, "r+"));
        $this->assertRegExp('/^`stream` Resource id #\d+$/', $found);
    }

    public function testCastWorksWithArrayAndWithSampling()
    {
        $typeFormatter = TypeFormatter::create();
        $typeFormatter = $typeFormatter->withArraySampleSize(3);
        $typeFormatter = $typeFormatter->withStringSampleSize(5);
        $array = [
            "foobar",
            "loremipsum" => "dolorsit",
            1,
            2,
            3,
        ];
        $expected = '[0 => "f ..." (sample), "loremipsum" => "d ..." (sample), 1 => 1, ... and 2 more elements] (sample)';
        $this->assertSame($expected, $typeFormatter->cast($array, true));
    }

    public function testCastWorksWithArrayButWithoutSampling()
    {
        $typeFormatter = TypeFormatter::create();
        $typeFormatter = $typeFormatter->withArraySampleSize(10);
        $typeFormatter = $typeFormatter->withStringSampleSize(200);
        $array = [
            "foobar",
            "loremipsum" => "dolorsit",
            1,
            2,
            3,
        ];
        $expected = '[0 => "foobar", "loremipsum" => "dolorsit", 1 => 1, 2 => 2, 3 => 3]';
        $this->assertSame($expected, $typeFormatter->cast($array, true));
    }

    /**
     * @dataProvider dataProvider_testCastWorksWithArrayLargerThanSampleSize
     */
    public function testCastWorksWithArrayLargerThanSampleSize(string $expected, array $array)
    {
        $this->assertSame($expected, TypeFormatter::create()->cast($array));
    }

    public function dataProvider_testCastWorksWithArrayLargerThanSampleSize(): array
    {
        return [
            ['[0 => "foo", 1 => 42, 2 => null, ... and 1 more element] (sample)', ["foo", 42, null, false]], // Singular "element"
            ['[0 => 1, 1 => 1, 2 => 1, ... and 97 more elements] (sample)', array_fill(0, 100, 1)], // Plural "elements"
        ];
    }

    public function testCastWorksWithAnAssociativeArray()
    {
        $expected = '["foo" => 1, "bar" => 2, "baz" => 3, ... and 1 more element] (sample)';
        $array = ["foo" => 1, "bar" => 2, "baz" => 3, "bim" => 4];
        $this->assertSame($expected, TypeFormatter::create()->cast($array));
    }

    public function testCastWorksWithAMixedArray()
    {
        $expected = '[0 => "foo", "bar" => 2, 1 => "baz", ... and 1 more element] (sample)';
        $array = ["foo", "bar" => 2, "baz", "bim" => 4];
        $this->assertSame($expected, TypeFormatter::create()->cast($array));
    }

    public function testCastWorksWithMaskedStrings()
    {
        $typeFormatter = TypeFormatter::create();
        $typeFormatter = $typeFormatter->withMaskedEncryptedStringCollection(new EncryptedStringCollection([
            new EncryptedString("bar"),
            new EncryptedString("bim"),
        ]));
        $expected = sprintf(
            '"foo %s baz %s" (masked)',
            TypeFormatter::STRING_MASK,
            TypeFormatter::STRING_MASK
        );
        $this->assertSame($expected, $typeFormatter->cast("foo bar baz bim"));
    }

    public function testCastWorksWithMaskedStringsAndSimplifying()
    {
        $typeFormatter = TypeFormatter::create();
        $typeFormatter = $typeFormatter->withStringSampleSize(10);
        $typeFormatter = $typeFormatter->withMaskedEncryptedStringCollection(new EncryptedStringCollection([
            new EncryptedString("bar"),
            new EncryptedString("bim"),
        ]));
        $expected = '"foo ** ..." (sample,masked)';
        $this->assertSame($expected, $typeFormatter->cast("foo bar baz bim"));
    }

    public function testCastWillCorrectlyMaskArrayKeys()
    {
        $typeFormatter = TypeFormatter::create();
        $typeFormatter = $typeFormatter->withMaskedEncryptedStringCollection(new EncryptedStringCollection([
            new EncryptedString("bar"),
            new EncryptedString("bim"),
        ]));
        $array = ["foo bar baz bim" => "bar"];
        $expected = '["foo ***** baz *****" (masked) => "*****" (masked)]';
        // It's the masked length = 19, not the original length. Don't bleed information about masked string
        $this->assertSame($expected, $typeFormatter->cast($array));
    }

    /**
     * @dataProvider dataProvider_testCastOnMaskedStringsWillNotCauseMaskingToBePartOfOtherMaskings
     */
    public function testCastOnMaskedStringsWillNotCauseMaskingToBePartOfOtherMaskings(
        string $expected,
        string $input,
        EncryptedStringCollection $encryptedStringCollection
    )
    {
        $typeFormatter = TypeFormatter::create();
        $typeFormatter = $typeFormatter->withMaskedEncryptedStringCollection($encryptedStringCollection);
        $this->assertSame($expected, $typeFormatter->cast($input));
    }

    public function dataProvider_testCastOnMaskedStringsWillNotCauseMaskingToBePartOfOtherMaskings()
    {
        return [
            [
                '"foo ***** baz ***** bim" (masked)',
                'foo bar baz *** bim',
                new EncryptedStringCollection([
                    new EncryptedString("***"),
                    new EncryptedString("bar"),
                ]),
            ],
            [
                '"foo ***** baz ***** bim" (masked)',
                'foo bar baz *** bim',
                new EncryptedStringCollection([
                    new EncryptedString("bar"),
                    new EncryptedString("***"),
                ]),
            ],
            [
                '"foo ***** ***** baz bim" (masked)',
                'foo *** bar baz bim',
                new EncryptedStringCollection([
                    new EncryptedString("***"),
                    new EncryptedString("bar"),
                ]),
            ],
            [
                '"foo ***** ***** baz bim" (masked)',
                'foo *** bar baz bim',
                new EncryptedStringCollection([
                    new EncryptedString("bar"),
                    new EncryptedString("***"),
                ]),
            ],
            [
                '"foo ***** bar" (masked)',
                'foo ********** bar',
                new EncryptedStringCollection([
                    new EncryptedString("***"),
                    new EncryptedString("**********"),
                ]),
            ],
        ];
    }

    public function testCastWorksWithCustomFormatters()
    {
        $typeFormatter = TypeFormatter::create();
        $typeFormatter = $typeFormatter->withCustomArrayFormatterCollection(new ArrayFormatterCollection([
            new class extends AbstractFormatter implements ArrayFormatterInterface
            {
                /**
                 * @inheritDoc
                 */
                public function format(array $array): ?string
                {
                    if (array_key_exists("replaceme", $array)) {
                        $array["replaceme"] = "replaced";
                        return $this->getTypeFormatter()->getDefaultArrayFormatter()->format($array);
                    }
                    return null;
                }
            },
        ]));
        $typeFormatter = $typeFormatter->withCustomObjectFormatterCollection(new ObjectFormatterCollection([
            new class extends AbstractFormatter implements ObjectFormatterInterface
            {
                /**
                 * @inheritDoc
                 */
                public function format($object): ?string
                {
                    if (is_object($object) && $object instanceof \DateTimeInterface) {
                        return sprintf(
                            "\\%s (%s)",
                            get_class($object),
                            $object->format("c")
                        );
                    }
                    return null;
                }
            },
            new class extends AbstractFormatter implements ObjectFormatterInterface
            {
                /**
                 * @inheritDoc
                 */
                public function format($object): ?string
                {
                    if (is_object($object) && $object instanceof \Throwable) {
                        return sprintf(
                            "\\%s {\$code = %s, \$file = %s, \$line = %s, \$message = %s}",
                            get_class($object),
                            $this->getTypeFormatter()->cast($object->getCode()),
                            $this->getTypeFormatter()->cast($object->getFile(), false),
                            $this->getTypeFormatter()->cast($object->getLine()),
                            $this->getTypeFormatter()->cast($object->getMessage())
                        );
                    }
                    return null;
                }
            },
        ]));
        $typeFormatter = $typeFormatter->withCustomResourceFormatterCollection(new ResourceFormatterCollection([
            new class extends AbstractFormatter implements ResourceFormatterInterface
            {
                /**
                 * @inheritDoc
                 */
                public function format($resource): ?string
                {
                    if ("stream" == get_resource_type($resource)) {
                        return "YOLO";
                    }
                    return null;
                }
            },
        ]));
        $typeFormatter = $typeFormatter->withCustomStringFormatterCollection(new StringFormatterCollection([
            new class extends AbstractFormatter implements StringFormatterInterface
            {
                /**
                 * @inheritDoc
                 */
                public function format(string $string): ?string
                {
                    if ("foo" === $string) {
                        return $this->getTypeFormatter()->getDefaultStringFormatter()->format("bar");
                    }
                    return null;
                }
            },
        ]));
        $this->assertSame('[0 => 1]', $typeFormatter->cast([1]));
        $this->assertSame('["replaceme" => "replaced"]', $typeFormatter->cast(["replaceme" => "original"]));
        $this->assertSame('\\stdClass', $typeFormatter->cast(new \stdClass));
        $this->assertSame('\\DateTimeImmutable (2019-01-01T00:00:00+00:00)', $typeFormatter->cast(new \DateTimeImmutable("2019-01-01T00:00:00+00:00")));
        $this->assertRegExp('/^\\\\RuntimeException \{\$code = 1, \$file = "(.+)", \$line = \d+, \$message = "test"\}$/', $typeFormatter->cast(new \RuntimeException("test", 1)));
        $this->assertRegExp("/^`xml` Resource id #\d+$/", $typeFormatter->cast(\xml_parser_create("UTF-8")));
        $this->assertSame("YOLO", $typeFormatter->cast(\fopen(__FILE__, "r+")));
        $this->assertSame('"baz"', $typeFormatter->cast("baz"));
        $this->assertSame('"bar"', $typeFormatter->cast("foo"));
    }

    /**
     * @dataProvider dataProvider_testEscapeWorks
     */
    public function testEscapeWorks(string $expected, string $str)
    {
        $this->assertSame($expected, TypeFormatter::create()->escape($str));
    }

    public function dataProvider_testEscapeWorks(): array
    {
        return [
            ['\\\\', '\\'],
            ['\\"', '"'],
            ['\\\\\\"', '\\"'],
            ['\\\\foo\\"', '\\foo"'],
        ];
    }

    /**
     * @dataProvider dataProvider_testTypeCastWorks
     */
    public function testTypeCastWorks(string $expected, $value)
    {
        $this->assertSame($expected, TypeFormatter::create()->typeCast($value));
    }

    public function dataProvider_testTypeCastWorks(): array
    {
        return [
            ['(null) null', null],
            ['(bool) true', true],
            ['(bool) false', false],
            ['(float) 3.14', 3.14],
            ['(int) 42', 42],
            ['(string(3)) "foo"', "foo"],
            ['(object) \\stdClass', new \stdClass],
            ['(array(3)) [(int) 0 => (int) 1, (int) 1 => (int) 2, (int) 2 => (int) 3]', [1, 2, 3]],
        ];
    }

    public function testTypeCastWorksWithStringSample()
    {
        $str = str_repeat("a", TypeFormatter::STRING_SAMPLE_SIZE_DEFAULT+1);
        $expected = sprintf(
            '(string(%d)) "%s ..." (sample)',
            (TypeFormatter::STRING_SAMPLE_SIZE_DEFAULT+1),
            str_repeat("a", TypeFormatter::STRING_SAMPLE_SIZE_DEFAULT-4)
        );
        $this->assertSame($expected, TypeFormatter::create()->typeCast($str));
    }

    public function testTypeCastWorksWithoutStringSample()
    {
        $str = str_repeat("a", TypeFormatter::STRING_SAMPLE_SIZE_DEFAULT+1);
        $expected = sprintf(
            '(string(%d)) "%s"',
            (TypeFormatter::STRING_SAMPLE_SIZE_DEFAULT+1),
            str_repeat("a", TypeFormatter::STRING_SAMPLE_SIZE_DEFAULT+1)
        );
        $this->assertSame($expected, TypeFormatter::create()->typeCast($str, false));
    }

    public function testTypeCastWorksWithAnonymousClass()
    {
        $class = new class {};
        $found = TypeFormatter::create()->typeCast($class);
        $this->assertStringStartsWith('(object) \\class@anonymous', $found);
    }

    public function testTypeCastWorksWithResource()
    {
        $found = TypeFormatter::create()->typeCast(fopen(__FILE__, "r+"));
        $this->assertRegExp('/^\(resource\) `stream` Resource id #\d+$/', $found);
    }

    public function testTypeCastWorksWithArrayAndWithSampling()
    {
        $typeFormatter = TypeFormatter::create();
        $typeFormatter = $typeFormatter->withArraySampleSize(3);
        $typeFormatter = $typeFormatter->withStringSampleSize(5);
        $array = [
            "foobar",
            "loremipsum" => "dolorsit",
            1,
            2,
            3,
        ];
        $expected = '(array(5)) [(int) 0 => (string(6)) "f ..." (sample), (string(10)) "loremipsum" => (string(8)) "d ..." (sample), (int) 1 => (int) 1, ... and 2 more elements] (sample)';
        $this->assertSame($expected, $typeFormatter->typeCast($array, true));
    }

    public function testTypeCastWorksWithArrayButWithoutSampling()
    {
        $typeFormatter = TypeFormatter::create();
        $typeFormatter = $typeFormatter->withArraySampleSize(10);
        $typeFormatter = $typeFormatter->withStringSampleSize(200);
        $array = [
            "foobar",
            "loremipsum" => "dolorsit",
            1,
            2,
            3,
        ];
        $expected = '(array(5)) [(int) 0 => (string(6)) "foobar", (string(10)) "loremipsum" => (string(8)) "dolorsit", (int) 1 => (int) 1, (int) 2 => (int) 2, (int) 3 => (int) 3]';
        $this->assertSame($expected, $typeFormatter->typeCast($array, true));
    }

    /**
     * @dataProvider dataProvider_testTypeCastWorksWithArrayLargerThanSampleSize
     */
    public function testTypeCastWorksWithArrayLargerThanSampleSize(string $expected, array $array)
    {
        $this->assertSame($expected, TypeFormatter::create()->typeCast($array));
    }

    public function dataProvider_testTypeCastWorksWithArrayLargerThanSampleSize(): array
    {
        return [
            ['(array(4)) [(int) 0 => (string(3)) "foo", (int) 1 => (int) 42, (int) 2 => (null) null, ... and 1 more element] (sample)', ["foo", 42, null, false]], // Singular "element"
            ['(array(100)) [(int) 0 => (int) 1, (int) 1 => (int) 1, (int) 2 => (int) 1, ... and 97 more elements] (sample)', array_fill(0, 100, 1)], // Plural "elements"
        ];
    }

    public function testTypeCastWorksWithAnAssociativeArray()
    {
        $expected = '(array(4)) [(string(3)) "foo" => (int) 1, (string(3)) "bar" => (int) 2, (string(3)) "baz" => (int) 3, ... and 1 more element] (sample)';
        $array = ["foo" => 1, "bar" => 2, "baz" => 3, "bim" => 4];
        $this->assertSame($expected, TypeFormatter::create()->typeCast($array));
    }

    public function testTypeCastWorksWithAMixedArray()
    {
        $expected = '(array(4)) [(int) 0 => (string(3)) "foo", (string(3)) "bar" => (int) 2, (int) 1 => (string(3)) "baz", ... and 1 more element] (sample)';
        $array = ["foo", "bar" => 2, "baz", "bim" => 4];
        $this->assertSame($expected, TypeFormatter::create()->typeCast($array));
    }

    public function testTypeCastWorksWithMaskedStrings()
    {
        $typeFormatter = TypeFormatter::create();
        $typeFormatter = $typeFormatter->withMaskedEncryptedStringCollection(new EncryptedStringCollection([
            new EncryptedString("bar"),
            new EncryptedString("bim"),
        ]));
        $expected = sprintf(
            '(string(19)) "foo %s baz %s" (masked)',
            TypeFormatter::STRING_MASK,
            TypeFormatter::STRING_MASK
        );
        $this->assertSame($expected, $typeFormatter->typeCast("foo bar baz bim"));
    }

    public function testTypeCastWorksWithMaskedStringsAndSimplifying()
    {
        $typeFormatter = TypeFormatter::create();
        $typeFormatter = $typeFormatter->withStringSampleSize(10);
        $typeFormatter = $typeFormatter->withMaskedEncryptedStringCollection(new EncryptedStringCollection([
            new EncryptedString("bar"),
            new EncryptedString("bim"),
        ]));
        $expected = '(string(19)) "foo ** ..." (sample,masked)';
        $this->assertSame($expected, $typeFormatter->typeCast("foo bar baz bim"));
    }

    public function testTypeCastWillCorrectlyMaskArrayKeys()
    {
        $typeFormatter = TypeFormatter::create();
        $typeFormatter = $typeFormatter->withMaskedEncryptedStringCollection(new EncryptedStringCollection([
            new EncryptedString("bar"),
            new EncryptedString("bim"),
        ]));
        $array = ["foo bar baz bim" => "bar"];
        $expected = '(array(1)) [(string(19)) "foo ***** baz *****" (masked) => (string(5)) "*****" (masked)]';
        // It's the masked length = 19, not the original length. Don't bleed information about masked string
        $this->assertSame($expected, $typeFormatter->typeCast($array));
    }

    public function testQuoteAndEscapeWorks()
    {
        $this->assertSame('"\\\\foo\\""', TypeFormatter::create()->quoteAndEscape('\\foo"'));
    }

    /**
     * @dataProvider dataProvider_testWithStringQuotingCharacterThrowsExceptionWhenQuotingCharacterIsInvalid
     */
    public function testWithStringQuotingCharacterThrowsExceptionWhenQuotingCharacterIsInvalid($value)
    {
        $typeFormatter = TypeFormatter::create();
        try {
            $typeFormatter->withStringQuotingCharacter($value);
        } catch (\Throwable $t) {
            $currentThrowable = $t;
            $this->assertSame('UnexpectedValueException', get_class($currentThrowable));
            $this->assertRegExp('/^Argument \$stringQuotingCharacter must be exactly 1 character\. Found: \(string\(\d+\)\) ".*"$/', $currentThrowable->getMessage());
            $currentThrowable = $currentThrowable->getPrevious();
            $this->assertTrue(is_null($currentThrowable));
            return;
        }
        $this->fail("Exception was never thrown");
    }

    public function dataProvider_testWithStringQuotingCharacterThrowsExceptionWhenQuotingCharacterIsInvalid(): array
    {
        return [
            [''],
            ['""'],
        ];
    }

    /**
     * @runInSeparateProcess
     */
    public function testSetAndGetDefault()
    {
        $typeFormatterA = TypeFormatter::getDefault();
        $typeFormatterB = clone $typeFormatterA;
        TypeFormatter::setDefault($typeFormatterB);
        $typeFormatterC = TypeFormatter::getDefault();
        $this->assertNotSame($typeFormatterA, $typeFormatterC);
        $this->assertSame($typeFormatterB, $typeFormatterC);
    }
}
