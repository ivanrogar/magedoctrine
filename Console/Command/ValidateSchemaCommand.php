<?php

declare(strict_types=1);

namespace JohnRogar\MageDoctrine\Console\Command;

use Doctrine\ORM\Tools\SchemaValidator;
use JohnRogar\MageDoctrine\Api\ManagerInterface;
use Magento\Framework\App\Area;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\State;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ValidateSchemaCommand
 * @package JohnRogar\MageDoctrine\Console\Command
 * @SuppressWarnings(LongVariable)
 */
class ValidateSchemaCommand extends Command
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
            ->setName('johnrogar:doctrine:validate-schema')
            ->setDescription('Validate Doctrine schema');

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

        $output->writeln('Validating database schema ... ');

        $manager = $this->manager->getManager();

        $validator = new SchemaValidator($manager);

        $errors = $validator->validateMapping();

        if (count($errors)) {
            echo implode(PHP_EOL . PHP_EOL, $errors);
            return -1;
        }

        $output->writeln('Everything is fine');

        return 0;
    }
}
