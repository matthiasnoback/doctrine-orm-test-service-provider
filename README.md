# Doctrine ORM service provider for unit tests

[![Build Status](https://travis-ci.org/matthiasnoback/doctrine-orm-test-service-provider.png?branch=1.0)](https://travis-ci.org/matthiasnoback/doctrine-orm-test-service-provider)

This library contains a service provider to be used with a [service container for PHPUnit
tests](https://github.com/matthiasnoback/phpunit-test-service-container).

## Usage

Use the trait `Noback\PHPUnitTestServiceContainer\PHPUnit\TestCaseWithEntityManager` in your test class. You then
need to implement the `getEntityDirectories()` which should return an array of the directories containing the entities that should be loaded.

For each test method a connection to an SQLite database will be available.
Also the schema for the given entities will be created automatically.

```php
<?php

use PHPUnit\Framework\TestCase;
use Noback\PHPUnitTestServiceContainer\PHPUnit\TestCaseWithEntityManager;

class StorageTest extends TestCase 
{
    use TestCaseWithEntityManager;
    
    protected function getEntityDirectories(): array
    {
        return array(
            __DIR__ . '/Entity'
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
    }
}
```

Of course, you would usually inject the entity manager into some object which is the subject-under-test.

To register Doctrine event listeners/subscribers, get the `EventManager` instance by calling
`$this->getEventManager()`. To get the database `Connection` object, call `$this->getConnection()`.

## Read more

- [Doctrine ORM](http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/)
- [PHPUnit test service container](https://github.com/matthiasnoback/phpunit-test-service-container)
- [Doctrine DBAL test service container](https://github.com/matthiasnoback/doctrine-dbal-test-service-provider)
