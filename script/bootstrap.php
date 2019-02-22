<?php
use Kafoso\TypeFormatter\TypeFormatter;

mb_internal_encoding("UTF-8");

define("ROOT_PATH", realpath(__DIR__ . "/.."));

if (false === class_exists(TypeFormatter::class)) {
    require(__DIR__ . "/../vendor/autoload.php");
}
