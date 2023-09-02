<?php

namespace App\Command;

use App\Service\RPPSService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to import file in empty database.
 */
class RppsImport extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:rpps:import';

    /**
     * RppsImport constructor.
     */
    public function __construct(protected RPPSService $rppsService, protected EntityManagerInterface $em)
    {
        parent::__construct(self::$defaultName);
    }

    protected function configure(): void
    {
        $this->setDescription('Import RPPS File into database')
            ->setHelp('This command will import a RPPS CSV file into your database.');

        $this->addOption(
            'process',
            'pr',
            InputOption::VALUE_OPTIONAL,
            'the process you want to run'
        );

        $this->addOption(
            'start-line',
            'st',
            InputOption::VALUE_OPTIONAL,
            'The line to start the import from'
        );

        $this->addOption(
            'limit',
            'lt',
            InputOption::VALUE_OPTIONAL,
            'The limit of the import size'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $process = $input->getOption('process');

        $startLine = $input->getOption('start-line') ?? 0;
        $limit = $input->getOption('limit') ?? 0;

        $this->rppsService->setOutput($output);

        try {
            // Turning off doctrine default logs queries for saving memory
            $this->em->getConnection()->getConfiguration()->setSQLLogger(null);

            // Showing when the cps process is launched
            $start = new DateTime();
            $output->writeln('<comment>' . $start->format('d-m-Y G:i:s') . ' Start processing :---</comment>');

            if ($process) {
                $this->rppsService->importFile($output, $process, $startLine, $limit);
            } else {
                $this->rppsService->importFile($output, 'rpps', $startLine, $limit);
                $this->rppsService->importFile($output, 'cps', $startLine, $limit);
            }

            // Showing when the cps process is launched
            $end = new DateTime();
            $output->writeln('<comment>' . $end->format('d-m-Y G:i:s') . ' Stop processing :---</comment>');

            $this->rppsService->loadTestData();

            return Command::SUCCESS;
        } catch (Exception $e) {
            error_log($e->getMessage());
            $output->writeln($e->getTraceAsString());

            return Command::FAILURE;
        }
    }
}
