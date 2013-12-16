<?php

namespace Noback\PHPUnitTestServiceContainer\PHPUnit;

use Noback\PHPUnitTestServiceContainer\ServiceProvider\DoctrineDbalServiceProvider;
use Noback\PHPUnitTestServiceContainer\ServiceProvider\DoctrineOrmServiceProvider;

abstract class AbstractTestCaseWithEntityManager extends AbstractTestCaseWithServiceContainer
{
    /**
     * Return the entity class names that should be loaded
     *
     * @return array
     */
    abstract protected function getEntityClasses();

    protected function getServiceProviders()
    {
        return array(
            new DoctrineDbalServiceProvider(),
            new DoctrineOrmServiceProvider($this->getEntityClasses())
        );
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getEntityManager()
    {
        return $this->container['doctrine_orm.entity_manager'];
    }

    /**
     * @return \Doctrine\Common\EventManager
     */
    protected function getEventManager()
    {
        return $this->container['doctrine_dbal.event_manager'];
    }

    /**
     * @return \Doctrine\DBAL\Connection
     */
    protected function getConnection()
    {
        return $this->container['doctrine_dbal.connection'];
    }
}
