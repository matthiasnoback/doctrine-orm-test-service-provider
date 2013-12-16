<?php

namespace Noback\PHPUnitTestServiceContainer\Tests\PHPUnit;

use Noback\PHPUnitTestServiceContainer\PHPUnit\AbstractTestCaseWithEntityManager;
use Noback\PHPUnitTestServiceContainer\Tests\PHPUnit\Entity\User;

class AbstractTestCaseWithEntityManagerTest extends AbstractTestCaseWithEntityManager
{
    protected function getEntityDirectories()
    {
        return array(__DIR__.'/Entity');
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

    /**
     * @test
     */
    public function a_new_test_has_a_fresh_database()
    {
        $count = $this
            ->getEntityManager()
            ->getRepository('Noback\PHPUnitTestServiceContainer\Tests\PHPUnit\Entity\User')
            ->createQueryBuilder('u')
            ->select('COUNT(u.id) as number_of_users')
            ->getQuery()
            ->getSingleScalarResult();

        $this->assertSame(0, (integer) $count);
    }
}
