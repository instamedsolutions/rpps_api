<?php

namespace App\Command;

use App\Entity\Specialty;
use App\Entity\TranslatableEntityInterface;
use App\Entity\Translation;
use App\Service\SpecialtyService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:specialty:translate',
    description: 'This command will import all specialties\'s translations from a CSV file.',
)]
class SpecialtyTranslationCommand extends Command
{
    public function __construct(protected SpecialtyService $specialtyService, protected EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $specialtiesFilePath = __DIR__ . '/../../data/specialties/specialties_en.csv';

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

        // Load data from csv
        $io->writeln('<info>Importing specialties translations...</info>');

        $file = fopen($specialtiesFilePath, 'r');

        // Read the headers
        $headers = fgetcsv($file, separator: ';');

        // Initialize an empty array to store data
        $data = [];

        // Loop through the file and map data to headers
        while (($row = fgetcsv($file, separator: ';')) !== false) {
            $rowData = array_combine($headers, $row);
            $data[$rowData['canonical']] = $rowData;
        }

        $specialties = $this->em->getRepository(Specialty::class)->findAll();

        foreach ($specialties as $specialty) {
            $values = $data[$specialty->getCanonical()];

            $this->createTranslation($specialty, $values['english_name'], 'name');
            $this->createTranslation($specialty, $values['english_specialist_name'], 'specialistName');

            $output->writeln("Specialty {$specialty->getCanonical()} translated with {$values['english_name']}");
        }

        $this->em->flush();

        $output->writeln('<info>Specialties translations imported successfully!</info>');

        return Command::SUCCESS;
    }

    private function createTranslation(TranslatableEntityInterface $entity, string $value, string $field): void
    {
        $translation = new Translation();
        $translation->setLang('en');
        $translation->setField($field);
        $translation->setTranslation($value);
        $entity->addTranslation($translation);
        $this->em->persist($translation);
    }
}
