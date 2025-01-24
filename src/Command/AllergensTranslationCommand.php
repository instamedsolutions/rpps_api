<?php

namespace App\Command;

use App\Entity\Allergen;
use App\Entity\TranslatableEntityInterface;
use App\Entity\Translation;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:allergens:translate',
    description: 'This command will import all specialties\'s translations from a CSV file.',
)]
class AllergensTranslationCommand extends Command
{
    public function __construct(protected EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $filePath = __DIR__ . '/../../data/allergens_en.csv';

        $start = new DateTime();
        $io->writeln('<comment>' . $start->format('d-m-Y G:i:s') . ' Start processing :---</comment>');

        // Turning off doctrine default logs queries for saving memory
        $this->em->getConnection()->getConfiguration()->setSQLLogger();

        // Load data from csv
        $io->writeln('<info>Importing allergens translations...</info>');

        $file = fopen($filePath, 'r');

        // Read the headers
        $headers = fgetcsv($file, separator: ',');

        // Initialize an empty array to store data
        $data = [];

        // Loop through the file and map data to headers
        while (($row = fgetcsv($file, separator: ',')) !== false) {
            $rowData = array_combine($headers, $row);
            $data[$rowData['code']] = $rowData;
        }

        $allergens = $this->em->getRepository(Allergen::class)->findAll();

        foreach ($allergens as $allergen) {
            $values = $data[$allergen->getCode()] ?? null;

            if (!$values) {
                $output->writeln("Allergen {$allergen->getName()} not found in the CSV file");
                continue;
            }

            $this->createTranslation($allergen, $values['english_name'], 'name');
            $this->createTranslation($allergen, $values['english_group'], 'group');

            $output->writeln("Allergen {$allergen->getName()} translated with {$values['english_name']}");
        }

        $this->em->flush();

        $output->writeln('<info>Allergens translations imported successfully!</info>');

        return Command::SUCCESS;
    }

    private function createTranslation(TranslatableEntityInterface $entity, string $value, string $field): void
    {
        $translation = new Translation();
        $translation->setLang('en');
        $translation->setField($field);
        $translation->setTranslation(trim($value));
        $entity->addTranslation($translation);
        $this->em->persist($translation);
    }
}
