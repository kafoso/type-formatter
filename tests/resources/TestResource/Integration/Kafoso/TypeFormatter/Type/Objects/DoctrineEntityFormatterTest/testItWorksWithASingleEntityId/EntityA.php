<?php
declare(strict_types = 1);

namespace Kafoso\TypeFormatter\TestResource\Integration\Kafoso\TypeFormatter\Type\Objects\DoctrineEntityFormatterTest\testItWorksWithASingleEntityId;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="testItWorksWithASingleEntityId_EntityA")
 */
class EntityA
{
    /**
     * @ORM\Id
     * @ORM\OneToOne(targetEntity="EntityB", cascade={"persist"})
     * @ORM\JoinColumn(name="id", referencedColumnName="id")
     */
    private $entityB;

    public function setEntityB(EntityB $entityB)
    {
        $this->entityB = $entityB;
    }

    public function getEntityB(): ?EntityB
    {
        return $this->entityB;
    }
}
