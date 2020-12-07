<?php

namespace App\Service;

use App\Entity\Drug;
use App\Repository\DrugRepository;
use App\Repository\RPPSRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpClient\HttpClient;

/**
 * Contains all useful methods to process files and import them into database.
 */
class DrugService extends ImporterService
{

    /**
     * @var string
     */
    protected $DRUGS_URL_CIS_BDPM;


    /**
     * @var string
     */
    protected $DRUGS_URL_CIS_CIP_BDPM;

    /**
     * @var string
     */
    protected $DRUGS_URL_CIS_CPD_BDPM;

    /**
     * @var string
     */
    protected $DRUGS_URL_CIS_GENER_BDPM;

    /**
     * @var string
     */
    protected $DRUGS_URL_CIS_InfoImportantes;



    public function __construct(string $DRUGS_URL_CIS_InfoImportantes,string $DRUGS_URL_CIS_GENER_BDPM,string $DRUGS_URL_CIS_CPD_BDPM,string $DRUGS_URL_CIS_BDPM,string $DRUGS_URL_CIS_CIP_BDPM,FileProcessor $fileProcessor,EntityManagerInterface $em)
    {
        parent::__construct(Drug::class,$fileProcessor,$em);

        $this->DRUGS_URL_CIS_BDPM = $DRUGS_URL_CIS_BDPM;
        $this->DRUGS_URL_CIS_CIP_BDPM = $DRUGS_URL_CIS_CIP_BDPM;
        $this->DRUGS_URL_CIS_CPD_BDPM = $DRUGS_URL_CIS_CPD_BDPM;
        $this->DRUGS_URL_CIS_GENER_BDPM = $DRUGS_URL_CIS_GENER_BDPM;
        $this->DRUGS_URL_CIS_InfoImportantes = $DRUGS_URL_CIS_InfoImportantes;

    }



    /**
     * @param array $data
     * @param string $type
     * @return Drug|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    protected function processData(array $data,string $type) : ?Drug
    {
        switch ($type)
        {
            case "DRUGS_URL_CIS_BDPM" :
                return $this->processCisBDPM($data);
            case "DRUGS_URL_CIS_CIP_BDPM" :
                return $this->processCipBDPM($data);
            case "DRUGS_URL_CIS_CPD_BDPM" :
                return $this->processCdpBDPM($data);
            case "DRUGS_URL_CIS_GENER_BDPM" :
                return $this->processGenerBDPM($data);
            case "DRUGS_URL_CIS_InfoImportantes" :
                return $this->processInfoImportantes($data);
        }

        throw new \Exception("Type $type is not supported yet");

    }


    /**
     * @param array $data
     *
     * @return Drug|null
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    protected function processCisBDPM(array $data): ?Drug
    {

        $drug = $this->repository->find($data[0]);

        if (null === $drug) {
            $drug = new Drug();
            $drug->setCisId($data[0]);
        }

        $drug = new Drug();
        $drug->setName($data[1]);
        $drug->setPharmaceuticalForm($data[2]);

        if($data[3]) {
            $drug->setAdministrationForms(explode(";",$data[3]));
        } else {
            $drug->setAdministrationForms(null);
        }
        $drug->setOwner($data[10]);

        return $drug;

    }



    /**
     * @param array $data
     *
     * @return Drug|null
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    protected function processGenerBDPM(array $data): ?Drug
    {


        $drug = $this->repository->find($data[2]);

        if (null === $drug) {
            return null;
        }

        $drug->setGenericGroupId($data[0]);
        $drug->setGenericType($data[1]);
        $drug->setGenericLabel($data[3]);

        return $drug;

    }

    /**
     * @param array $data
     *
     * @return Drug|null
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    protected function processCipBDPM(array $data): ?Drug
    {

        /** @var Drug $drug */
        $drug = $this->repository->find($data[0]);

        if (null === $drug) {
            return null;
        }

        $drug->setPresentationLabel($data[2]);

        if($data[8]) {
            $drug->setReimbursementRates(explode(";", $data[8]));
        } else {
            $drug->setReimbursementRates(null);
        }

        if($data[9]) {
            $data[9] = floatval(str_replace(",","",$data[9]));
            $data[9] = $data[9]/100;
            $drug->setPrice($data[9]);
        } else {
            $drug->setPrice(null);
        }

        return $drug;

    }



    /**
     * @param array $data
     *
     * @return Drug|null
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    protected function processCdpBDPM(array $data): ?Drug
    {

        $drug = $this->repository->find($data[0]);

        if (null === $drug) {
            return null;
        }

        $drug->setPrescriptionConditions($data[1]);

        return $drug;

    }


    /**
     * @param array $data
     *
     * @return Drug|null
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    protected function processInfoImportantes(array $data): ?Drug
    {

        $drug = $this->repository->find($data[0]);

        if (null === $drug) {
            return null;
        }

        $drug->setSecurityText($data[3]);

        return $drug;

    }


}
