<?php

declare(strict_types=1);

namespace JohnRogar\MageDoctrine\Console\Command;

use JohnRogar\MageDoctrine\Api\ManagerInterface;
use JohnRogar\MageDoctrine\Model\SchemaTool;
use Magento\Framework\App\Area;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\State;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class UpdateSchemaCommand
 * @package JohnRogar\MageDoctrine\Console\Command
 * @SuppressWarnings(LongVariable)
 */
class UpdateSchemaCommand extends Command
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
            ->setName('johnrogar:doctrine:update-schema')
            ->setDescription('Update Doctrine schema')
            ->addOption('force', null, InputOption::VALUE_NONE)
            ->addOption(
                'dump-sql',
                null,
                InputOption::VALUE_NONE,
                'Dumps the generated SQL statements to the screen (does not execute them).'
            );

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

        if (!$input->getOption('dump-sql') && !$input->getOption('force')) {
            $style->error('You must explicitly include the force parameter!');
            return -1;
        }

        $manager = $this->manager->getManager();

        $metaData = $manager->getMetadataFactory()->getAllMetadata();

        $schemaTool = new SchemaTool($manager);

        $sqls = $schemaTool->getUpdateSchemaSql($metaData);

        if (empty($sqls)) {
            $style->success('Nothing to update - your database is already in sync with the current entity metadata.');
            return 0;
        }

        if ($input->getOption('dump-sql')) {
            $style->text('The following SQL statements will be executed:');
            $style->newLine();

            foreach ($sqls as $sql) {
                $style->text(sprintf('    %s;', $sql));
            }

            return 0;
        }

        if ($input->getOption('force')) {
            $style->newLine();
            $style->text('Updating database schema...');
            $style->newLine();

            $schemaTool->updateSchema($metaData);

            $pluralization = (1 === count($sqls)) ? 'query was' : 'queries were';

            $style->text(sprintf('    <info>%s</info> %s executed', count($sqls), $pluralization));
            $style->success('Database schema updated successfully!');
            $output->writeln('Done');
        }

        return 0;
    }
}
