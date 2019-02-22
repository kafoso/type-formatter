<?php
declare(strict_types = 1);

namespace Kafoso\TypeFormatter\TestResource\Integration\Kafoso\TypeFormatter\Type\Objects\DoctrineEntityFormatterTest\testItWorksWithCompositeEntityIds;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="testItWorksWithCompositeEntityIds_EntityA")
 */
class EntityA
{
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="EntityB", inversedBy="entityA")
     * @ORM\JoinColumn(name="idB", referencedColumnName="id")
     */
    private $entityB;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="EntityC", inversedBy="entityA")
     * @ORM\JoinColumn(name="idC", referencedColumnName="id")
     */
    private $entityC;

    public function getEntityB(): EntityB
    {
        return $this->entityB;
    }

    public function getEntityC(): EntityC
    {
        return $this->entityC;
    }
}
