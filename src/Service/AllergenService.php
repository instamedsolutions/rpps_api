<?php

namespace App\Service;

use App\Entity\Allergen;
use App\Entity\Drug;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Contains all useful methods to process files and import them into database.
 */
class AllergenService extends FileParserService
{

    /**
     * @var string
     */
    protected $projectDir;


    public function __construct(string $projectDir,FileProcessor $fileProcessor,EntityManagerInterface $em)
    {
        $this->projectDir = $projectDir;
        parent::__construct(Allergen::class,$fileProcessor,$em);
    }


    /**
     * @return bool
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function parse() : bool
    {
        return $this->processFile($this->output,$this->getFile(),"default",['delimiter' => ",","utf8" => true,"headers" => true]);
    }


    /**
     * @param array $data
     * @param string $type
     * @return Drug|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    protected function processData(array $data,string $type) : ?Allergen
    {
        $allergen = $this->repository->find($data[0]);

        if (null === $allergen) {
            $allergen = new Allergen();
            $allergen->setCode($data[0]);
        }

        $allergen->setName($data[1]);
        $allergen->setGroup($data[2]);

        $this->em->persist($allergen);
        $this->em->flush();


        return $allergen;
    }



    /**
     * @return string
     */
    protected function getFile() : string
    {
        return  "$this->projectDir/data/allergens.csv";
    }
}
