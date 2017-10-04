<?php

namespace Noback\PHPUnitTestServiceContainer\PHPUnit;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManager;
use Noback\PHPUnitTestServiceContainer\ServiceProvider\DoctrineDbalServiceProvider;
use Noback\PHPUnitTestServiceContainer\ServiceProvider\DoctrineOrmServiceProvider;

trait TestCaseWithEntityManager
{
    use TestCaseWithServiceContainer;

    /**
     * Return the directories containing the entity classes that should be loaded.
     *
     * @return string[]
     */
    abstract protected function getEntityDirectories(): array;

    protected function getServiceProviders(): array
    {
        return [
            new DoctrineDbalServiceProvider(new Schema()),
            new DoctrineOrmServiceProvider($this->getEntityDirectories())
        ];
    }

    protected function getEntityManager(): EntityManager
    {
        return $this->container['doctrine_orm.entity_manager'];
    }

    protected function getEventManager(): EventManager
    {
        return $this->container['doctrine_dbal.event_manager'];
    }

    protected function getConnection(): Connection
    {
        return $this->container['doctrine_dbal.connection'];
    }
}
