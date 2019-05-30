<?php

declare(strict_types=1);

namespace Bolt\Tests;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Input\StringInput;

class DbAwareTestCase extends WebTestCase
{
    /** @var Application */
    private static $application;

    /** @var EntityManager */
    private $entityManager;

    protected function getEm(): EntityManager
    {
        return $this->entityManager;
    }

    protected function setUp(): void
    {
        self::runCommand('doctrine:database:drop --force');
        self::runCommand('doctrine:database:create');
        self::runCommand('doctrine:schema:create');

        $this->entityManager = static::createClient()->getContainer()
            ->get('doctrine')
            ->getManager();

        parent::setUp();
    }

    protected static function runCommand($command)
    {
        $command = sprintf('%s --quiet', $command);

        return self::getApplication()->run(new StringInput($command));
    }

    private static function getApplication()
    {
        if (self::$application === null) {
            $client = static::createClient();

            // Since Symfony 4.3.0, the `doRun` method no longer triggers `->boot()`, so we do it ourselves.
            // @see: https://github.com/symfony/framework-bundle/commit/2c0499210e365bdfe81ae2c56a5a81c5ec687532
            $client->getKernel()->boot();

            self::$application = new Application($client->getKernel());
            self::$application->setAutoExit(false);
        }

        return self::$application;
    }

    protected function tearDown(): void
    {
        self::runCommand('doctrine:database:drop --force');

        parent::tearDown();

        $this->entityManager->close();
        $this->entityManager = null; // avoid memory leaks
    }
}
