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
    private $entityClasses;

    public function __construct(array $entityClasses = array())
    {
        $this->entityClasses = $entityClasses;
    }

    public function register(ServiceContainerInterface $serviceContainer)
    {
        $serviceContainer['doctrine_orm.entity_classes'] = $this->entityClasses;
        $serviceContainer['doctrine_orm.entity_directories'] = array();
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
            function ($serviceContainer) {
                return Setup::createAnnotationMetadataConfiguration(
                    $serviceContainer['doctrine_orm.entity_directories'],
                    $serviceContainer['doctrine_orm.development_mode'],
                    $serviceContainer['doctrine_orm.proxy_dir'],
                    $serviceContainer['doctrine_orm.driver_cache']
                );
            }
        );
    }

    public function setUp(ServiceContainerInterface $serviceContainer)
    {
        $this->createSchema(
            $serviceContainer['doctrine_orm.entity_manager'],
            $serviceContainer['doctrine_orm.entity_classes']
        );
    }

    public function tearDown(ServiceContainerInterface $serviceContainer)
    {
        $this->dropSchema(
            $serviceContainer['doctrine_orm.entity_manager'],
            $serviceContainer['doctrine_orm.entity_classes']
        );
    }

    private function createSchema(EntityManager $entityManager, array $classes)
    {
        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->createSchema($this->getClassMetadatas($entityManager, $classes));
    }

    private function dropSchema(EntityManager $entityManager, array $classes)
    {
        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->dropSchema($this->getClassMetadatas($entityManager, $classes));
    }

    private function getClassMetadatas(EntityManager $entityManager, array $classes)
    {
        $classMetadatas = array_map(
            function ($class) use ($entityManager) {
                return $entityManager->getClassMetadata($class);
            },
            $classes
        );

        return $classMetadatas;
    }
}
