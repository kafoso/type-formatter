<?php
declare(strict_types = 1);

namespace Kafoso\TypeFormatter\TestResource\Unit\Kafoso\TypeFormatter\Type\Objects\DoctrineEntityFormatterTest\testItWorksWithCompositeEntityIds;

use Doctrine\Common\Persistence\Proxy;
use Doctrine\ORM\Mapping as ORM;

class EntityAProxy extends EntityA implements Proxy
{
    public function __load()
    {
        $reflectionObject = new \ReflectionObject($this);

        $entityB = new EntityB;
        $reflectionProperty = $reflectionObject->getParentClass()->getParentClass()->getProperty("entityB");
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this, $entityB);

        $entityC = new EntityC;
        $reflectionProperty = $reflectionObject->getParentClass()->getParentClass()->getProperty("entityC");
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this, $entityC);

        $reflectionObject = new \ReflectionObject($entityB);
        $reflectionProperty = $reflectionObject->getParentClass()->getProperty("id");
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($entityB, 1);

        $reflectionObject = new \ReflectionObject($entityC);
        $reflectionProperty = $reflectionObject->getParentClass()->getProperty("id");
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($entityC, 2);
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
