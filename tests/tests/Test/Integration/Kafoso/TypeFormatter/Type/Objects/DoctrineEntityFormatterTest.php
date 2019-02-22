<?php
declare(strict_types = 1);

namespace Kafoso\TypeFormatter\Test\Integration\Kafoso\TypeFormatter\Type\Objects;

use Kafoso\TypeFormatter\Collection\Type\ObjectFormatterCollection;
use Kafoso\TypeFormatter\TestResource\Integration\Kafoso\TypeFormatter\Abstraction\AbstractDoctrineTestCase;
use Kafoso\TypeFormatter\TestResource\Integration\Kafoso\TypeFormatter\Type\Objects\DoctrineEntityFormatterTest\testItWorksWithASingleEntityId;
use Kafoso\TypeFormatter\TestResource\Integration\Kafoso\TypeFormatter\Type\Objects\DoctrineEntityFormatterTest\testItWorksWithASingleId;
use Kafoso\TypeFormatter\TestResource\Integration\Kafoso\TypeFormatter\Type\Objects\DoctrineEntityFormatterTest\testItWorksWithCompositeEntityIds;
use Kafoso\TypeFormatter\TestResource\Integration\Kafoso\TypeFormatter\Type\Objects\DoctrineEntityFormatterTest\testItWorksWithCompositeIds;
use Kafoso\TypeFormatter\Type\DefaultObjectFormatter;
use Kafoso\TypeFormatter\Type\Objects\DoctrineEntityFormatter;
use Kafoso\TypeFormatter\TypeFormatter;

/**
 * @runTestsInSeparateProcesses
 */
class DoctrineEntityFormatterTest extends AbstractDoctrineTestCase
{
    public function testItWorksWithASingleId()
    {
        $this->getEntityManager()->getConnection()->exec(
            "CREATE TABLE testItWorksWithASingleId_EntityA (
                id INT(11) unsigned NOT NULL AUTO_INCREMENT,
                PRIMARY KEY (id)
            )"
        );
        $entity = new testItWorksWithASingleId\EntityA;
        $this->assertNull($entity->getId());

        $typeFormatter = TypeFormatter::create();
        $doctrineEntityFormatter = new DoctrineEntityFormatter($this->getEntityManager());
        $typeFormatter = $typeFormatter->withCustomObjectFormatterCollection(new ObjectFormatterCollection([
            $doctrineEntityFormatter,
        ]));
        $expected = sprintf(
            '\\%s {$id = null}',
            testItWorksWithASingleId\EntityA::class
        );
        $this->assertSame($expected, $doctrineEntityFormatter->format($entity));
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush($entity);

        $this->assertSame(1, $entity->getId());

