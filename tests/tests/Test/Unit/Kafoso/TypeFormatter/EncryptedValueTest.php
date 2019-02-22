<?php
declare(strict_types = 1);

namespace Kafoso\TypeFormatter\Test\Unit\Kafoso\TypeFormatter;

use Kafoso\TypeFormatter\EncryptedString;
use PHPUnit\Framework\TestCase;

class EncryptedStringTest extends TestCase
{
    public function testBasics()
    {
        $encryptedString = new EncryptedString("foo");
        $this->assertInstanceOf(EncryptedString::class, $encryptedString);
        $this->assertSame("foo", $encryptedString->decrypt());

        $reflectionObject = new \ReflectionObject($encryptedString);
        $reflectionProperty = $reflectionObject->getProperty("encryptedString");
        $reflectionProperty->setAccessible(true);
        $encryptedString = $reflectionProperty->getValue($encryptedString);
        $this->assertNotSame("foo", $encryptedString);
    }
}
