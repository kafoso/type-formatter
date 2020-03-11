<?php
declare(strict_types = 1);

namespace Kafoso\TypeFormatter\Test\Unit\Kafoso\TypeFormatter\Type\Objects;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataFactory;
use Kafoso\TypeFormatter\Collection\Type\ObjectFormatterCollection;
use Kafoso\TypeFormatter\TestResource\Unit\Kafoso\TypeFormatter\Type\Objects\DoctrineEntityFormatterTest\testItWorksWithASingleEntityId;
use Kafoso\TypeFormatter\TestResource\Unit\Kafoso\TypeFormatter\Type\Objects\DoctrineEntityFormatterTest\testItWorksWithASingleId;
use Kafoso\TypeFormatter\TestResource\Unit\Kafoso\TypeFormatter\Type\Objects\DoctrineEntityFormatterTest\testItWorksWithCompositeEntityIds;
use Kafoso\TypeFormatter\TestResource\Unit\Kafoso\TypeFormatter\Type\Objects\DoctrineEntityFormatterTest\testItWorksWithCompositeIds;
use Kafoso\TypeFormatter\Type\DefaultObjectFormatter;
use Kafoso\TypeFormatter\Type\Objects\DoctrineEntityFormatter;
use Kafoso\TypeFormatter\TypeFormatter;
use PHPUnit\Framework\TestCase;

class DoctrineEntityFormatterTest extends TestCase
{
    public function testItIgnoresNonEntities()
    {
        $entityManager = $this->mockEntityManager();
        $entityManager->getMetadataFactory()
            ->expects($this->any())
            ->method('isTransient')
            ->will($this->returnValue(true));
        $doctrineEntityFormatter = new DoctrineEntityFormatter($entityManager);
        $typeFormatter = TypeFormatter::create();
        $typeFormatter = $typeFormatter->withCustomObjectFormatterCollection(new ObjectFormatterCollection([
            $doctrineEntityFormatter,
        ]));
        $this->assertNull($doctrineEntityFormatter->format(new \stdClass));
    }

    public function testItWorksWithASingleId()
    {
        $entityManager = $this->mockEntityManager();
        $entityManager->getMetadataFactory()
            ->expects($this->any())
            ->method('isTransient')
            ->will($this->returnValue(false));
        $classMetadata = $this->mockClassMetadata();
        $classMetadata
            ->expects($this->any())
            ->method('getIdentifierFieldNames')
            ->will($this->returnValue(["id"]));
        $entityManager
            ->expects($this->any())
            ->method('getClassMetadata')
            ->will($this->returnValue($classMetadata));
        $doctrineEntityFormatter = new DoctrineEntityFormatter($entityManager);
        $typeFormatter = TypeFormatter::create();
        $typeFormatter = $typeFormatter->withCustomObjectFormatterCollection(new ObjectFormatterCollection([
            $doctrineEntityFormatter,
        ]));

        $expected = sprintf(
            '\%s {$id = null}',
            testItWorksWithASingleId\EntityA::class
        );
        $entityA = new testItWorksWithASingleId\EntityA;
        $this->assertSame($expected, $doctrineEntityFormatter->format($entityA));

        $expected = sprintf(
            '\%s {$id = 22}',
            testItWorksWithASingleId\EntityA::class
        );
        $entityAProxy = new testItWorksWithASingleId\EntityAProxy;
        $this->assertSame($expected, $doctrineEntityFormatter->format($entityAProxy));
    }

    public function testItWorksWithASingleEntityId()
    {
        $entityManager = $this->mockEntityManager();
        $entityManager->getMetadataFactory()
            ->expects($this->any())
            ->method('isTransient')
            ->will($this->returnValue(false));
        $classMetadata = $this->mockClassMetadata();
        $classMetadata
            ->expects($this->any())
            ->method('getIdentifierFieldNames')
            ->willReturnOnConsecutiveCalls(
                $this->returnValue(["entityB"]),
                $this->returnValue(["entityB"]),
                $this->returnValue(["id"])
            );
        $entityManager
            ->expects($this->any())
            ->method('getClassMetadata')
            ->will($this->returnValue($classMetadata));
        $doctrineEntityFormatter = new DoctrineEntityFormatter($entityManager);
        $typeFormatter = TypeFormatter::create();
        $typeFormatter = $typeFormatter->withCustomObjectFormatterCollection(new ObjectFormatterCollection([
            $doctrineEntityFormatter,
        ]));

        $expected = sprintf(
            '\%s {$entityB = null}',
            testItWorksWithASingleEntityId\EntityA::class
        );
        $entityA = new testItWorksWithASingleEntityId\EntityA;
        $this->assertSame($expected, $doctrineEntityFormatter->format($entityA));

        $expected = sprintf(
            '\%s {$entityB = \%s {$id = 42}}',
            testItWorksWithASingleEntityId\EntityA::class,
            testItWorksWithASingleEntityId\EntityB::class
        );
        $entityAProxy = new testItWorksWithASingleEntityId\EntityAProxy;
        $entityAProxy->__load();
        $this->assertSame($expected, $doctrineEntityFormatter->format($entityAProxy));
    }

