<?php

namespace App\Service;

use App\Entity\CCAM;
use App\Entity\CCAMGroup;
use App\Entity\Entity;
use Doctrine\ORM\EntityManagerInterface;


/**
 * Contains all useful methods to process files and import them into database.
 */
class CCAMService extends FileParserService
{

    /**
     * @var string
     */
    protected $projectDir;


    /**
     * @var CCAMGroup|null;
     */
    protected $currentGroup;


    /**
     * @var CCAMGroup|null;
     */
    protected $currentCategory;


    /**
     * @var CCAM
     */
    protected $currentCCAM;


    protected $groupRepository;

    public function __construct(string $projectDir,FileProcessor $fileProcessor,EntityManagerInterface $em)
    {
        $this->projectDir = $projectDir;
        parent::__construct(CCAM::class,$fileProcessor,$em);
        $this->groupRepository = $this->em->getRepository(CCAMGroup::class);
        $this->setClearalbe(false);
    }


    /**
     * @return bool
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function parse() : bool
    {
        return $this->processFile($this->output,$this->getFile(),"default",['delimiter' => ",","utf8" => true,"first_line" => 2,"headers" => true]);
    }


    /**
     * @param array $data
     * @param string $type
     * @return CCAM|CCAMGroup
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    protected function processData(array $data,string $type) : ?Entity
    {

        if($this->empty($data)) {
            return null;
        }

        if($this->isGroup($data)) {
            $group = $this->groupRepository->find($data[0]);

            if(null === $group) {
                $group = new CCAMGroup();
            }
            $group->setName($data[2]);
            $group->setCode($data[0]);
            if(!$this->isCategory($data)) {
                $group->setParent($this->currentCategory);
            } else {
                $this->currentCategory = $group;
            }

            $this->em->persist($group);
            $this->currentGroup = $group;
            $this->currentCCAM = null;
            return $group;
        }

        if($this->isCCAM($data)) {
            $ccam = $this->repository->find($data[0]);
            if(null === $ccam) {
                $ccam = new CCAM();
            }
            $ccam->setCode($data[0]);
            $ccam->setName($data[2]);
            $ccam->setRegroupementCode($data[10]);
            $ccam->setRate1($this->parseRate($data[5]));
            $ccam->setRate2($this->parseRate($data[6]));
            $this->currentCCAM = $ccam;
            $this->currentCCAM->setGroup($this->currentGroup);
            $this->currentCCAM->setCategory($this->currentCategory);
            $this->em->persist($ccam);
            return $ccam;
        }

        if($this->isModifier($data)) {
            $this->currentCCAM->setModifiers($this->parseModifier($data));
        }

        if($this->currentCCAM) {
            $this->currentCCAM->addDescriptionLine($data[2]);
            $this->currentCCAM->setGroup($this->currentGroup);
            $this->currentCCAM->setCategory($this->currentCategory);
            $this->em->persist($this->currentCCAM);
        } else {
            $this->currentGroup->addDescriptionLine($data[2]);
        }


        return $this->currentCCAM;
    }



    protected function isModifier(array $data) : bool
    {
        // [A, F, J, K, T, P, S, U, 7]
        return preg_match("#^\[[A-Z0-9+, ]+\]$#",$data[0]) === 1;
    }

    /**
     * @param array $data
     * @return array
     */
    protected function parseModifier(array $data) : array
    {
        $m = $data[0];
        $m = str_replace([" ","[","]"],"",$m);

        return explode(",",$m);
    }

    /**
     * @param array $data
     * @return bool
     */
    protected function isGroup(array $data) : bool
    {
        // 01.30.44
        return preg_match("#^[0-9]{1,2}([0-9.]+)?$#",$data[0]) === 1;
    }


    /**
     * @param string $rate
     * @return float|null
     */
    protected function parseRate(string $rate) : ?float
    {
        if($rate === "Non pris en charge") {
            return null;
        }

        return (float)$rate;

    }


    /**
     *
     * This is a main category
     *
     * @param array $data
     * @return bool
     */
    protected function isCategory(array $data) : bool
    {
        return preg_match("#^[0-9]{1,2}$#",$data[0]) === 1;
    }

    /**
     * @param array $data
     * @return bool
     */
    protected function isCCAM(array $data) : bool
    {
        return preg_match("#^[A-Z]{4}[0-9]{3}$#",$data[0]) === 1;

    }


    /**
     * @param array $data
     * @return bool
     */
    protected function empty(array $data) : bool
    {
        return false === (bool)(trim(join("",$data)));
    }


    /**
     * @return string
     */
    protected function getFile() : string
    {
        return  "$this->projectDir/data/ccam.csv";
    }
}
