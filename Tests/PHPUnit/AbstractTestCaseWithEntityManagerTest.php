<?php

namespace Noback\PHPUnitTestServiceContainer\Tests\PHPUnit;

use Noback\PHPUnitTestServiceContainer\PHPUnit\AbstractTestCaseWithEntityManager;
use Noback\PHPUnitTestServiceContainer\Tests\PHPUnit\Entity\User;

class AbstractTestCaseWithEntityManagerTest extends AbstractTestCaseWithEntityManager
{
    protected function getModelDirectories()
    {
        return array(
            __DIR__.'/Entity'
        );
    }

    protected function getModelClasses()
    {
        return array(
            'Noback\PHPUnitTestServiceContainer\Tests\PHPUnit\Entity\User'
        );
    }

    /**
     * @test
     */
    public function it_persists_an_entity()
    {
        $user = new User();
        $user->setName('Matthias');

        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();

        $this->getEntityManager()->clear();

        $retrievedUser = $this->getEntityManager()->find(get_class($user), $user->getId());
        $this->assertSame($retrievedUser->getName(), $user->getName());
    }
}
