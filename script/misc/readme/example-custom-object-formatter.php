<?php
declare(strict_types = 1); // README.md.remove
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
require(__DIR__ . "/../../bootstrap.php"); // README.md.remove

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
