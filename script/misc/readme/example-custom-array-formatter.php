<?php
declare(strict_types = 1); // README.md.remove
use Kafoso\TypeFormatter\Abstraction\Type\AbstractFormatter;
use Kafoso\TypeFormatter\Collection\Type\ArrayFormatterCollection;
use Kafoso\TypeFormatter\Encoding;
use Kafoso\TypeFormatter\Type\ArrayFormatterInterface;
use Kafoso\TypeFormatter\TypeFormatter;
require(__DIR__ . "/../../bootstrap.php"); // README.md.remove

$customTypeFormatter = TypeFormatter::create();
$customTypeFormatter = $customTypeFormatter->withCustomArrayFormatterCollection(new ArrayFormatterCollection([
    new class extends AbstractFormatter implements ArrayFormatterInterface
    {
        /**
         * @inheritDoc
         */
        public function format(array $array): ?string
        {
            if (1 == count($array)) {
                return print_r($array, true);
            }
            if (2 == count($array)) {
                return "I am an array!";
            }
            if (3 === count($array)) {
                $array[0] = "SURPRISE!";
                // Override and use DefaultArrayFormatter for rendering output
                return $this->getTypeFormatter()->getDefaultArrayFormatter()->format($array);
            }
            return null; // Pass on to next formatter or lastly DefaultArrayFormatter
        }
    }
]));

echo $customTypeFormatter->cast(["foo"]) . PHP_EOL;

/**
 * Will output:
 * Array
 * (
 *     [0] => foo
 * )
 */

echo $customTypeFormatter->cast(["foo", "bar"]) . PHP_EOL;

/**
 * Will output:
 * I am an array!
 */

echo $customTypeFormatter->cast(["foo", "bar", "baz"]) . PHP_EOL;

/**
 * Will output:
 * [0 => "SURPRISE!", 1 => "bar", 2 => "baz"]
 */

echo $customTypeFormatter->cast(["foo", "bar", "baz", "bim"]) . PHP_EOL;

/**
 * Will output:
 * [0 => "foo", 1 => "bar", 2 => "baz", ... and 1 more element] (sample)
 */

echo $customTypeFormatter->typeCast(["foo", "bar", "baz", "bim"]) . PHP_EOL;

/**
 * Will output:
 * (array(4)) [(int) 0 => (string(3)) "foo", (int) 1 => (string(3)) "bar", (int) 2 => (string(3)) "baz", ... and 1 more element] (sample)
 */
