<?php

namespace Noback\PHPUnitTestServiceContainer\PHPUnit;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Noback\PHPUnitTestServiceContainer\ServiceProvider\DoctrineDbalServiceProvider;
use Noback\PHPUnitTestServiceContainer\ServiceProvider\DoctrineOrmServiceProvider;
use Noback\PHPUnitTestServiceContainer\ServiceProviderInterface;

abstract class AbstractTestCaseWithEntityManager extends AbstractTestCaseWithServiceContainer
{
    /**
     * Return the names of the directories containing the models for this test
     *
     * @return string
     */
    abstract protected function getModelDirectories();

    /**
     * Return the model class names that should be loaded
     *
     * @return array
     */
    abstract protected function getModelClasses();

    /**
     * Return an array of ServiceProviderInterface instances you want to use in this test case
     *
     * @return ServiceProviderInterface[]
     */
    protected function getServiceProviders()
    {
        return array(
            new DoctrineDbalServiceProvider(),
            new DoctrineOrmServiceProvider($this->getModelDirectories(), $this->getModelClasses())
        );
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        return $this->container['doctrine_orm.entity_manager'];
    }

    /**
     * @return EventManager
     */
    protected function getEventManager()
    {
        return $this->container['doctrine_dbal.event_manager'];
    }

    /**
     * @return Connection
     */
    protected function getConnection()
    {
        return $this->container['doctrine_dbal.connection'];
    }
}
