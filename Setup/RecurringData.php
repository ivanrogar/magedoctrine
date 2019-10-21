<?php

declare(strict_types=0);

namespace JohnRogar\MageDoctrine\Setup;

use JohnRogar\MageDoctrine\Api\ManagerInterfaceFactory;
use JohnRogar\MageDoctrine\Model\SchemaTool;
use Magento\Framework\App\State;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Class RecurringData
 * @package JohnRogar\MageDoctrine\Setup
 */
class RecurringData implements InstallDataInterface
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
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        echo ' -> SCHEMA UPDATE ... ';

        if ($this->state->getMode() === State::MODE_PRODUCTION) {
            echo 'skipping [PRODUCTION MODE]' . PHP_EOL;
            return;
        }

        $manager = $this->ormFactory->create()->getManager();

        $metaData = $manager->getMetadataFactory()->getAllMetadata();

        $schemaTool = new SchemaTool($manager);

        $sqls = $schemaTool->getUpdateSchemaSql($metaData);

        if (empty($sqls)) {
            echo 'no changes' . PHP_EOL;
            return;
        }

        $schemaTool->updateSchema($metaData);

        echo count($sqls) . ' queries executed' . PHP_EOL;
    }
}
