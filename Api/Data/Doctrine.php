<?php

declare(strict_types=1);

namespace JohnRogar\MageDoctrine\Api\Data;

use JohnRogar\MageDoctrine\Api\ManagerInterface;
use JohnRogar\MageDoctrine\Api\EventManagerInterface as DoctrineEventManagerInterface;
use JohnRogar\MageDoctrine\Model\Repository\Factory\DoctrineFactory;
use Magento\Framework\App\State;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\App\ResourceConnection;
use Doctrine\Common\EventManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Configuration;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Types\Type;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Doctrine
 * @package JohnRogar\MageDoctrine\Api\Data
 * @SuppressWarnings(StaticAccess)
 * @SuppressWarnings(LongVariable)
 * @SuppressWarnings(CouplingBetweenObjects)
 */
class Doctrine implements ManagerInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    private $eventManager;

    private $state;

    private $repositoryFactory;

    private $connectionConfiguration;

    private $modules;

    /**
     * @var null|Configuration
     */
    private $ormConfiguration;

    /**
     * @var null|EventManager
     */
    private $doctrineEventManager;

    private $resourceConnection;

    private $registrar;

    /**
     * Doctrine constructor.
     * @param EventManagerInterface $eventManager
     * @param State $state
     * @param $repositoryFactory
     * @param DoctrineEventManagerInterface $doctrineEventManager
     * @param ResourceConnection $resourceConnection
     * @param ComponentRegistrarInterface $registrar
     * @param array $connectionConfiguration
     * @param array $modules
     * @param Configuration|null $ormConfiguration
     */
    public function __construct(
        EventManagerInterface $eventManager,
        State $state,
        DoctrineFactory $repositoryFactory,
        DoctrineEventManagerInterface $doctrineEventManager,
        ResourceConnection $resourceConnection,
        ComponentRegistrarInterface $registrar,
        array $connectionConfiguration = [],
        array $modules = [],
        ?Configuration $ormConfiguration = null
    ) {
        $this->eventManager = $eventManager;
        $this->state = $state;
        $this->repositoryFactory = $repositoryFactory;
        $this->doctrineEventManager = $doctrineEventManager;
        $this->resourceConnection = $resourceConnection;
        $this->registrar = $registrar;
        $this->connectionConfiguration = $connectionConfiguration;
        $this->modules = $modules;
        $this->ormConfiguration = $ormConfiguration;
    }

    /**
     * @return EntityManagerInterface
     */
    public function getManager(): EntityManagerInterface
    {
        if (!$this->entityManager) {
            $this->initialize();
        }

        return $this->entityManager;
    }

    /**
     * @throws LocalizedException
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Doctrine\ORM\ORMException
     */
    private function initialize()
    {
        $connection = $this->resourceConnection->getConnection()->getConnection();

        if (!$connection instanceof \PDO) {
            throw new LocalizedException(__('Expected PDO connection, got ' . get_class($connection)));
        }

        $this->initDoctrineClassLoaders();

        $isDev = $this->state->getMode() === State::MODE_DEVELOPER;

        $cache = ($isDev)
            ? new ArrayCache()
            : new ArrayCache(); // todo, detect if redis or memcached is present

        $configuration = $this->ormConfiguration;

        if (!$configuration) {
            $configuration = new Configuration();

            $this
                ->eventManager
                ->dispatch(
                    'doctrine_configure_before',
                    [
                        'ormConfiguration' => $configuration,
                    ]
                );

            $configuration->setMetadataCacheImpl($cache);

            $driverImpl = $configuration->newDefaultAnnotationDriver($this->getPaths(), false);

            $configuration->setMetadataDriverImpl($driverImpl);

            $configuration->setQueryCacheImpl($cache);

            $configuration->setProxyDir(BP . '/var/doctrine_proxies/');

            $configuration->setAutoGenerateProxyClasses($isDev);

            $configuration->setProxyNamespace('JohnRogar\DoctrineProxies');

            $namingStrategy = new \Doctrine\ORM\Mapping\UnderscoreNamingStrategy(CASE_LOWER);

            $configuration->setNamingStrategy($namingStrategy);
        }

        $configuration->setRepositoryFactory($this->repositoryFactory);

        // add uuid support by default
        if (!Type::hasType('uuid')) {
            Type::addType('uuid', 'Ramsey\Uuid\Doctrine\UuidType');
        }

        $connectionDriver = DriverManager::getConnection(
            [
                'driver' => 'pdo_' . $connection->getAttribute(\PDO::ATTR_DRIVER_NAME),
                'pdo' => $connection,
                //'server_version' => 'mariadb-10.2.14'
            ],
            $configuration,
            $this->doctrineEventManager
        );

        $connectionConfiguration = [];

        if (is_array($this->connectionConfiguration) && !empty($this->connectionConfiguration)) {
            $connectionConfiguration = array_replace($connectionConfiguration, $this->connectionConfiguration);
        }

        $this
            ->eventManager
            ->dispatch(
                'core_orm_manager_type_doctrine_create_before',
                [
                    'connectionConfiguration' => $connectionConfiguration,
                    'ormConfiguration' => $configuration,
                ]
            );

        $this->entityManager = EntityManager::create(
            $connectionDriver,
            $configuration,
            $this->doctrineEventManager
        );
    }

    /**
     * @return array
     */
    private function getPaths()
    {
        $paths = [];

        $modulePaths = $this->registrar->getPaths(ComponentRegistrar::MODULE);

        foreach ($this->modules as $module) {
            if (array_key_exists($module, $modulePaths)) {
                $entityPath = rtrim($modulePaths[$module], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'Entity';
                if (\is_dir($entityPath)) {
                    $paths[] = $entityPath;
                }
            }
        }

        return $paths;
    }

    /**
     * Temporary fix for gedmo
     */
    private function initDoctrineClassLoaders()
    {
        \Doctrine\Common\Annotations\AnnotationRegistry::registerFile(BP
            . '/vendor/gedmo/doctrine-extensions/lib/Gedmo/Mapping/Annotation/Timestampable.php');
    }
}
