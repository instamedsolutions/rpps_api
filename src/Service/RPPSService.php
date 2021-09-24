<?php

namespace App\Service;

use App\Entity\Drug;
use App\Entity\RPPS;
use App\Repository\DrugRepository;
use App\Repository\RPPSRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Entity;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Contains all useful methods to process files and import them into database.
 */
class RPPSService extends ImporterService
{


    /**
     * @var EntityManagerInterface
     */
    protected $em;


    /**
     * @var string
     */
    protected $cps;

    /**
     * @var string
     */
    protected $rpps;


    /**
     * RPPSService constructor.
     * @param string $cpsUrl
     * @param string $rppsUrl
     * @param FileProcessor $fileProcessor
     * @param EntityManagerInterface $em
     */
    public function __construct(string $cpsUrl,string $rppsUrl,FileProcessor $fileProcessor,EntityManagerInterface $em)
    {
        parent::__construct(RPPS::class,$fileProcessor,$em);

        $this->rpps = $rppsUrl;
        $this->cps = $cpsUrl;

    }



    /**
     * @param OutputInterface $output
     * @return bool
     * @throws \Exception
     */
    public function importFile(OutputInterface $output,string $type) : bool
    {
        /** Handling File File */
        $file = $this->fileProcessor->getFile($this->$type,$type,true);

        if($type === "rpps") {
            $options = array('delimiter' => ";", "utf8" => true, "headers" => true);
        } else if($type === "cps") {
            $options = array('delimiter' => "|", "utf8" => false, "headers" => true);
        } else {
            throw new \Exception("Type $type not working");
        }

        $process = $this->processFile($output,$file,$type,$options);

        unlink($file);

        return $process;
    }


    /**
     * @param array $data
     * @param string $type
     * @return RPPS|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    protected function processData(array $data,string $type) : ?RPPS
    {
        switch ($type)
        {
            case "cps" :
                return $this->processCPS($data);
            case "rpps" :
                return $this->processRPPS($data);
        }

        throw new \Exception("Type $type is not supported yet");

    }

    /**
     * @param array $data
     *
     * @return RPPS|null
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    protected function processCPS(array $data): ?RPPS
    {


        /** @var RPPS $rpps */
        $rpps = $this->repository->find($data[0]);

        if (null === $rpps) {
            return null;
        }

        $rpps->setCpsNumber($data[11]);

        return $rpps;
    }


    /**
     * @param array $data
     *
     * @return RPPS|null
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    protected function processRPPS(array $data): ?RPPS
    {

        /** @var RPPS|null $rpps */
        $rpps = $this->repository->find($data[1]);

        if (null === $rpps) {
            $rpps = new RPPS();
        }

        $rpps->setIdRpps($data[1]);
        $rpps->setTitle($data[4]);
        $rpps->setLastName($data[5]);
        $rpps->setFirstName($data[6]);
        $rpps->setSpecialty($data[8]);

        if($data[12] && in_array($data[13],["S","CEX"])) {
            $rpps->setSpecialty($data[12]);
        }

        $rpps->setAddress($data[24] . " " . $data[25] . " " . $data[27] . " " . $data[28] . " " . $data[29]);
        $rpps->setZipcode($data[31]);
        $rpps->setCity($data[30]);
        $rpps->setPhoneNumber(str_replace(' ', '', $data[36]));
        $rpps->setEmail($data[39]);
        $rpps->setFinessNumber($data[18]);

        return $rpps;
    }

}
