<?php

namespace App\Command;

use App\Entity\Cim11;
use App\Entity\Cim11Modifier;
use App\Entity\Cim11ModifierValue;
use App\Entity\ModifierType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('app:cim11:import')]
class Cim11Import extends Command
{
    private array $modifierValues = [];

    private array $diseases = [];

    private array $cim11Mapping = [];

    private string $importId;

    private OutputInterface $output;

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly string $projectDir,
    ) {
        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        ini_set('memory_limit', '-1');
        $this->importId = uniqid();
        $this->output = $output;

        $this->importModifiers();
        $this->importDiseases();

        return self::SUCCESS;
    }

    private function importDiseases(): void
    {
        $this->buildCim10Cim11Database();

        $this->output->writeln('Importing diseases...');

        $file = "{$this->projectDir}/var/cim-11.csv";

        $this->readCsv($file, ',', function (array $data) {
            if (!$data['code']) {
                $this->output->writeln("No code found, skipping {$data['title']}");

                return;
            }

            if (!$data['id']) {
                $this->output->writeln("No id found, skipping {$data['title']}");

                return;
            }

            if (isset($this->modifierValues[$data['id']])) {
                $this->output->writeln("{$data['id']} is a modifier, ignore");
                //  return;
            }

            $existing = $this->em->getRepository(Cim11::class)->findOneBy([
                'code' => $data['code'],
            ]);
            if ($existing) {
                $this->output->writeln("{$data['title']} already exists");
                //   return;
            }

            $this->output->writeln("Importing {$data['title']}");

            $cim11Disease = $existing ?? new Cim11();
            $cim11Disease->setWhoId($data['id']);
            $cim11Disease->setCode($data['code']);
            $cim11Disease->setName($data['title']);
            $cim11Disease->setSynonyms(explode(';', $data['synonyms']));

            $basicHierarchyLevel = 2;
            $explode = explode('.', $data['code']);
            $hierarchyLevel = strlen($explode[1] ?? '') + $basicHierarchyLevel;

            $cim11Disease->setHierarchyLevel($hierarchyLevel);
            $cim11Disease->setCim10Code($this->cim11Mapping[$data['code']] ?? null);
            $cim11Disease->importId = $this->importId;

            foreach (ModifierType::cases() as $case) {
                if (!$data[$case->value]) {
                    continue;
                }

                if ($cim11Disease->hasModifier($case)) {
                    $this->output->writeln("Modifier {$case->value} already set in $cim11Disease");
                    continue;
                }

                $modifier = new Cim11Modifier();
                $modifier->setType($case);
                $modifier->importId = $this->importId;

                $elem = json_decode($data[$case->value], true);

                $modifier->setMultiple('NotAllowed' !== $elem['allowMultipleValues']);

                foreach ($elem['ids'] as $id) {
                    $value = $this->modifierValues[$id] ?? null;

                    if (!$value) {
                        $this->output->writeln("Value {$id} not found");
                        continue;
                    }
                    $modifier->addValue($value);
                }
                $modifier->setCim11($cim11Disease);
            }

            if ($data['parent_id'] && isset($this->diseases[$data['parent_id']])) {
                $cim11Disease->setParent($this->diseases[$data['parent_id']]);
            }

            $this->em->persist($cim11Disease);

            $this->diseases[$data['id']] = $cim11Disease;
        });

        $this->em->flush();
    }

    private function importModifiers(): void
    {
        $this->output->writeln('Importing modifiers...');

        $file = "{$this->projectDir}/var/modifiers.csv";

        $this->readCsv($file, ',', function (array $data) {
            if (!$data['code']) {
                $this->output->writeln("No code found, skipping {$data['title']}");

                return;
            }

            if (!$data['id']) {
                $this->output->writeln('Id is empty');

                return;
            }

            $existing = $this->em->getRepository(Cim11ModifierValue::class)->findOneBy([
                'code' => $data['code'],
            ]);
            if ($existing) {
                $this->modifierValues[$existing->getWhoId()] = $existing;
                $this->output->writeln("{$data['title']} already exists");

                return;
            }

            $this->output->writeln("Importing {$data['title']}");
            $value = new Cim11ModifierValue();
            $value->setWhoId($data['id']);
            $value->setCode($data['code']);
            $value->setName($data['title']);
            $value->setSynonyms(explode(';', $data['synonyms']));
            $value->importId = $this->importId;

            $this->em->persist($value);

            $this->modifierValues[$data['id']] = $value;
        });

        $this->em->flush();

        $this->output->writeln('All modifiers imported successfully...');
    }

    private function readCsv(string $fileName, string $separator, callable $callback, bool $convertToUtf8 = false): void
    {
        $row = 0;

        $tmpName = tempnam(sys_get_temp_dir(), 'csv');
        file_put_contents($tmpName, file_get_contents($fileName));

        $header = null;
        if (($handle = fopen($tmpName, 'r')) !== false) {
            if ($convertToUtf8) {
                stream_filter_append($handle, 'convert.iconv.ISO-8859-1/UTF-8');
            }

            $totalRows = -1; // Start at -1 to exclude the header from the count
            while (false !== fgetcsv($handle, 0, $separator)) {
                ++$totalRows;
            }

            // Rewind the file pointer to the beginning of the file for the second read
            rewind($handle);

            while (($data = fgetcsv($handle, separator: $separator)) !== false) {
                $data = array_map(fn ($item) => preg_replace("/^b'(.*)'$/", '$1', $item), $data);

                if (!$header) {
                    $header = $data;
                    continue;
                }
                ++$row;

                $length = count($header) - count($data);

                for ($i = 0; $i < $length; ++$i) {
                    $data[] = '';
                }

                $value = array_combine($header, $data);
                $callback($value, $row, $totalRows);
            }
            fclose($handle);
        }
    }

    private function buildCim10Cim11Database(): void
    {
        $file = "{$this->projectDir}/mapping-cim-11.csv";

        $this->readCsv($file, ',', function (array $data) {
            $this->cim11Mapping[$data['icd11Code']] = $data['icd10Code'];
        });
    }
}
