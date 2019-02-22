<?php
declare(strict_types = 1);

namespace Kafoso\TypeFormatter;

class Encoding
{
    /**
     * @var array
     */
    private static $instances = [];

    /**
     * @var string
     */
    protected $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function __toString()
    {
        return $this->name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public static function getInstance(): Encoding
    {
        $className = get_called_class();
        if (false === array_key_exists($className, self::$instances)) {
            self::$instances[$className] = new static(mb_internal_encoding());
        }
        return self::$instances[$className];
    }
}
