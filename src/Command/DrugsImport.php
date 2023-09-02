<?php

namespace App\Command;

use App\Service\DrugService;
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
class DrugsImport extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:drugs:import';

    public function __construct(protected DrugService $drugService, protected EntityManagerInterface $em)
    {
        parent::__construct(self::$defaultName);
    }

    protected function configure(): void
    {
        $this->setDescription('Import Drugs File into database')
            ->setHelp('This command will import all drugs data.');

        $this->addOption(
            'process',
            'pr',
            InputOption::VALUE_OPTIONAL,
            'the process you want to run'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->drugService->setOutput($output);

        $process = $input->getOption('process');

        try {
            // Turning off doctrine default logs queries for saving memory
            $this->em->getConnection()->getConfiguration()->setSQLLogger(null);

            // Showing when the cps process is launched
            $start = new DateTime();
            $output->writeln('<comment>' . $start->format('d-m-Y G:i:s') . ' Start processing :---</comment>');

            if ($process) {
                $this->drugService->importFile($output, $process);
            } else {
                $this->drugService->importFile($output, 'DRUGS_URL_CIS_BDPM');
                $this->drugService->importFile($output, 'DRUGS_URL_CIS_CIP_BDPM');
                $this->drugService->importFile($output, 'DRUGS_URL_CIS_CPD_BDPM');
                $this->drugService->importFile($output, 'DRUGS_URL_CIS_GENER_BDPM');
                $this->drugService->importFile($output, 'DRUGS_URL_CIS_InfoImportantes');
            }
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
