<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\String\UnicodeString;

#[AsCommand(
    name: 'app:lat-lng:import',
    description: 'Import lat/lng data into database'
)]
class LatLngImportCommand extends Command
{
    private OutputInterface $output;

    public function __construct(
        private readonly string $projectDir,
        private readonly EntityManagerInterface $em
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            'department',
            'dp',
            InputOption::VALUE_OPTIONAL,
            'The department to import'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->output = $output;
        $department = $input->getOption('department');

        $departments = $department ? [$department] : $this->getAllDepartments();

        $updated = $errors = 0;

        foreach ($departments as $department) {
            $this->importDepartment($department, $updated, $errors);
        }

        $this->output->writeln("Updated $updated RPPS, $errors errors");

        return Command::SUCCESS;
    }

    private function getAllDepartments(): array
    {
        $departments = array_map(fn ($item) => str_pad((string) $item, 2, '0', STR_PAD_LEFT), range(1, 95));
        unset($departments[19]);

        return array_merge($departments, ['2A', '2B', '971', '972', '973', '974', '975', '976', '977', '978', '984', '986', '987', '988', '989']);
    }

    private function importDepartment(string $department, int &$updated = 0, int &$errors = 0): void
    {
        $gzip = "{$this->projectDir}/var/lat-lng/adresses-{$department}.csv.gz";
        $csv = "{$this->projectDir}/var/lat-lng/adresses-{$department}.csv";

        $this->output->writeln("Importing department $department");

        if (!file_exists($csv)) {
            $this->downloadAndUncompress($gzip, $department);
        } else {
            $this->output->writeln("Using existing file $csv");
        }

        $addresses = $this->parseCsvFile($csv);

        $this->updateRppsData($addresses, $department, $updated, $errors);
    }

    private function downloadAndUncompress(string $gzip, string $department): void
    {
        $url = "https://adresse.data.gouv.fr/data/ban/adresses/latest/csv/adresses-{$department}.csv.gz";
        $this->output->writeln("Downloading $url");

        $content = file_get_contents($url);
        if (!is_dir(dirname($gzip))) {
            mkdir(dirname($gzip), 0777, true);
        }

        file_put_contents($gzip, $content);
        $this->output->writeln("Uncompressing $gzip");

        $csvData = gzopen($gzip, 'r');
        file_put_contents(str_replace('.gz', '', $gzip), gzread($csvData, 1000000000));
    }

    private function parseCsvFile(string $csv): array
    {
        $csv = fopen($csv, 'r');
        fgetcsv($csv, 0, ';'); // Skip header

        $addresses = [];
        while ($line = fgetcsv($csv, 0, ';')) {
            foreach ($this->createAddressEntries($line) as $key => $ad) {
                $addresses[$key] = $ad;
            }
        }

        return $addresses;
    }

    private function createAddressEntries(array $line): array
    {
        $address1 = (new UnicodeString("$line[2] $line[18] $line[5]"))->ascii()->lower()->toString();
        $address2 = (new UnicodeString("$line[2] $line[18] $line[7]"))->ascii()->lower()->toString();

        return [
            $address1 => ['longitude' => $line[12], 'latitude' => $line[13], 'address' => "$line[2] $line[4]"],
            $address2 => ['longitude' => $line[12], 'latitude' => $line[13], 'address' => "$line[2] $line[4]"],
        ];
    }

    private function updateRppsData(array $addresses, string $department, int &$updated = 0, int &$errors = 0): void
    {
        $rpps = $this->em->getConnection()->fetchAllAssociative(
            'SELECT id, address, zipcode, city FROM rpps WHERE address IS NOT NULL
           AND latitude IS NULL 
           AND zipcode LIKE :department',
            ['department' => "{$department}%"]
        );

        $this->output->writeln('Found ' . count($rpps) . " RPPS without lat/lng data for department $department");

        foreach ($rpps as $item) {
            $address = $this->normalizeAddress($item['address']);
            $addressKey = "$address {$item['zipcode']}";
            $cityKey = "$address {$item['city']}";

            if (isset($addresses[$addressKey]) || isset($addresses[$cityKey])) {
                $found = $addresses[$addressKey] ?? $addresses[$cityKey];

                $this->em->getConnection()->executeQuery(
                    'UPDATE rpps SET address = :address, latitude = :latitude, longitude = :longitude WHERE id = :id',
                    [
                        'address' => $found['address'],
                        'latitude' => $found['latitude'],
                        'longitude' => $found['longitude'],
                        'id' => $item['id'],
                    ]
                );

                ++$updated;
            } else {
                ++$errors;
                $this->output->writeln("<error>Could not find $address</error>");
            }

            if (0 === $updated % 100) {
                $this->output->writeln("Updated $updated RPPS");
            }
        }

        $this->output->writeln("Updated $updated RPPS, $errors errors in department $department");
    }

    private function normalizeAddress(string $address): string
    {
        $address = mb_strtolower($address);
        if (str_starts_with($address, '0')) {
            $address = substr($address, 1);
        }

        $replacements = [
            ' all ' => ' allee ',
            ' bd ' => ' boulevard ',
            ' av ' => ' avenue ',
            ' r ' => ' rue ',
            ' pl ' => ' place ',
            ' st ' => ' saint ',
            ' pte ' => ' porte ',
            ' prom ' => ' promenade ',
            "'" => ' ',
            ' renouilliers ' => ' Renouillers ',
            'genral' => 'general',
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $address);
    }
}
