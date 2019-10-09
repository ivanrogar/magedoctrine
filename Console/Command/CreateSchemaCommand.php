<?php

declare(strict_types=1);

namespace JohnRogar\MageDoctrine\Console\Command;

use Doctrine\ORM\Tools\SchemaTool;
use JohnRogar\MageDoctrine\Api\ManagerInterface;
use Magento\Framework\App\Area;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\State;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CreateSchemaCommand
 * @package JohnRogar\MageDoctrine\Console\Command
 * @SuppressWarnings(LongVariable)
 */
class CreateSchemaCommand extends Command
{

    private $state;

    private $manager;

    /**
     * CreateSchemaCommand constructor.
     * @param State $state
     * @param ManagerInterface $manager
     */
    public function __construct(
        State $state,
        ManagerInterface $manager
    ) {
        parent::__construct();
        $this->state = $state;
        $this->manager = $manager;
    }

    protected function configure()
    {
        $this
            ->setName('johnrogar:doctrine:create-schema')
            ->setDescription('Create Doctrine schema');

        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws \Doctrine\ORM\Tools\ToolsException
     * @SuppressWarnings(UnusedFormalParameter)
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        try {
            $this->state->setAreaCode(Area::AREA_ADMINHTML);
        } catch (LocalizedException $e) {
        }

        $output->writeln('Creating database schema ... ');

        $manager = $this->manager->getManager();

        $metaData = $manager->getMetadataFactory()->getAllMetadata();

        $schemaTool = new SchemaTool($manager);

        $schemaTool->createSchema($metaData);

        $output->writeln('Done');

        return 0;
    }
}
