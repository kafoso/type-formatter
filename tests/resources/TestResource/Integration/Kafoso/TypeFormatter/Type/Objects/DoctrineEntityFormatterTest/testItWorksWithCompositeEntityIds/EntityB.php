<?php
declare(strict_types = 1);

namespace Kafoso\TypeFormatter\TestResource\Integration\Kafoso\TypeFormatter\Type\Objects\DoctrineEntityFormatterTest\testItWorksWithCompositeEntityIds;

use Doctrine\Common\Collections\Collection as CollectionInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="testItWorksWithCompositeEntityIds_EntityB")
 */
class EntityB
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToMany(targetEntity="EntityA", mappedBy="entityB")
     */
    private $entityACollection;

    public function getEntityACollection(): CollectionInterface
    {
        return $this->entityACollection;
    }

    public function getId(): int
    {
        return $this->id;
    }
}