    public function testItWorksWithCompositeIds()
    {
        $entityManager = $this->mockEntityManager();
        $entityManager->getMetadataFactory()
            ->expects($this->any())
            ->method('isTransient')
            ->will($this->returnValue(false));
        $classMetadata = $this->mockClassMetadata();
        $classMetadata
            ->expects($this->any())
            ->method('getIdentifierFieldNames')
            ->will($this->returnValue(["idA", "idB"]));
        $entityManager
            ->expects($this->any())
            ->method('getClassMetadata')
            ->will($this->returnValue($classMetadata));
        $doctrineEntityFormatter = new DoctrineEntityFormatter($entityManager);
        $typeFormatter = TypeFormatter::create();
        $typeFormatter = $typeFormatter->withCustomObjectFormatterCollection(new ObjectFormatterCollection([
            $doctrineEntityFormatter,
        ]));

        $expected = sprintf(
            '\%s {$idA = null, $idB = null}',
            testItWorksWithCompositeIds\EntityA::class
        );
        $entityA = new testItWorksWithCompositeIds\EntityA;
        $this->assertSame($expected, $doctrineEntityFormatter->format($entityA));

        $expected = sprintf(
            '\%s {$idA = 1, $idB = 2}',
            testItWorksWithCompositeIds\EntityA::class
        );
        $entityAProxy = new testItWorksWithCompositeIds\EntityAProxy;
        $entityAProxy->__load();
        $this->assertSame($expected, $doctrineEntityFormatter->format($entityAProxy));
    }

    public function testItWorksWithCompositeEntityIds()
    {
        $entityManager = $this->mockEntityManager();
        $entityManagerA = $entityManager;
        $entityManager->getMetadataFactory()
            ->expects($this->any())
            ->method('isTransient')
            ->will($this->returnValue(false));
        $classMetadataA = $this->mockClassMetadata();
        $classMetadataA
            ->expects($this->any())
            ->method('getIdentifierFieldNames')
            ->will($this->returnValue(["entityB", "entityC"]));
        $classMetadataB = $this->mockClassMetadata();
        $classMetadataB
            ->expects($this->any())
            ->method('getIdentifierFieldNames')
            ->will($this->returnValue(["id"]));
        $entityManager
            ->expects($this->any())
            ->method('getClassMetadata')
            ->withConsecutive(
                [$this->equalTo(testItWorksWithCompositeEntityIds\EntityA::class)],
                [$this->equalTo(testItWorksWithCompositeEntityIds\EntityB::class)],
                [$this->equalTo(testItWorksWithCompositeEntityIds\EntityC::class)]
            )
            ->willReturnOnConsecutiveCalls(
                $this->returnValue($classMetadataA),
                $this->returnValue($classMetadataB),
                $this->returnValue($classMetadataB)
            );
        $doctrineEntityFormatter = new DoctrineEntityFormatter($entityManager);
        $typeFormatter = TypeFormatter::create();
        $typeFormatter = $typeFormatter->withCustomObjectFormatterCollection(new ObjectFormatterCollection([
            $doctrineEntityFormatter,
        ]));

        $expected = sprintf(
            '\%s {$entityB = \%s {$id = 1}, $entityC = \%s {$id = 2}}',
            testItWorksWithCompositeEntityIds\EntityA::class,
            testItWorksWithCompositeEntityIds\EntityB::class,
            testItWorksWithCompositeEntityIds\EntityC::class
        );
        $entityAProxy = new testItWorksWithCompositeEntityIds\EntityAProxy;
        $entityAProxy->__load();
        $this->assertSame($expected, $doctrineEntityFormatter->format($entityAProxy));
    }

    public function testFailureWillBePartOfFormattedStringAndNotAThrowable()
    {
        $entityManager = $this->mockEntityManager();
        $entityManager->getMetadataFactory()
            ->expects($this->any())
            ->method('isTransient')
            ->will($this->returnValue(false));
        $classMetadata = $this->mockClassMetadata();
        $classMetadata
            ->expects($this->any())
            ->method('getIdentifierFieldNames')
            ->will($this->returnValue(["id"]));
        $previous = new \RuntimeException("bar");
        $entityManager
            ->expects($this->any())
            ->method('getClassMetadata')
            ->willThrowException(new \Exception("foo", 0, $previous));
        $doctrineEntityFormatter = new DoctrineEntityFormatter($entityManager);
        $typeFormatter = TypeFormatter::create();
        $typeFormatter = $typeFormatter->withCustomObjectFormatterCollection(new ObjectFormatterCollection([
            $doctrineEntityFormatter,
        ]));

        $expected = sprintf(
            '/^\\\\%s'
                . ' \(cannot show @ORM\\\\Id fields: Failure during formatting:'
                .  ' \\\\Exception \{\$code = 0, \$file = (.+), \$line = (\d+), \$message = "foo",'
                . ' \$previous = \\\\RuntimeException\}\)$/',
            str_replace('\\', '\\\\', testItWorksWithASingleId\EntityA::class)
        );
        $entityAProxy = new testItWorksWithASingleId\EntityAProxy;
        $this->assertRegExp($expected, $doctrineEntityFormatter->format($entityAProxy));
    }

    private function mockClassMetadata(): ClassMetadata
    {
        return $this
            ->getMockBuilder(ClassMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function mockClassMetadataFactory(): ClassMetadataFactory
    {
        return $this
            ->getMockBuilder(ClassMetadataFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function mockEntityManager(): EntityManager
    {
        $entityManager = $this
            ->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $metadataFactory = $this->mockClassMetadataFactory();
        $entityManager
            ->expects($this->any())
            ->method('getMetadataFactory')
            ->will($this->returnValue($metadataFactory));
        return $entityManager;
    }
}
