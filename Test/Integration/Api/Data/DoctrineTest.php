<?php
// phpcs:ignorefile

namespace JohnRogar\MageDoctrine\Test\Integration\Api\Data;

use Doctrine\ORM\EntityManagerInterface;
use JohnRogar\MageDoctrine\Api\ManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\ObjectManagerInterface;

/**
 * @covers \JohnRogar\MageDoctrine\Api\Data\Doctrine
 * @magentoAppIsolation enabled
 * @magentoAppArea frontend
 * @magentoCache all disabled
 * @SuppressWarnings(PHPMD)
 */
class DoctrineTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var ObjectManagerInterface
     */
    public $objectManager;

    /**
     * @return void
     */
    public function setup()
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * @test
     */
    public function it_should_initialize()
    {
        $ormFactory = $this->objectManager->get('JohnRogar\MageDoctrine\Api\ManagerInterfaceFactory');

        /**
         * @var ManagerInterface $orm
         */
        $orm = $ormFactory->create();

        $this->assertTrue($orm->getManager() instanceof EntityManagerInterface);
    }
}
