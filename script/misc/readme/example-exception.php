<?php
declare(strict_types = 1); // README.md.remove
use Kafoso\TypeFormatter\TypeFormatter;
require(__DIR__ . "/../../bootstrap.php"); // README.md.remove

/**
 * @param string|int $value
 * @throws \InvalidArgumentException
 */
function foo($value){
    if (false == is_string($value) && false == is_int($value)) {
        throw new \InvalidArgumentException(sprintf(
            "Expects argument \$value to be a string or an integer. Found: %s",
            TypeFormatter::create()->typeCast($value)
        ));
    }
};

foo(["bar"]);

/**
 * Exception message will read:
 * Expects argument $value to be a string or an integer. Found: (array(1)) [(int) 0 => (string(3)) "bar"]
 */
