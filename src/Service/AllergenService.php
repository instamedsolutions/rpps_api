<?php

namespace App\Service;

use Doctrine\ORM\NonUniqueResultException;
use App\Entity\Allergen;
use App\Entity\Drug;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Contains all useful methods to process files and import them into database.
 */
class AllergenService extends FileParserService
{

    public function __construct(protected string $projectDir, FileProcessor $fileProcessor, EntityManagerInterface $em)
    {
        parent::__construct(Allergen::class, $fileProcessor, $em);
    }


    public function parse(): bool
    {
        return $this->processFile(
            $this->output,
            $this->getFile(),
            "default",
            ['delimiter' => ",", "utf8" => true, "headers" => true]
        );
    }


    protected function processData(array $data, string $type): ?Allergen
    {
        $allergen = $this->repository->find($data[0]);

        if (null === $allergen) {
            $allergen = new Allergen();
            $allergen->setCode($data[0]);
        }

        $allergen->setName($data[1]);
        $allergen->setGroup($data[2]);

        $allergen->importId = $this->getImportId();

        $this->em->persist($allergen);
        $this->em->flush();


        return $allergen;
    }


    protected function getFile(): string
    {
        return "$this->projectDir/data/allergens.csv";
    }
}
