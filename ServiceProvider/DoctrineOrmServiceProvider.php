<?php

namespace Noback\PHPUnitTestServiceContainer\ServiceProvider;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\Setup;
use Noback\PHPUnitTestServiceContainer\ServiceContainerInterface;
use Noback\PHPUnitTestServiceContainer\ServiceProviderInterface;

class DoctrineOrmServiceProvider implements ServiceProviderInterface
{
    private $entityDirectories;

    public function __construct(array $entityDirectories = array())
    {
        $this->entityDirectories = $entityDirectories;
    }

    public function register(ServiceContainerInterface $serviceContainer)
    {
        $serviceContainer['doctrine_orm.entity_directories'] = $this->entityDirectories;
        $serviceContainer['doctrine_orm.development_mode'] = true;
        $serviceContainer['doctrine_orm.proxy_dir'] = sys_get_temp_dir();

        $serviceContainer['doctrine_orm.driver_cache'] = $serviceContainer->share(
            function () {
                return new ArrayCache();
            }
        );

        $serviceContainer['doctrine_orm.entity_manager'] = $serviceContainer->share(
            function (ServiceContainerInterface $serviceContainer) {
                return EntityManager::create(
                    $serviceContainer['doctrine_dbal.connection'],
                    $serviceContainer['doctrine_orm.configuration'],
                    $serviceContainer['doctrine_dbal.event_manager']
                );
            }
        );

        $serviceContainer['doctrine_orm.configuration'] = $serviceContainer->share(
            function (ServiceContainerInterface $serviceContainer) {
                return Setup::createAnnotationMetadataConfiguration(
                    $serviceContainer['doctrine_orm.entity_directories'],
                    $serviceContainer['doctrine_orm.development_mode'],
                    $serviceContainer['doctrine_orm.proxy_dir'],
                    $serviceContainer['doctrine_orm.driver_cache']
                );
            }
        );

        $serviceContainer['doctrine_orm.schema_tool'] = $serviceContainer->share(
            function (ServiceContainerInterface $serviceContainer) {
                return new SchemaTool($serviceContainer['doctrine_orm.entity_manager']);
            }
        );
    }

    public function setUp(ServiceContainerInterface $serviceContainer)
    {
        $this->createSchema(
            $serviceContainer['doctrine_orm.schema_tool'],
            $serviceContainer['doctrine_orm.entity_manager']
        );
    }

    public function tearDown(ServiceContainerInterface $serviceContainer)
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
