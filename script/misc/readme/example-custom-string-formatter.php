<?php
declare(strict_types = 1); // README.md.remove
use Kafoso\TypeFormatter\Abstraction\Type\AbstractFormatter;
use Kafoso\TypeFormatter\Collection\Type\StringFormatterCollection;
use Kafoso\TypeFormatter\Encoding;
use Kafoso\TypeFormatter\Type\StringFormatterInterface;
use Kafoso\TypeFormatter\TypeFormatter;
require(__DIR__ . "/../../bootstrap.php"); // README.md.remove

$customTypeFormatter = TypeFormatter::create();
$customTypeFormatter = $customTypeFormatter->withCustomStringFormatterCollection(new StringFormatterCollection([
    new class ($entityManager) extends AbstractFormatter implements StringFormatterInterface
    {
        /**
         * @inheritDoc
         */
        public function format(string $string): ?string
        {
            if ("What do we like?" === $string) {
                return $this->getTypeFormatter()->getDefaultStringFormatter()->format("CAKE!");
            }
            return null; // Pass on to next formatter or lastly DefaultArrayFormatter
        }
    },
]));

echo $customTypeFormatter->cast("What do we like?") . PHP_EOL;

/**
 * Will output:
 * "CAKE!"
 */
