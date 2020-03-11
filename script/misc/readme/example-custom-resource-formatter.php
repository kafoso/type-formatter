<?php
declare(strict_types = 1); // README.md.remove
use Kafoso\TypeFormatter\Abstraction\Type\AbstractFormatter;
use Kafoso\TypeFormatter\Collection\Type\ResourceFormatterCollection;
use Kafoso\TypeFormatter\Encoding;
use Kafoso\TypeFormatter\Type\ResourceFormatterInterface;
use Kafoso\TypeFormatter\TypeFormatter;
require(__DIR__ . "/../../bootstrap.php"); // README.md.remove

$customTypeFormatter = TypeFormatter::create();
$customTypeFormatter = $customTypeFormatter->withCustomResourceFormatterCollection(new ResourceFormatterCollection([
    new class extends AbstractFormatter implements ResourceFormatterInterface
    {
        /**
         * @inheritDoc
         */
        public function format($resource): ?string
        {
            if (false == is_resource($resource)) {
                return null; // Pass on to next formatter or lastly DefaultResourceFormatter
            }
            if ("stream" === get_resource_type($resource)) {
                return "opendir/fopen/tmpfile/popen/fsockopen/pfsockopen {$resource}";
            }
            return null; // Pass on to next formatter or lastly DefaultResourceFormatter
        }
    },
    new class extends AbstractFormatter implements ResourceFormatterInterface
    {
        /**
         * @inheritDoc
         */
        public function format($resource): ?string
        {
            if (false == is_resource($resource)) {
                return null; // Pass on to next formatter or lastly DefaultResourceFormatter
            }
            if ("xml" === get_resource_type($resource)) {
                return "XML {$resource}";
            }
            return null; // Pass on to next formatter or lastly DefaultResourceFormatter
        }
    },
]));

echo $customTypeFormatter->cast(fopen(__FILE__, "r+")) . PHP_EOL;

/**
 * Will output:
 * opendir/fopen/tmpfile/popen/fsockopen/pfsockopen Resource id #<id>
 */

echo $customTypeFormatter->cast(\xml_parser_create("UTF-8")) . PHP_EOL;

/**
 * Will output:
 * XML Resource id #<id>
 */
