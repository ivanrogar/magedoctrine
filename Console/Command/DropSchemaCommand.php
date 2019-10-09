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
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class DropSchemaCommand
 * @package JohnRogar\MageDoctrine\Console\Command
 * @SuppressWarnings(LongVariable)
 */
class DropSchemaCommand extends Command
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
            ->setName('johnrogar:doctrine:drop-schema')
            ->setDescription('Drop Doctrine schema')
            ->addOption('force', null, InputOption::VALUE_NONE);

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

        $style = new SymfonyStyle($input, $output);

        if (!$input->getOption('force')) {
            $style->error('You must explicitly include the force parameter!');
            return -1;
        }

        $style->warning('Dropping database schema ... ');

        $manager = $this->manager->getManager();

        $metaData = $manager->getMetadataFactory()->getAllMetadata();

        $schemaTool = new SchemaTool($manager);

        $schemaTool->dropSchema($metaData);

        $style->success('Done');

        return 0;
    }
}
