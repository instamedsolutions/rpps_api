<?php

namespace App\Command;

use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'app:cim11:export'
)]
class Cim11Export extends Command
{
    private OutputInterface $output;

    private array $scaleEntities = [];
    private array $headers = [];

    public function __construct(
        private readonly string $projectDir,
        private readonly HttpClientInterface $cim11Client,
    ) {
        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->output = $output;

        $data = $this->getDiseases();

        $header = [
            ...array_keys($data[0]),
            ...array_values(array_unique($this->headers)),
        ];
        $fp = fopen("$this->projectDir/var/cim-11.csv", 'w');
        fputcsv($fp, $header);

        foreach ($data as $row) {
            if (str_starts_with($row['code'], 'X')) {
                continue;
            }

            $fullRow = [];
            foreach ($header as $item) {
                $fullRow[] = $row[$item] ?? '';
            }
            fputcsv($fp, $fullRow);
        }

        fclose($fp);

        $output->writeln('Writting scale entities');

        $this->headers = [];
        $scaleEntities = [];

        foreach ($this->scaleEntities as $entity) {
            $scaleEntities[] = $this->getDiseases($entity, true);
        }

        $header = [
            ...array_keys($scaleEntities[0]),
            ...array_values(array_unique($this->headers)),
        ];
        $fp = fopen("$this->projectDir/var/modifiers.csv", 'w');
        fputcsv($fp, $header);

        foreach ($scaleEntities as $row) {
            $fullRow = [];
            foreach ($header as $item) {
                $fullRow[] = $row[$item] ?? '';
            }
            fputcsv($fp, $fullRow);
        }

        return self::SUCCESS;
    }

    public function getDiseases(string $entity = '', bool $isScaleEntity = false): array
    {
        $responses = [];

        $data = $this->getDiseaseData($entity ? "/$entity" : '');

        if (isset($data['child']) && !$isScaleEntity) {
            foreach ($data['child'] as $children) {
                $children = $this->cleanId($children);
                $response = $this->getDiseases($children, $isScaleEntity);

                if (isset($response['code'])) {
                    $responses[] = $response;
                } else {
                    $responses = [
                        ...$responses,
                        ...$response,
                    ];
                }
            }
        } else {
            $title = $this->sanitize($data['title']['@value']);

            $this->output->writeln("Handling $title");

            $synonyms = $data['indexTerm'] ?? [];

            try {
                $entityId = explode('/', $entity)[0];
                $entityData = $this->cim11Client->request('GET', "/icd/entity/$entityId", [
                    'headers' => [
                        'API-Version' => 'v2',
                        'Accept-Language' => 'fr',
                    ],
                ]);

                $entityData = $entityData->toArray();

                $synonyms = $entityData['synonym'] ?? [];
            } catch (Exception $exception) {
                $this->output->writeln("Error fetching entity $entityId : {$exception->getMessage()}");
            }

            $datum = [
                'id' => $entity,
                'parent_id' => isset($data['parent'][0]) ? $this->cleanId($data['parent'][0]) : '',
                'code' => $data['code'] ?: $data['codeRange'] ?? '',
                'title' => $title,
                'type' => $data['classKind'],
                'definition' => isset($data['definition']['@value']) ? $this->sanitize($data['definition']['@value']) : '',
                'fullName' => isset($data['fullySpecifiedName']['@value']) ? $this->sanitize($data['fullySpecifiedName']['@value']) : $data['fullySpecifiedName'] ?? '',
                'categories' => implode(';', array_map(fn ($value) => $value['label']['@value'], $data['inclusion'] ?? [])),
                'synonyms' => implode(';', array_map(fn ($value) => $this->sanitize($value['label']['@value']), $synonyms)),
            ];

            if (isset($data['postcoordinationScale'])) {
                foreach ($data['postcoordinationScale'] as $scaleEntity) {
                    if (!isset($scaleEntity['scaleEntity'])) {
                        continue;
                    }

                    $name = $this->parseKey($scaleEntity['axisName']);

                    if (!in_array($name, $this->headers)) {
                        $this->headers[] = $name;
                    }

                    $ids = array_map(function ($item) {
                        $item = $this->cleanId($item);

                        if (!in_array($item, $this->scaleEntities)) {
                            $this->scaleEntities[] = $item;
                        }

                        return $item;
                    }, $scaleEntity['scaleEntity']);

                    $datum[$name] = json_encode(['allowMultipleValues' => $scaleEntity['allowMultipleValues'], 'ids' => $ids]);
                }
            }

            return $datum;
        }

        return $responses;
    }

    private function getDiseaseData(string $entity): array
    {
        $url = '/icd/release/11/2023-01/mms' . $entity;

        $d = $this->cim11Client->request('GET', $url, [
            'headers' => [
                'API-Version' => 'v2',
                'Accept-Language' => 'fr',
            ],
        ]);

        return $d->toArray();
    }

    private function sanitize(string $string): string
    {
        $string = str_replace(', sans précision', '', $string);

        return trim(str_replace('[No translation available]', '', $string));
    }

    private function parseKey(string $value): string
    {
        return str_replace('http://id.who.int/icd/schema/', '', $value);
    }

    private function cleanId(string $value): string
    {
        $str = str_replace('http://id.who.int/icd/release/11/2023-01/mms', '', $value);

        return str_starts_with($str, '/') ? substr($str, 1) : $str;
    }
}
