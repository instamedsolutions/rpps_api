<?php

namespace App\Service;

use App\Entity\Disease;
use App\Entity\DiseaseGroup;
use App\Entity\Thing;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Contains all useful methods to process files and import them into database.
 */
class DiseaseService extends ImporterService
{
    protected array $diseases = [];

    protected array $groups = [];

    private const PARSING_OPTIONS = ['delimiter' => ';', 'utf8' => true, 'headers' => false];

    final public const CODES = 'codes';

    final public const CHAPITRES = 'chapitres';

    final public const GROUPES = 'groupes';

    public function __construct(
        protected string $cim10,
        FileProcessor $fileProcessor,
        EntityManagerInterface $em,
    ) {
        parent::__construct(DiseaseGroup::class, $fileProcessor, $em);
        $this->setClearable(false);
    }

    public function importFiles(OutputInterface $output, string $type): bool
    {
        /** Handling File */
        $files = $this->fileProcessor->getFiles($this->$type, $type, true);

        $types = [];

        $process = true;
        foreach ($files as $file) {
            $type = $this->getTypeFromFileName($file);

            if ($type) {
                $types[$type] = $file;
            }
        }

        // Import in a specific order
        $first = $this->processFile($output, $types[self::CHAPITRES], self::CHAPITRES, self::PARSING_OPTIONS);
        $second = $this->processFile($output, $types[self::GROUPES], self::GROUPES, self::PARSING_OPTIONS);
        $third = $this->processFile($output, $types[self::CODES], self::CODES, self::PARSING_OPTIONS);

        foreach ($files as $file) {
            unlink($file);
        }

        return $first && $second && $third;
    }

    /**
     * @throws Exception
     */
    protected function processData(array $data, string $type): ?Thing
    {
        return match ($type) {
            self::CHAPITRES => $this->parseChapters($data),
            self::GROUPES => $this->parseGroups($data),
            self::CODES => $this->parseCodes($data),
            default => throw new Exception("Type $type is not supported yet"),
        };
    }

    protected function parseChapters(array $data): ?DiseaseGroup
    {
        /** @var DiseaseGroup|null $group */
        $group = $this->repository->find($data[0]);

        if (null === $group) {
            $group = new DiseaseGroup();
            $group->setCim($data[0]);
        }

        $group->setName($data[1]);
        $group->importId = $this->getImportId();

        $this->groups[$group->getCim()] = $group;

        return $group;
    }

    protected function parseGroups(array $data): ?DiseaseGroup
    {
        $cim10 = "{$data[0]}-{$data[1]}";

        /** @var DiseaseGroup|null $group */
        $group = $this->repository->find($cim10);

        if (null === $group) {
            $group = new DiseaseGroup();
            $group->setCim($cim10);
        }

        $group->setParent($this->groups[$data[2]]);
        $group->setName($data[3]);
        $group->importId = $this->getImportId();

        $this->groups[$data[0]] = $group;

        return $group;
    }

    protected function parseCodes(array $data): ?Disease
    {
        $this->init(Disease::class);

        /** @var Disease|null $disease */
        $disease = $this->repository->find($data[7]);

        if (null === $disease) {
            $disease = new Disease();
            $disease->setCim($data[7]);
        }

        $disease->setHierarchyLevel($data[0]);

        $disease->setName($data[8]);
        $disease->setSex($data[12]);
        $disease->setLowerAgeLimit($data[14]);
        $disease->setUpperAgeLimit($data[15]);

        $disease->setGroup($this->groups[$data[4]]);
        $disease->setCategory($this->groups[self::transformToDoubleDigit($data[3])]);

        $parentId = explode('.', (string) $data[6])[0];
        if ($parentId !== $disease->getCim()) {
            $disease->setParent($this->diseases[$parentId]);
        }
        $disease->importId = $this->getImportId();

        $this->diseases[$disease->getCim()] = $disease;

        return $disease;
    }

    protected function getTypeFromFileName(string $fileName): ?string
    {
        if (strpos($fileName, self::CHAPITRES)) {
            return self::CHAPITRES;
        }

        if (strpos($fileName, self::CODES)) {
            return self::CODES;
        }

        if (strpos($fileName, self::GROUPES)) {
            return self::GROUPES;
        }

        return null;
    }

    public static function transformToDoubleDigit(int $int): string
    {
        if ($int < 10) {
            return "0{$int}";
        }

        return (string) $int;
    }
}
