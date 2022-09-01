<?php

namespace App\Service;

use Doctrine\ORM\NonUniqueResultException;
use Exception;
use App\Entity\Disease;
use App\Entity\DiseaseGroup;
use App\Entity\Drug;
use App\Entity\Thing;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Contains all useful methods to process files and import them into database.
 */
class DiseaseService extends ImporterService
{

    protected array $diseases = [];

    protected array $groups = [];


    final const CODES = "codes";

    final const CHAPITRES = "chapitres";

    final const GROUPES = "groupes";

    public function __construct(
        protected string $cim10Url,
        FileProcessor $fileProcessor,
        EntityManagerInterface $em
    ) {
        parent::__construct(DiseaseGroup::class, $fileProcessor, $em);
        $this->setClearalbe(false);
    }


    /**
     * @throws NonUniqueResultException
     */
    public function importFiles(OutputInterface $output, string $type): bool
    {
        /** Handling File File */
        $files = $this->fileProcessor->getFiles($this->$type, $type, true);

        $types = [];

        $process = true;
        foreach ($files as $file) {
            $type = $this->getTypeFromFileName($file);


            if ($type) {
                $types[$type] = $file;
            }
        }


        $options = ["delimiter" => ";", "utf8" => true, "headers" => false];

        // Import in a specific order
        $first = $this->processFile($output, $types[self::CHAPITRES], self::CHAPITRES, $options);
        $second = $this->processFile($output, $types[self::GROUPES], self::GROUPES, $options);
        $third = $this->processFile($output, $types[self::CODES], self::CODES, $options);

        foreach ($files as $file) {
            unlink($file);
        }

        return $first && $second && $third;
    }


    /**
     * @throws NonUniqueResultException
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
        /** @var DiseaseGroup $group */
        $group = $this->repository->find($data[0]);

        if (null === $group) {
            $group = new DiseaseGroup();
            $group->setCim($data[0]);
        }

        $group->setName($data[1]);

        $this->groups[$group->getCim()] = $group;

        return $group;
    }


    protected function parseGroups(array $data): ?DiseaseGroup
    {
        $cim10 = "{$data[0]}-{$data[1]}";

        /** @var DiseaseGroup $group */
        $group = $this->repository->find($cim10);

        if (null === $group) {
            $group = new DiseaseGroup();
            $group->setCim($cim10);
        }

        $group->setParent($this->groups[$data[2]]);
        $group->setName($data[3]);

        $this->groups[$data[0]] = $group;

        return $group;
    }


    /**
     * @return DiseaseGroup|null
     */
    protected function parseCodes(array $data): ?Disease
    {
        $this->init(Disease::class);

        /** @var Disease $disease */
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

        $parentId = explode(".", (string)$data[6])[0];
        if ($parentId !== $disease->getCim()) {
            $disease->setParent($this->diseases[$parentId]);
        }

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

        return (string)$int;
    }

}
