<?php

namespace Noback\PHPUnitTestServiceContainer\ServiceProvider;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\Setup;
use Noback\PHPUnitTestServiceContainer\ServiceContainerInterface;
use Noback\PHPUnitTestServiceContainer\ServiceProviderInterface;

class DoctrineOrmServiceProvider implements ServiceProviderInterface
{
    private $modelDirectories;
    private $modelClasses;

    public function __construct(array $modelDirectories, array $modelClasses)
    {
        $this->modelDirectories = $modelDirectories;
        $this->modelClasses = $modelClasses;
    }

    public function register(ServiceContainerInterface $serviceContainer)
    {
        $serviceContainer['doctrine_orm.model_classes'] = $this->modelClasses;
        $serviceContainer['doctrine_orm.model_directories'] = $this->modelDirectories;
        $serviceContainer['doctrine_orm.dev_mode'] = true;

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
                    $serviceContainer['doctrine_orm.model_directories'],
                    $serviceContainer['doctrine_orm.dev_mode']
                );
            }
        );
    }

    public function setUp(ServiceContainerInterface $serviceContainer)
    {
        $entityManager = $serviceContainer['doctrine_orm.entity_manager'];
        /* @var $entityManager \Doctrine\ORM\EntityManager */

        $schema = array_map(
            function ($class) use ($entityManager) {
                return $entityManager->getClassMetadata($class);
            },
            $serviceContainer['doctrine_orm.model_classes']
        );

        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->dropSchema(array());
        $schemaTool->createSchema($schema);
    }

    public function tearDown(ServiceContainerInterface $serviceContainer)
    {
    }
}
