<?php

namespace App\Command;

use App\Service\InseeService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:insee:import',
    description: 'Command to import data from INSEE CSV files.'
)]
class InseeImportCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly InseeService $inseeService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'purge',
                null,
                InputOption::VALUE_NONE,
                'Purge all related data tables before the import.'
            )
            ->addOption(
                'force',
                null,
                InputOption::VALUE_NONE,
                'Must be set to allow the purge when using --purge.'
            )
            ->addOption(
                'target',
                null,
                InputOption::VALUE_REQUIRED,
                'Which data to import? Choose "commune","commune1943","event","pays","pays1943" or "all".',
                'all'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $purge = (bool) $input->getOption('purge');
        $force = (bool) $input->getOption('force');
        $target = strtolower($input->getOption('target'));

        // If purge is requested but --force is not provided, abort
        if ($purge && !$force) {
            $io->error('You must provide --force to allow the purge when using --purge. Aborting.');

            return Command::FAILURE;
        }

        // Show a brief recap of the chosen options
        $io->section('Option Recap');
        $io->writeln([
            ' - Purge: ' . ($purge ? 'Yes' : 'No'),
            ' - Force: ' . ($force ? 'Yes' : 'No'),
            " - Target: $target",
        ]);

        // Paths to CSV files
        $communeFilePath = __DIR__ . '/../../data/insee/v_commune_2024.csv';
        $commune1943FilePath = __DIR__ . '/../../data/insee/v_commune_depuis_1943.csv';
        $communeEventFilePath = __DIR__ . '/../../data/insee/v_mvt_commune_2024.csv';
        $paysFilePath = __DIR__ . '/../../data/insee/v_pays_territoire_2024.csv';
        $pays1943FilePath = __DIR__ . '/../../data/insee/v_pays_et_territoire_depuis_1943.csv';

        // Pass output + verbosity to the service
        $this->inseeService->setOutput($output);
        $verbose = $output->isVerbose();
        $veryVerbose = $output->isVeryVerbose();
        $this->inseeService->setVerbose($verbose || $veryVerbose);

        // Start time
        $startTime = new DateTime();
        $io->writeln("<comment>{$startTime->format('d-m-Y G:i:s')} - Start processing</comment>");
        $output->writeln("Import ID is {$this->inseeService->getImportId()}");

        // Turn off Doctrine's default SQL logger to save memory
        $this->em->getConnection()->getConfiguration()->setSQLLogger();

        // If purge is requested, execute it before importing
        if ($purge) {
            $io->writeln('<info>Purging all existing data...</info>');
            $this->inseeService->purgeAllData();
            $io->success('Purge completed successfully.');
        }

        // Launch imports based on target
        if ('all' === $target || 'commune' === $target) {
            $io->section('Importing: COMMUNES');
            $this->inseeService->importData($communeFilePath, 'commune');
            $io->success('Commune import completed successfully.');
        }

        if ('all' === $target || 'commune1943' === $target) {
            $io->section('Importing: COMMUNES 1943');
            $this->inseeService->importData($commune1943FilePath, 'commune1943');
            $io->success('Commune 1943 import completed successfully.');
        }

        if ('all' === $target || 'event' === $target) {
            $io->section('Importing: COMMUNE EVENTS');
            $this->inseeService->importData($communeEventFilePath, 'event');
            $io->success('Commune events import completed successfully.');
        }

        if ('all' === $target || 'pays' === $target) {
            $io->section('Importing: PAYS');
            $this->inseeService->importData($paysFilePath, 'pays');
            $io->success('Pays import completed successfully.');
        }

        if ('all' === $target || 'pays1943' === $target) {
            $io->section('Importing: PAYS 1943');
            $this->inseeService->importData($pays1943FilePath, 'pays1943');
            $io->success('Pays 1943 import completed successfully.');
        }

        $this->inseeService->printFinalStats();

        // End time + total execution time
        $endTime = new DateTime();
        $io->writeln("<comment>{$endTime->format('d-m-Y G:i:s')} - Stop processing</comment>");

        // Calculate & display total duration
        $durationSec = $endTime->getTimestamp() - $startTime->getTimestamp();
        $io->writeln("<comment>Total execution time: {$durationSec}s</comment>");

        $io->success('Job done master!');

        return Command::SUCCESS;
    }
}
