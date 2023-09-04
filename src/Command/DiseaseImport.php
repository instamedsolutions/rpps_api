<?php

namespace App\Command;

use App\Service\DiseaseService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to import file in empty database.
 **/
class DiseaseImport extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:disease:import';

    protected string $projectDir;

    public function __construct(protected DiseaseService $diseaseService, protected EntityManagerInterface $em)
    {
        parent::__construct(self::$defaultName);
    }

    protected function configure(): void
    {
        $this->setDescription('Import all diseases File into database')
            ->setHelp('This command will import all diseases data.');

        $this->addOption(
            'process',
            'pr',
            InputOption::VALUE_OPTIONAL,
            'the process you want to run'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->diseaseService->setOutput($output);

        try {
            // Turning off doctrine default logs queries for saving memory
            $this->em->getConnection()->getConfiguration()->setSQLLogger(null);

            // Showing when the cps process is launched
            $start = new DateTime();
            $output->writeln('<comment>' . $start->format('d-m-Y G:i:s') . ' Start processing :---</comment>');

            $this->diseaseService->importFiles($output, 'cim10');

            // Showing when the cps process is launched
            $end = new DateTime();
            $output->writeln('<comment>' . $end->format('d-m-Y G:i:s') . ' Stop processing :---</comment>');

            return Command::SUCCESS;
        } catch (Exception $e) {
            error_log($e->getMessage());
            $output->writeln($e->getMessage());

            return Command::FAILURE;
        }
    }
}
