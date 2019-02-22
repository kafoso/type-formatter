<?php
declare(strict_types = 1); // README.md.remove
use Kafoso\TypeFormatter\TypeFormatter;
require(__DIR__ . "/../../bootstrap.php"); // README.md.remove

$typeFormatter = TypeFormatter::create();

echo sprintf(
    "%s %s %s %s",
    $typeFormatter->cast(null),
    $typeFormatter->cast(true),
    $typeFormatter->cast("foo"),
    $typeFormatter->cast(new \stdClass)
);

/**
 * Will output:
 * null true "foo" \stdClass
 */
