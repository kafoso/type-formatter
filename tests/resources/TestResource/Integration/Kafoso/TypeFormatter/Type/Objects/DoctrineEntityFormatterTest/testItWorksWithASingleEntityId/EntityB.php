<?php
declare(strict_types = 1);

namespace Kafoso\TypeFormatter\TestResource\Integration\Kafoso\TypeFormatter\Type\Objects\DoctrineEntityFormatterTest\testItWorksWithASingleEntityId;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="testItWorksWithASingleEntityId_EntityB")
 */
class EntityB
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    private $id;

    private $entityA;

    public function __construct(EntityA $entityA)
    {
        $this->entityA = $entityA;
        $this->entityA->setEntityB($this);
    }

    public function getEntityA(): ?EntityA
    {
        return $this->entityA;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
}
