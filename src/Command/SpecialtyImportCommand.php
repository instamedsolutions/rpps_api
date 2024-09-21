<?php

namespace App\Command;

use App\Service\SpecialtyService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:specialty:import',
    description: 'This command will import all specialties from a CSV file.',
)]
class SpecialtyImportCommand extends Command
{
    public function __construct(protected SpecialtyService $specialtyService, protected EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $specialtiesFilePath = __DIR__ . '/../../data/specialties.csv';
        $linkFilePath = __DIR__ . '/../../data/specialties_link.csv';

        $this->specialtyService->setOutput($output);

        // Get the verbosity level and pass it to the service
        $verbose = $output->isVerbose();  // For -vv verbosity
        $veryVerbose = $output->isVeryVerbose();  // For -vvv verbosity
        $this->specialtyService->setVerbose($verbose || $veryVerbose);

        $start = new DateTime();
        $io->writeln('<comment>' . $start->format('d-m-Y G:i:s') . ' Start processing :---</comment>');
        $output->writeln("Import id is {$this->specialtyService->getImportId()}");

        // Turning off doctrine default logs queries for saving memory
        $this->em->getConnection()->getConfiguration()->setSQLLogger();

        $io->writeln('<info>Purging all existing specialties...</info>');
        $this->specialtyService->purgeAllSpecialties();
        $io->success('Purge completed successfully.');

        // Step 1: Import specialties
        $io->writeln('<info>Importing specialties...</info>');
        $this->specialtyService->importData($specialtiesFilePath, 'specialties');
        $io->success('Specialties imported successfully!');

        // Step 2: Link specialties
        $io->writeln('<info>Linking specialties...</info>');
        $this->specialtyService->importData($linkFilePath, 'link');
        $io->success('Specialties linking completed successfully!');

        $end = new DateTime();
        $io->writeln('<comment>' . $end->format('d-m-Y G:i:s') . ' Stop processing :---</comment>');
        $io->success('Job done master!');

        return Command::SUCCESS;
    }
}
