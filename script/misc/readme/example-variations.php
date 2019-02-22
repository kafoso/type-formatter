<?php
declare(strict_types = 1); // README.md.remove
use Kafoso\TypeFormatter\TypeFormatter;
require(__DIR__ . "/../../bootstrap.php"); // README.md.remove

$typeFormatter = TypeFormatter::create();
$typeFormatter = $typeFormatter->withArrayDepthMaximum(3);
TypeFormatter::setVariation("variation1", $typeFormatter);
$typeFormatter = TypeFormatter::getVariation("variation1");
