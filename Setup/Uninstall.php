<?php

declare(strict_types=0);

namespace JohnRogar\MageDoctrine\Setup;

use Doctrine\ORM\Tools\ToolsException;
use JohnRogar\MageDoctrine\Api\ManagerInterfaceFactory;
use JohnRogar\MageDoctrine\Model\SchemaTool;
use Magento\Framework\App\State;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UninstallInterface;

/**
 * Class InstallData
 * @package JohnRogar\MageDoctrine\Setup
 */
class Uninstall implements UninstallInterface
{

    private $ormFactory;

    private $state;

    /**
     * RecurringData constructor.
     * @param ManagerInterfaceFactory $managerFactory
     * @param State $state
     */
    public function __construct(
        ManagerInterfaceFactory $managerFactory,
        State $state
    ) {
        $this->ormFactory = $managerFactory;
        $this->state = $state;
    }

    /**
     * @inheritDoc
     * @throws ToolsException
     */
    public function uninstall(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        echo ' -> SCHEMA DROP ... ';

        if ($this->state->getMode() === State::MODE_PRODUCTION) {
            echo 'skipping [PRODUCTION MODE]' . PHP_EOL;
            return;
        }

        $manager = $this->ormFactory->create()->getManager();

        $metaData = $manager->getMetadataFactory()->getAllMetadata();

        $schemaTool = new SchemaTool($manager);

        $schemaTool->dropSchema($metaData);
    }
}
