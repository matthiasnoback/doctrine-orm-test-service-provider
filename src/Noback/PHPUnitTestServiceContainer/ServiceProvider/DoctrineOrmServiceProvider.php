<?php

namespace Noback\PHPUnitTestServiceContainer\ServiceProvider;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\ORMSetup;
use Noback\PHPUnitTestServiceContainer\ServiceContainer;
use Noback\PHPUnitTestServiceContainer\ServiceProvider;
use Noback\PHPUnitTestServiceContainer\Cache\ArrayCachePool;
use Pimple\Container;

class DoctrineOrmServiceProvider implements ServiceProvider
{
    private $entityDirectories;

    public function __construct(array $entityDirectories = [])
    {
        $this->entityDirectories = $entityDirectories;
    }

    public function register(Container $serviceContainer)
    {
        $serviceContainer['doctrine_orm.entity_directories'] = $this->entityDirectories;
        $serviceContainer['doctrine_orm.development_mode'] = true;
        $serviceContainer['doctrine_orm.proxy_dir'] = sys_get_temp_dir();

        $serviceContainer['doctrine_orm.entity_manager'] = function (ServiceContainer $serviceContainer) {
            return new EntityManager(
                $serviceContainer['doctrine_dbal.connection'],
                $serviceContainer['doctrine_orm.configuration']
            );
        };

        $serviceContainer['doctrine_orm.configuration'] = function (ServiceContainer $serviceContainer) {
            $config = ORMSetup::createAttributeMetadataConfiguration(
                $serviceContainer['doctrine_orm.entity_directories'],
                $serviceContainer['doctrine_orm.development_mode'],
                $serviceContainer['doctrine_orm.proxy_dir'],
                new ArrayCachePool()
            );

            if (method_exists(ProxyHelper::class, 'generateLazyGhost') === false) {
                $config->enableNativeLazyObjects(true);
            }

            return $config;
        };

        $serviceContainer['doctrine_orm.schema_tool'] = function (ServiceContainer $serviceContainer) {
            return new SchemaTool($serviceContainer['doctrine_orm.entity_manager']);
        };
    }

    public function setUp(ServiceContainer $serviceContainer)
    {
        $this->createSchema(
            $serviceContainer['doctrine_orm.schema_tool'],
            $serviceContainer['doctrine_orm.entity_manager']
        );
    }

    public function tearDown(ServiceContainer $serviceContainer)
    {
        $this->dropSchema(
            $serviceContainer['doctrine_orm.schema_tool'],
            $serviceContainer['doctrine_orm.entity_manager']
        );
    }

    private function createSchema(SchemaTool $schemaTool, EntityManager $entityManager)
    {
        $schemaTool->createSchema($this->getClassMetadatas($entityManager));
    }

    private function dropSchema(SchemaTool $schemaTool, EntityManager $entityManager)
    {
        $schemaTool->dropSchema($this->getClassMetadatas($entityManager));
    }

    private function getClassMetadatas(EntityManager $entityManager)
    {
        return $entityManager->getMetadataFactory()->getAllMetadata();
    }
}
