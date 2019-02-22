<?php
declare(strict_types = 1);

namespace Kafoso\TypeFormatter\TestResource\Integration\Kafoso\TypeFormatter\Type\Objects\DoctrineEntityFormatterTest\testItWorksWithCompositeIds;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="testItWorksWithCompositeIds_EntityA")
 * @ORM\HasLifecycleCallbacks
 */
class EntityA
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private $idA;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private $idB;

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->idA = 1;
        $this->idB = 2;
    }

    public function getIdA(): ?int
    {
        return $this->idA;
    }

    public function getIdB(): ?int
    {
        return $this->idB;
    }
}
