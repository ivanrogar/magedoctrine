<?php

declare(strict_types=1);

namespace JohnRogar\MageDoctrine\Model\Repository\Factory;

use Doctrine\ORM\Repository\RepositoryFactory;
use Doctrine\ORM\EntityManagerInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class DoctrineFactory
 * @package JohnRogar\MageDoctrine\Model\Repository\Factory
 */
class DoctrineFactory implements RepositoryFactory
{

    /**
     * The list of EntityRepository instances.
     *
     * @var \Doctrine\Common\Persistence\ObjectRepository[]
     */
    private $repositoryList = [];

    /**
     * @var ObjectManagerInterface
     */
    private $container;

    /**
     * @param ObjectManagerInterface $container
     */
    public function __construct(ObjectManagerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function getRepository(EntityManagerInterface $entityManager, $entityName)
    {
        $repositoryHash = $entityManager->getClassMetadata($entityName)->getName() . spl_object_hash($entityManager);

        if (isset($this->repositoryList[$repositoryHash])) {
            return $this->repositoryList[$repositoryHash];
        }

        return $this->repositoryList[$repositoryHash] = $this->createRepository($entityManager, $entityName);
    }

    /**
     * Create a new repository instance for an entity class.
     *
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager The EntityManager instance.
     * @param string                               $entityName    The name of the entity.
     *
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    private function createRepository(EntityManagerInterface $entityManager, $entityName)
    {
        /* @var $metadata \Doctrine\ORM\Mapping\ClassMetadata */
        $metadata            = $entityManager->getClassMetadata($entityName);
        $repositoryClassName = $metadata->customRepositoryClassName
            ?: $entityManager->getConfiguration()->getDefaultRepositoryClassName();

        return $this->container->create($repositoryClassName, [$entityManager, $metadata]);
    }
}