        $expected = sprintf(
            '\\%s {$id = 1}',
            testItWorksWithASingleId\EntityA::class
        );
        $this->assertSame($expected, $doctrineEntityFormatter->format($entity));
    }

    public function testItWorksWithASingleEntityId()
    {
        $this->getEntityManager()->getConnection()->exec(
            "CREATE TABLE testItWorksWithASingleEntityId_EntityA (
                id INT(11) unsigned NOT NULL,
                PRIMARY KEY (id)
            )"
        );
        $this->getEntityManager()->getConnection()->exec(
            "CREATE TABLE testItWorksWithASingleEntityId_EntityB (
                id INT(11) unsigned NOT NULL AUTO_INCREMENT,
                PRIMARY KEY (id)
            )"
        );
        $entityA = new testItWorksWithASingleEntityId\EntityA;
        $entityB = new testItWorksWithASingleEntityId\EntityB($entityA);
        $this->assertNull($entityB->getId());

        $typeFormatter = TypeFormatter::create();
        $doctrineEntityFormatter = new DoctrineEntityFormatter($this->getEntityManager());
        $typeFormatter = $typeFormatter->withCustomObjectFormatterCollection(new ObjectFormatterCollection([
            $doctrineEntityFormatter,
        ]));
        $expected = sprintf(
            '\\%s {$id = null}',
            testItWorksWithASingleEntityId\EntityB::class
        );
        $this->assertSame($expected, $doctrineEntityFormatter->format($entityB));

        $expected = sprintf(
            '\\%s {$entityB = \\%s {$id = null}}',
            testItWorksWithASingleEntityId\EntityA::class,
            testItWorksWithASingleEntityId\EntityB::class
        );
        $this->assertSame($expected, $doctrineEntityFormatter->format($entityB->getEntityA()));

        $this->getEntityManager()->persist($entityB);
        $this->getEntityManager()->flush($entityB);

        $this->assertSame(1, $entityB->getId());

        $expected = sprintf(
            '\\%s {$id = 1}',
            testItWorksWithASingleEntityId\EntityB::class
        );
        $this->assertSame($expected, $doctrineEntityFormatter->format($entityB));
        $expected = sprintf(
            '\\%s {$entityB = \\%s {$id = 1}}',
            testItWorksWithASingleEntityId\EntityA::class,
            testItWorksWithASingleEntityId\EntityB::class
        );
        $this->assertSame($expected, $doctrineEntityFormatter->format($entityB->getEntityA()));

        $this->getEntityManager()->clear();

        $entityB = $this->getEntityManager()->getRepository(testItWorksWithASingleEntityId\EntityB::class)->find(1);
        $this->assertTrue(is_object($entityB));
    }

    public function testItWorksWithCompositeIds()
    {
        $this->getEntityManager()->getConnection()->exec(
            "CREATE TABLE testItWorksWithCompositeIds_EntityA (
                idA INT(11) unsigned NOT NULL,
                idB INT(11) unsigned NOT NULL,
                PRIMARY KEY (idA, idB)
            )"
        );
        $entity = new testItWorksWithCompositeIds\EntityA;
        $this->assertNull($entity->getIdA());
        $this->assertNull($entity->getIdB());

        $typeFormatter = TypeFormatter::create();
        $doctrineEntityFormatter = new DoctrineEntityFormatter($this->getEntityManager());
        $typeFormatter = $typeFormatter->withCustomObjectFormatterCollection(new ObjectFormatterCollection([
            $doctrineEntityFormatter,
        ]));
        $expected = sprintf(
            '\\%s {$idA = null, $idB = null}',
            testItWorksWithCompositeIds\EntityA::class
        );
        $this->assertSame($expected, $doctrineEntityFormatter->format($entity));
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush($entity);

        $this->assertSame(1, $entity->getIdA());
        $this->assertSame(2, $entity->getIdB());

        $expected = sprintf(
            '\\%s {$idA = 1, $idB = 2}',
            testItWorksWithCompositeIds\EntityA::class
        );
        $this->assertSame($expected, $doctrineEntityFormatter->format($entity));
    }

    public function testItWorksWithCompositeEntityIds()
    {
        $this->getEntityManager()->getConnection()->exec(
            "CREATE TABLE testItWorksWithCompositeEntityIds_EntityA (
                idB INT(11) unsigned NOT NULL,
                idC INT(11) unsigned NOT NULL,
                PRIMARY KEY (idB, idC)
            )"
        );
        $this->getEntityManager()->getConnection()->exec(
            "INSERT INTO testItWorksWithCompositeEntityIds_EntityA (idB, idC) VALUES (1, 2)"
        );
        $this->getEntityManager()->getConnection()->exec(
            "CREATE TABLE testItWorksWithCompositeEntityIds_EntityB (
                id INT(11) unsigned NOT NULL,
                PRIMARY KEY (id)
            )"
        );
        $this->getEntityManager()->getConnection()->exec(
            "INSERT INTO testItWorksWithCompositeEntityIds_EntityB (id) VALUES (1)"
        );
        $this->getEntityManager()->getConnection()->exec(
            "CREATE TABLE testItWorksWithCompositeEntityIds_EntityC (
                id INT(11) unsigned NOT NULL,
                PRIMARY KEY (id)
            )"
        );
        $this->getEntityManager()->getConnection()->exec(
            "INSERT INTO testItWorksWithCompositeEntityIds_EntityC (id) VALUES (2)"
        );

        $entityB = $this->getEntityManager()->getRepository(testItWorksWithCompositeEntityIds\EntityB::class)->find(1);
        $this->assertTrue(is_object($entityB));
        $this->assertInstanceOf(
            testItWorksWithCompositeEntityIds\EntityB::class,
            $entityB
        );
        $this->assertSame(1, $entityB->getId());
        $this->assertTrue(is_object($entityB->getEntityACollection()));
        $this->assertTrue(is_object($entityB->getEntityACollection()->first()));
        $this->assertInstanceOf(
            testItWorksWithCompositeEntityIds\EntityA::class,
            $entityB->getEntityACollection()->first()
        );
        $entityA = $entityB->getEntityACollection()->first();
        $this->assertTrue(is_object($entityA->getEntityC()));
        $this->assertInstanceOf(
            testItWorksWithCompositeEntityIds\EntityC::class,
            $entityA->getEntityC()
        );
        $entityC = $entityA->getEntityC();
        $this->assertSame(2, $entityC->getId());

        $typeFormatter = TypeFormatter::create();
        $doctrineEntityFormatter = new DoctrineEntityFormatter($this->getEntityManager());
        $typeFormatter = $typeFormatter->withCustomObjectFormatterCollection(new ObjectFormatterCollection([
            $doctrineEntityFormatter,
        ]));

        $expected = sprintf(
            '\\%s {$id = 1}',
            testItWorksWithCompositeEntityIds\EntityB::class
        );
        $this->assertSame($expected, $doctrineEntityFormatter->format($entityB));

        $expected = sprintf(
            '\\%s {$id = 2}',
            testItWorksWithCompositeEntityIds\EntityC::class
        );
        $this->assertSame($expected, $doctrineEntityFormatter->format($entityC));

        $expected = sprintf(
            '\\%s {$entityB = \\%s {$id = 1}, $entityC = \\%s {$id = 2}}',
            testItWorksWithCompositeEntityIds\EntityA::class,
            testItWorksWithCompositeEntityIds\EntityB::class,
            testItWorksWithCompositeEntityIds\EntityC::class
        );
        $this->assertSame($expected, $doctrineEntityFormatter->format($entityA));
    }
}
