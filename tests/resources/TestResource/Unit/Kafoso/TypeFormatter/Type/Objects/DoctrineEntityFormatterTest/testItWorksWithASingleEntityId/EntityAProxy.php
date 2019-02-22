<?php
declare(strict_types = 1);

namespace Kafoso\TypeFormatter\TestResource\Unit\Kafoso\TypeFormatter\Type\Objects\DoctrineEntityFormatterTest\testItWorksWithASingleEntityId;

use Doctrine\Common\Persistence\Proxy;
use Doctrine\ORM\Mapping as ORM;

class EntityAProxy extends EntityA implements Proxy
{
    public function __load()
    {
        $reflectionObject = new \ReflectionObject($this);
        $reflectionProperty = $reflectionObject->getParentClass()->getParentClass()->getProperty("entityB");
        $reflectionProperty->setAccessible(true);
        $entityB = new EntityB($this);
        $reflectionProperty->setValue($this, $entityB);

        $reflectionObject = new \ReflectionObject($entityB);
        $reflectionProperty = $reflectionObject->getParentClass()->getProperty("id");
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($entityB, 42);
    }

    public function __isInitialized()
    {
        return false;
    }

    public function __setInitialized($initialized)
    {

    }

    public function __setInitializer(Closure $initializer = null)
    {

    }

    public function __getInitializer()
    {

    }

    public function __setCloner(\Closure $cloner = null)
    {

    }

    public function __getLazyProperties()
    {

    }
}
