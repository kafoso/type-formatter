<?php
declare(strict_types = 1); // README.md.remove
use Kafoso\TypeFormatter\TypeFormatter;
require(__DIR__ . "/../../bootstrap.php"); // README.md.remove

$typeFormatter = TypeFormatter::getDefault();
$typeFormatter = $typeFormatter->withArrayDepthMaximum(2);
TypeFormatter::setDefault($typeFormatter);
