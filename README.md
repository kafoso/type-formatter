PHP Type Formatter
===============================

Minimalistic, lightweight library for converting PHP data types to readable strings. Great for type-safe outputs, exception messages, transparency during debugging, and similar things. Also helps avoiding innate problems such as printing recursive objects and large arrays.

[comment]: # (The README.md is generated using `script/generate-readme.php`)


<a name="requirements"></a>
# Requirements

```json
"php": "^7.1",
"ext-mbstring": "^7.1",
"doctrine/collections": "^1"
```

For more information, see the [`composer.json`](composer.json) file.

# License & Disclaimer

See [`LICENSE`](LICENSE) file. Basically: Use this library at your own risk.

# Installation

Via [Composer](https://getcomposer.org/) (https://packagist.org/packages/kafoso/type-formatter):

    composer install kafoso/type-formatter

Via GitHub:

    git clone git@github.com:kafoso/type-formatter.git

# Fundamentals

## Type conversions to string

The data types are converted as illustrated in the table below.

|Type|Conversion logic|Example(s)|Note|
|---|---|---|---|---|
|Null|As is.|`null`||
|Booleans|As is.|`true`<br>`false`||
|Float numbers|As is.|`3.14`|Standard float-to-string conversion rounding will occur, as produced by `strval(3.14)`.|
|Integers|As is.|`42`||
|Strings|As is or as a sample (substring).|`"foo"`<br>`"bar ..." (sample)`|If you wish to control how strings are presented or apply conditions, you may do so by providing an instance of `\Kafoso\TypeFormatter\Type\StringFormatterInterface`. More on this interface and implementation <a href="#usage--type-specific-formatters--custom-string-formatter">further down</a>.|
|Arrays|As is or as a sample.|`[0 => "foo"]`<br><br>`[0 => "bar" ... and 9 more elements]`|**Sub-arrays**<br>By default, no sub-arrays are displayed; i.e. the depth is zero. However, a custom depth may be specified.<br>Sub-arrays with depth 0 (zero) may appear as such: `[0 => (array(1)) [...]]`<br>Sub-arrays with depth 1 may appear as such: `[0 => (array(1)) ["foo"]]`<br><br>**Sampling and sample size**<br>By default, a maximum of 3 elements are displayed, before the " ... and X more elements" message is displayed. This number is also customizible.<br><br>**Custom array-to-string conversion**<br>If you wish to customize how arrays are being converted to a string, you may do so by providing an instance of `\Kafoso\TypeFormatter\Type\ArrayFormatterInterface`. More on this interface and implementation <a href="#usage--type-specific-formatters--custom-array-formatter">further down</a>.|
|Objects|Class namespace with leading backslash.|`\stdClass`<br><br>`\class@anonymous/foo/bar/baz.php0x11038bd57`|Objects are rather complex types. As such, something sensible besides its class name cannot be reliably displayed. Not even using `__toString` or similar methods.<br><br>**Custom object-to-string conversion**<br>If you wish to customize how objects are being converted to a string, you may do so by providing an instance of `\Kafoso\TypeFormatter\Type\ObjectFormatterInterface`. More on this interface and implementation <a href="#usage--type-specific-formatters--custom-object-formatter">further down</a>.<br>This is especially useful for displaying relevant information in classes such as IDs in [Doctrine ORM entities](https://github.com/doctrine/orm).|
|Resource|A text and the resource's ID.|`#Resource id #2`|Resources can be many different things. A file pointer, database connection, image canvas, etc. As such, only the bare minimum of information is displayed.<br><br>**Custom resource-to-string conversion**<br>If you wish to customize how resources are being converted to a string, you may do so by providing an instance of `\Kafoso\TypeFormatter\Type\ResourceFormatterInterface`. More on this interface and implementation <a href="#usage--type-specific-formatters--custom-resource-formatter">further down</a>.|

# Output examples

## Echo

```php
<?php
use Kafoso\TypeFormatter\TypeFormatter;

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

```

## Exception

```php
<?php
use Kafoso\TypeFormatter\TypeFormatter;

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

```

# Usage

`\Kafoso\TypeFormatter\TypeFormatter` is immutable. As such, it may only be configured upon construction.

## The standard formatter

By default, `Kafoso\TypeFormatter\TypeFormatter::create()` returns a new instance every time. If you wish to re-use the same instance over and over, you have two options.

**Option 1:** Store it in a variable and use that. As such:

```php
<?php
use Kafoso\TypeFormatter\TypeFormatter;

$typeFormatter = TypeFormatter::create();
```

**Option 2:** Use a dependency container; see below.

## Dependency container (default & variations)

For ease-of-use, you may store formatters statically in `Kafoso\TypeFormatter\TypeFormatter`.

You may specify 2 types of dependencies.

**Default:**

```php
<?php
use Kafoso\TypeFormatter\TypeFormatter;

$typeFormatter = TypeFormatter::getDefault();
$typeFormatter = $typeFormatter->withArrayDepthMaximum(2);
TypeFormatter::setDefault($typeFormatter);

```

**Variations:**

```php
<?php
use Kafoso\TypeFormatter\TypeFormatter;

$typeFormatter = TypeFormatter::create();
$typeFormatter = $typeFormatter->withArrayDepthMaximum(3);
TypeFormatter::setVariation("variation1", $typeFormatter);
$typeFormatter = TypeFormatter::getVariation("variation1");

```

### Use a real Dependency Injection Container

Alternatively, use an actual Dependency Injection Container (DIC) such as [Pimple](https://pimple.symfony.com/). However, this means you will have to pass around the dependencies everywhere you need them, which - from a SOLID perspective - is nice, but not always very practical.

## A custom basic formatter

You may customize the formatter to your specific needs, e.g. changing string sample size, array depth, or providing custom array and/or object formatters. Afterwards, you may store it as the default or a variation for later re-use.

```php
<?php
use Kafoso\TypeFormatter\Encoding;
use Kafoso\TypeFormatter\TypeFormatter;

$customTypeFormatter = TypeFormatter::create();
$customTypeFormatter = $customTypeFormatter->withArrayDepthMaximum(2);
$customTypeFormatter = $customTypeFormatter->withArraySampleSize(3);
$customTypeFormatter = $customTypeFormatter->withStringSampleSize(4);
$customTypeFormatter = $customTypeFormatter->withStringQuotingCharacter("`");

```

## Type specific formatters

The following type specific formatters exists, which may help providing additional information. Especially useful for printing relevant information relating to an object.

These formatters are injected into the desired instance of `\Kafoso\TypeFormatter\TypeFormatter` using the `with*` methods. Do however notice, that `\Kafoso\TypeFormatter\TypeFormatter` is immutable.

|Data type|`\Kafoso\TypeFormatter\TypeFormatter` method|Interface|Note|
|---|---|---|---|
|`array`|`withCustomArrayFormatterCollection`|`\Kafoso\TypeFormatter\Type\ArrayFormatterInterface`|See usage example in <a href="#usage--type-specific-formatters--custom-array-formatter">Custom array formatter</a> further down.|
|`object`|`withCustomObjectFormatterCollection`|`\Kafoso\TypeFormatter\Type\ObjectFormatterInterface`|See usage example in <a href="#usage--type-specific-formatters--custom-object-formatter">Custom object formatter</a> further down.<br><br>**Notice:** This library ships with a series of ready-to-use object formatters. These may be found under `\Kafoso\TypeFormatter\Type\Objects`. Details [below](#usage--type-specific-formatters--included-object-formatters).|
|`resource`|`withCustomResourceFormatterCollection`|`\Kafoso\TypeFormatter\Type\ResourceFormatterInterface`|See usage example in <a href="#usage--type-specific-formatters--custom-resource-formatter">Custom resource formatter</a> further down.|
|`string`|`withCustomStringFormatterCollection`|`\Kafoso\TypeFormatter\Type\StringFormatterInterface`|See usage example in <a href="#usage--type-specific-formatters--custom-string-formatter">Custom string formatter</a> further down.|

Multiple custom formatters can be provided, such that they each handle only specific cases. Order is significant.

Ultimately, all custom formatters fall back to their respective standard formatters.

<a name="usage--type-specific-formatters--included-object-formatters"></a>
### Included object formatters

The following object formatters are readily available. You may use them as-is or extend them, providing your own custom logic. Everything is very Open-closed Principle.

**Namespace:** `\Kafoso\TypeFormatter\Type\Objects`

|Class name|Description|Output example(s)|
|---|---|---|
|`DateTimeInterfaceFormatter`|Formats `\DateTimeInterface` objects, appending ISO 8601 time in parenthesis.|`\DateTimeImmutable ("2019-01-01T00:00:00+00:00")`|
|`DirectoryFormatter`|Formats `\Directory` objects, as produced by `dir(__DIR__)`.|`\Directory ($path = "/foo.php")`|
|`DoctrineEntityFormatter`|Formats [Doctrine ORM](https://github.com/doctrine/orm) entities using the provided `\Doctrine\ORM\EntityManager`.|`\User {$id = 1}`<br><br>`\Message {$uuid = "ad39f689-1070-41cd-9e0f-17112abdfc85"}`|
|`PublicVariableFormatter`|Formats any object which has publicly accessible variables.|`\stdClass {$foo = "bar"}`|
|`TextuallyIdentifiableInterfaceFormatter`|Formats objects, which implement the interface `\Kafoso\TypeFormatter\Contract\TextuallyIdentifiableInterface`.|`\MyUserClass (USER.ID = 22)`|
|`ThrowableFormatter`|Formats instances of `\Throwable`.<br>**Notice:** The output is greatly simplified compared to properly dumping a `\Throwable` with stack trace and everything else.|`\RuntimeException {$code = 0, $file = "/foo.php", $line = 22, $message = "bar", $previous = null}`|

<a name="usage--type-specific-formatters--custom-array-formatter"></a>
### Custom array formatter

```php
<?php
use Kafoso\TypeFormatter\Abstraction\Type\AbstractFormatter;
use Kafoso\TypeFormatter\Collection\Type\ArrayFormatterCollection;
use Kafoso\TypeFormatter\Encoding;
use Kafoso\TypeFormatter\Type\ArrayFormatterInterface;
use Kafoso\TypeFormatter\TypeFormatter;

$customTypeFormatter = TypeFormatter::create();
$customTypeFormatter = $customTypeFormatter->withCustomArrayFormatterCollection(new ArrayFormatterCollection([
    new class extends AbstractFormatter implements ArrayFormatterInterface
    {
        /**
         * @inheritDoc
         */
        public function format(array $array): ?string
        {
            if (1 == count($array)) {
                return print_r($array, true);
            }
            if (2 == count($array)) {
                return "I am an array!";
            }
            if (3 === count($array)) {
                $array[0] = "SURPRISE!";
                // Override and use DefaultArrayFormatter for rendering output
                return $this->getTypeFormatter()->getDefaultArrayFormatter()->format($array);
            }
            return null; // Pass on to next formatter or lastly DefaultArrayFormatter
        }
    }
]));

echo $customTypeFormatter->cast(["foo"]) . PHP_EOL;

/**
 * Will output:
 * Array
 * (
 *     [0] => foo
 * )
 */

echo $customTypeFormatter->cast(["foo", "bar"]) . PHP_EOL;

/**
 * Will output:
 * I am an array!
 */

echo $customTypeFormatter->cast(["foo", "bar", "baz"]) . PHP_EOL;

/**
 * Will output:
 * [0 => "SURPRISE!", 1 => "bar", 2 => "baz"]
 */

echo $customTypeFormatter->cast(["foo", "bar", "baz", "bim"]) . PHP_EOL;

/**
 * Will output:
 * [0 => "foo", 1 => "bar", 2 => "baz", ... and 1 more element] (sample)
 */

echo $customTypeFormatter->typeCast(["foo", "bar", "baz", "bim"]) . PHP_EOL;

/**
 * Will output:
 * (array(4)) [(int) 0 => (string(3)) "foo", (int) 1 => (string(3)) "bar", (int) 2 => (string(3)) "baz", ... and 1 more element] (sample)
 */

```

<a name="usage--type-specific-formatters--custom-object-formatter"></a>
### Custom object formatter

In this example, `\DateTimeInterface`, `\Throwable`, and the [Doctrine ORM](https://github.com/doctrine/orm) EntityManager is utilized to supply good real-world use cases.

```php
<?php
use Doctrine\Common\Persistence\Proxy;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadataFactory;
use Kafoso\TypeFormatter\Abstraction\Type\AbstractFormatter;
use Kafoso\TypeFormatter\Collection\Type\ObjectFormatterCollection;
use Kafoso\TypeFormatter\Encoding;
use Kafoso\TypeFormatter\Type\DefaultObjectFormatter;
use Kafoso\TypeFormatter\Type\ObjectFormatterInterface;
use Kafoso\TypeFormatter\TypeFormatter;
use PHPUnit\Framework\MockObject\Generator;
use PHPUnit\Framework\TestCase;

$generator = new Generator;
$entityManager = $generator->getMock(EntityManager::class, [], [], '', false);
$metadataFactory = $generator->getMock(ClassMetadataFactory::class, [], [], '', false);
$metadataFactory
    ->expects(TestCase::any())
    ->method('isTransient')
    ->withConsecutive(TestCase::equalTo('User'), TestCase::equalTo('stdClass'))
    ->willReturnOnConsecutiveCalls(TestCase::returnValue(false), TestCase::returnValue(true));
$entityManager
    ->expects(TestCase::any())
    ->method('getMetadataFactory')
    ->will(TestCase::returnValue($metadataFactory));

$customTypeFormatter = TypeFormatter::create();
$customTypeFormatter = $customTypeFormatter->withCustomObjectFormatterCollection(new ObjectFormatterCollection([
    new class ($entityManager) extends AbstractFormatter implements ObjectFormatterInterface
    {
        /**
         * @inheritDoc
         */
        public function format($object): ?string
        {
            if (false == is_object($object)) {
                return null; // Pass on to next formatter or lastly DefaultArrayFormatter
            }
            if ($object instanceof \DateTimeInterface) {
                return sprintf(
                    "\\%s (%s)",
                    DefaultObjectFormatter::getClassName($object),
                    $object->format("c")
                );
            }
            return null; // Pass on to next formatter or lastly DefaultArrayFormatter
        }
    },
    new class extends AbstractFormatter implements ObjectFormatterInterface
    {
        /**
         * @inheritDoc
         */
        public function format($object): ?string
        {
            if (false == is_object($object)) {
                return null; // Pass on to next formatter or lastly DefaultArrayFormatter
            }
            if ($object instanceof \Throwable) {
                return sprintf(
                    "\\%s {\$code = %s, \$file = %s, \$line = %s, \$message = %s}",
                    DefaultObjectFormatter::getClassName($object),
                    $this->getTypeFormatter()->cast($object->getCode()),
                    $this->getTypeFormatter()->cast($object->getFile(), false),
                    $this->getTypeFormatter()->cast($object->getLine()),
                    $this->getTypeFormatter()->cast($object->getMessage())
                );
            }
            return null; // Pass on to next formatter or lastly DefaultArrayFormatter
        }
    },
    new class ($entityManager) extends AbstractFormatter implements ObjectFormatterInterface
    {
        /**
         * @var EntityManager
         */
        private $entityManager;

        public function __construct(EntityManager $entityManager)
        {
            $this->entityManager = $entityManager;
        }

        /**
         * @inheritDoc
         */
        public function format($object): ?string
        {
            if (false == is_object($object)) {
                return null; // Pass on to next formatter or lastly DefaultArrayFormatter
            }
            $className = ($object instanceof Proxy) ? get_parent_class($object) : DefaultObjectFormatter::getClassName($object);
            $isEntity = (false == $this->entityManager->getMetadataFactory()->isTransient($className));
            $id = null;
            if ($isEntity && method_exists($object, 'getId')) {
                // You may of course implement logic, which can extract and present any @ORM\Id columns, even composite IDs.
                $id = $object->getId();
            }
            if (is_int($id)) {
                return sprintf(
                    "\\%s {\$id = %d}",
                    $className,
                    $id
                );
            }
            return null; // Pass on to next formatter or lastly DefaultArrayFormatter
        }
    },
]));

echo $customTypeFormatter->cast(new \stdClass) . PHP_EOL;

/**
 * Will output (standard TypeFormatter object-to-string output):
 * \stdClass
 */

echo $customTypeFormatter->cast(new \DateTimeImmutable("2019-01-01T00:00:00+00:00")) . PHP_EOL;

/**
 * Will output:
 * \DateTimeImmutable ("2019-01-01T00:00:00+00:00")
 */

class User
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }
}
$doctrineEntity = new \User;

// Pretend we fetch it from a database
$reflectionObject = new \ReflectionObject($doctrineEntity);
$reflectionProperty = $reflectionObject->getProperty("id");
$reflectionProperty->setAccessible(true);
$reflectionProperty->setValue($doctrineEntity, 1);

echo $customTypeFormatter->cast($doctrineEntity) . PHP_EOL;

/**
 * Will output:
 * \User {$id = 1}
 */

echo $customTypeFormatter->cast(new \RuntimeException("test", 1)) . PHP_EOL;

/**
 * Will output:
 * \RuntimeException {$code = 1, $file = "<file>", $line = <line>, $message = "test"}
 * , where:
 *    - <file> is the path this this file.
 *    - <line> is the line number at which the \RuntimeException is instantiated.
 */

```

<a name="usage--type-specific-formatters--custom-resource-formatter"></a>
### Custom resource formatter

```php
<?php
use Kafoso\TypeFormatter\Abstraction\Type\AbstractFormatter;
use Kafoso\TypeFormatter\Collection\Type\ResourceFormatterCollection;
use Kafoso\TypeFormatter\Encoding;
use Kafoso\TypeFormatter\Type\ResourceFormatterInterface;
use Kafoso\TypeFormatter\TypeFormatter;

$customTypeFormatter = TypeFormatter::create();
$customTypeFormatter = $customTypeFormatter->withCustomResourceFormatterCollection(new ResourceFormatterCollection([
    new class ($entityManager) extends AbstractFormatter implements ResourceFormatterInterface
    {
        /**
         * @inheritDoc
         */
        public function format($resource): ?string
        {
            if (false == is_resource($resource)) {
                return null; // Pass on to next formatter or lastly DefaultArrayFormatter
            }
            if ("stream" === get_resource_type($resource)) {
                return "opendir/fopen/tmpfile/popen/fsockopen/pfsockopen {$resource}";
            }
            return null; // Pass on to next formatter or lastly DefaultArrayFormatter
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
                return null; // Pass on to next formatter or lastly DefaultArrayFormatter
            }
            if ("xml" === get_resource_type($resource)) {
                return "XML {$resource}";
            }
            return null; // Pass on to next formatter or lastly DefaultArrayFormatter
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

```

<a name="usage--type-specific-formatters--custom-string-formatter"></a>
### Custom string formatter

```php
<?php
use Kafoso\TypeFormatter\Abstraction\Type\AbstractFormatter;
use Kafoso\TypeFormatter\Collection\Type\StringFormatterCollection;
use Kafoso\TypeFormatter\Encoding;
use Kafoso\TypeFormatter\Type\StringFormatterInterface;
use Kafoso\TypeFormatter\TypeFormatter;

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

```

# Tests

## Test/development requirements

To run tests, fix bugs, provide features, etc. the following is required:

- A system capable of running a virtual machine with [ubuntu/xenial64](https://app.vagrantup.com/ubuntu/boxes/xenial64) (currently Ubuntu 16.04.5 LTS).
- [Virtualbox](https://www.virtualbox.org/) >= 5.1.0
- [Vagrant](https://www.vagrantup.com/) >= 2.0.0
- [Ruby](https://www.ruby-lang.org/en/) (programming language) for installing the Vagrant box.

You may of course install everything manually using your own VM setup. For help and a stack list (required apt-get packages), see the [tests/Vagrantfile](tests/Vagrantfile).

## Installation to run tests

A few steps are required to run all tests.

**Unit tests** ([tests/tests/Test/Unit](tests/tests/Test/Unit)) will run on all environments that conform to the basic [requirements](#requirements).

**Integration tests** ([tests/tests/Test/Integration](tests/tests/Test/Integration)) require the following because they test against a running Firebird database in the VM.

To set up the Vagrant box, follow these steps:

1. `composer install`
2. `cd tests`
3. `vagrant up`<br>Install/provision the VM.
4. `vagrant ssh`
5. `sudo su`
6. To install MySQL, follow this guide, [https://www.digitalocean.com/community/tutorials/how-to-install-mysql-on-ubuntu-18-04](https://www.digitalocean.com/community/tutorials/how-to-install-mysql-on-ubuntu-18-04). Essentially:
  - `apt update`
  - `apt install mysql-server`
  - `mysql_secure_installation` and set up the MySQL Server.
    - Password for `root` user: `8364f9f87133242a9bd8d230da24379d`

## Running tests

For all tests, first follow these steps:

**Unit tests** will run on most systems.

```
cd tests
php ../bin/phpunit tests/Test/Unit
```

**Integration tests** require that you run them in the Vagrant VM, which in turn require that you set up the MySQL Server mentioned above.

```
cd tests
vagrant ssh
vagrant@ubuntu-xenial:~$ sudo su
root@ubuntu-xenial:/home/vagrant# cd /var/git/kafoso/type-formatter/tests
root@ubuntu-xenial:/var/git/kafoso/type-formatter/tests# php ../bin/phpunit tests/Test/Integration
```

Unit tests may of course also be run inside the Vagrant box.

# Credits

## Authors

- Kasper Søfren<br>E-mail: <a href="mailto:soefritz@gmail.com">soefritz@gmail.com</a><br>Homepage: <a href="https://github.com/kafoso">https://github.com/kafoso</a>
