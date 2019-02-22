<?php
declare(strict_types = 1);

namespace Kafoso\TypeFormatter\TestResource\Integration\Kafoso\TypeFormatter\Type\Objects\DoctrineEntityFormatterTest\testItWorksWithASingleId;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="testItWorksWithASingleId_EntityA")
 */
class EntityA
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    private $id;

    public function getId(): ?int
    {
        return $this->id;
    }
}
