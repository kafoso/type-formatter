<?php
declare(strict_types = 1); // README.md.remove
use Kafoso\TypeFormatter\Encoding;
use Kafoso\TypeFormatter\TypeFormatter;
require(__DIR__ . "/../../bootstrap.php"); // README.md.remove

$customTypeFormatter = TypeFormatter::create();
$customTypeFormatter = $customTypeFormatter->withArrayDepthMaximum(2);
$customTypeFormatter = $customTypeFormatter->withArraySampleSize(3);
$customTypeFormatter = $customTypeFormatter->withStringSampleSize(4);
$customTypeFormatter = $customTypeFormatter->withStringQuotingCharacter("`");
