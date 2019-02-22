<?php
declare(strict_types = 1);

namespace Kafoso\TypeFormatter\TestResource\Unit\Kafoso\TypeFormatter\Type\Objects\DoctrineEntityFormatterTest\testItWorksWithCompositeIds;

use Doctrine\Common\Persistence\Proxy;
use Doctrine\ORM\Mapping as ORM;

class EntityAProxy extends EntityA implements Proxy
{
    public function __load()
    {
        $reflectionObject = new \ReflectionObject($this);

        $reflectionProperty = $reflectionObject->getParentClass()->getParentClass()->getProperty("idA");
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this, 1);

        $reflectionProperty = $reflectionObject->getParentClass()->getParentClass()->getProperty("idB");
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this, 2);
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
