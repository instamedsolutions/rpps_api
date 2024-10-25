<?php

namespace App\Command;

use App\Service\CityService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:city:import',
    description: 'This command will import all French cities, departments, and regions.',
)]
class CityImportCommand extends Command
{
    public function __construct(protected CityService $cityService, protected EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'purge',
                null,
                InputOption::VALUE_NONE,
                'Purge all existing regions, departments, and cities before import'
            )
            ->addOption(
                'population-only',
                'p',
                InputOption::VALUE_NONE,
                'Import only population data, purging population data before'
            )
            ->addOption(
                'coordinates-only',
                'c',
                InputOption::VALUE_NONE,
                'Import only coordinates data, purging coordinates data before'
            )
            ->addOption(
                'cities-only',
                'co',
                InputOption::VALUE_NONE,
                'Import only cities data, purging coordinates data before'
            );

        $this->addOption(
            'start-line',
            'st',
            InputOption::VALUE_OPTIONAL,
            'The line to start the import from'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $startLine = $input->getOption('start-line') ?? 0;

        $io = new SymfonyStyle($input, $output);
        $purge = $input->getOption('purge');
        $populationOnly = $input->getOption('population-only');
        $coordinatesOnly = $input->getOption('coordinates-only');
        $citiesOnly = $input->getOption('cities-only');

        $regionFilePath = __DIR__ . '/../../data/cities/regions.csv';
        $departmentFilePath = __DIR__ . '/../../data/cities/departments.csv';
        $citiesFilePath = __DIR__ . '/../../data/cities/cities.csv';
        $populationFilePath = __DIR__ . '/../../data/cities/population.csv';
        $coordinateFilePath = __DIR__ . '/../../data/cities/coordinates.csv';

        $this->cityService->setOutput($output);

        // Get the verbosity level and pass it to the service
        $verbose = $output->isVerbose();  // For -vv verbosity
        $veryVerbose = $output->isVeryVerbose();  // For -vvv verbosity
        $this->cityService->setVerbose($verbose || $veryVerbose); // Pass verbosity to the service

        $start = new DateTime();
        $io->writeln('<comment>' . $start->format('d-m-Y G:i:s') . ' Start processing :---</comment>');
        $output->writeln("Import id is {$this->cityService->getImportId()}");

        // Turning off doctrine default logs queries for saving memory
        $this->em->getConnection()->getConfiguration()->setSQLLogger();

        if ($populationOnly) {
            if (!$startLine) {
                $io->writeln('<info>Purging all population data...</info>');
                $this->cityService->purgePopulation();
                $io->success('Population purge completed successfully.');
            }
            $this->cityService->importData($populationFilePath, 'population', ';', $startLine);
            $this->cityService->aggregatePopulationForMainCities();
        } elseif ($coordinatesOnly) {
            if (!$startLine) {
                $io->writeln('<info>Purging all coordinates data...</info>');
                $this->cityService->purgeCoordinates();
                $io->success('Coordinates purge completed successfully.');
            }
            $this->cityService->importData($coordinateFilePath, 'coordinates', startLine: $startLine);
        } else {
            if ($purge) {
                $io->writeln('<info>Purging all existing regions, departments, and cities...</info>');
                $this->cityService->purgeAllData();
                $io->success('Purge completed successfully.');
            }

            $this->cityService->importData($regionFilePath, 'region');
            $this->cityService->importData($departmentFilePath, 'department');
            $this->cityService->importData($citiesFilePath, 'city', ';', $startLine);
            if (!$citiesOnly) {
                $this->cityService->importData($coordinateFilePath, 'coordinates', startLine: $startLine);
                $this->cityService->importData($populationFilePath, 'population', ';', $startLine);
                $this->cityService->aggregatePopulationForMainCities();
            }
        }

        $end = new DateTime();
        $io->writeln('<comment>' . $end->format('d-m-Y G:i:s') . ' Stop processing :---</comment>');
        $io->success('Job done master!');

        return Command::SUCCESS;
    }
}
