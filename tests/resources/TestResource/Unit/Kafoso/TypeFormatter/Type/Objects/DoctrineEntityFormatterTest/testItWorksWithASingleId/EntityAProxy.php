<?php
declare(strict_types = 1);

namespace Kafoso\TypeFormatter\TestResource\Unit\Kafoso\TypeFormatter\Type\Objects\DoctrineEntityFormatterTest\testItWorksWithASingleId;

use Doctrine\Common\Persistence\Proxy;
use Doctrine\ORM\Mapping as ORM;

class EntityAProxy extends EntityA implements Proxy
{
    public function __load()
    {
        $reflectionObject = new \ReflectionObject($this);
        $reflectionProperty = $reflectionObject->getParentClass()->getParentClass()->getProperty("id");
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this, 22);
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
