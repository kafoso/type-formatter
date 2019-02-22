<?php
declare(strict_types = 1);

namespace Kafoso\TypeFormatter\TestResource\Integration\Kafoso\TypeFormatter\Abstraction;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;

class AbstractDoctrineTestCase extends TestCase
{
    /**
     * @var null|EntityManager
     */
    private $entityManager = null;

    public function setUp()
    {
        $configuration = static::getSetUpDoctrineConfiguration();
        $configurationArray = static::getSetUpDoctrineConfigurationArray();
        $connectionWithoutDatabase = static::createConnection(
            array_diff_key($configurationArray, ["dbname" => null]),
            $configuration
        );
        static::reinstallFirebirdDatabase($connectionWithoutDatabase, $configurationArray);
        $connection = $connectionWithoutDatabase = static::createConnection($configurationArray, $configuration);
        $this->entityManager = EntityManager::create($connection, $configuration);
    }

    public function tearDown()
    {
        if ($this->entityManager) {
            $this->entityManager->getConnection()->close();
        }
    }

    protected function getEntityManager(): EntityManager
    {
        if (!$this->entityManager) {
            throw new \RuntimeException(sprintf(
                "A entity manager (\\%s) has not been set",
                EntityManager::class
            ));
        }
        return $this->entityManager;
    }

    protected static function createConnection(array $configurationArray, Configuration $configuration)
    {
        return DriverManager::getConnection($configurationArray, $configuration, new EventManager());
    }

    protected static function reinstallFirebirdDatabase(Connection $connection, array $configurationArray)
    {
        $stmt = $connection->prepare(sprintf(
            "DROP DATABASE IF EXISTS %s",
            $configurationArray['dbname']
        ));
        $stmt->execute();
        $stmt = $connection->prepare(sprintf(
            "CREATE DATABASE %s CHARACTER SET ?",
            $configurationArray['dbname']
        ));
        $stmt->execute([$configurationArray['charset']]);
    }

    protected static function getSetUpDoctrineConfiguration(): Configuration
    {
        $cache = new \Doctrine\Common\Cache\ArrayCache;
        $configuration = new Configuration;
        $driverImpl = $configuration->newDefaultAnnotationDriver(
            [
                ROOT_PATH . '/tests/resources/Test/Entity'
            ],
            false
        );
        $configuration->setMetadataDriverImpl($driverImpl);
        $configuration->setAutoGenerateProxyClasses(true);
        $configuration->setProxyDir(ROOT_PATH . '/var/doctrine-proxies');
        $configuration->setProxyNamespace('Doctrine\Proxies');
        $configuration->setMetadataCacheImpl($cache);
        $configuration->setResultCacheImpl($cache);
        $configuration->setQueryCacheImpl($cache);
        return $configuration;
    }

    protected static function getSetUpDoctrineConfigurationArray(): array
    {
        return [
            'driver' => 'pdo_mysql',
            'host' => 'localhost',
            'dbname' => 'TypeFormatterDatabase',
            'user' => 'root',
            'password' => '8364f9f87133242a9bd8d230da24379d',
            'charset' => 'utf8',
        ];
    }
}
