<?php

namespace App\Service;

use Doctrine\ORM\NonUniqueResultException;
use Exception;
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

    public function __construct(
        protected string $DRUGS_URL_CIS_InfoImportantes,
        protected string $DRUGS_URL_CIS_GENER_BDPM,
        protected string $DRUGS_URL_CIS_CPD_BDPM,
        protected string $DRUGS_URL_CIS_BDPM,
        protected string $DRUGS_URL_CIS_CIP_BDPM,
        FileProcessor $fileProcessor,
        EntityManagerInterface $em
    ) {
        parent::__construct(Drug::class, $fileProcessor, $em);
    }


    protected function processData(array $data, string $type): ?Drug
    {
        return match ($type) {
            "DRUGS_URL_CIS_BDPM" => $this->processCisBDPM($data),
            "DRUGS_URL_CIS_CIP_BDPM" => $this->processCipBDPM($data),
            "DRUGS_URL_CIS_CPD_BDPM" => $this->processCdpBDPM($data),
            "DRUGS_URL_CIS_GENER_BDPM" => $this->processGenerBDPM($data),
            "DRUGS_URL_CIS_InfoImportantes" => $this->processInfoImportantes($data),
            default => throw new Exception("Type $type is not supported yet"),
        };
    }


    protected function processCisBDPM(array $data): ?Drug
    {
        $drug = $this->repository->find($data[0]);

        if (null === $drug) {
            $drug = new Drug();
            $drug->setCisId($data[0]);
        }

        $drug->setName($data[1]);
        $drug->setPharmaceuticalForm($data[2]);

        if ($data[3]) {
            $drug->setAdministrationForms(explode(";", (string)$data[3]));
        } else {
            $drug->setAdministrationForms(null);
        }
        $drug->setOwner($data[10]);
        $drug->importId = $this->getImportId();

        return $drug;
    }


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


    protected function processCipBDPM(array $data): ?Drug
    {
        /** @var Drug $drug */
        $drug = $this->repository->find($data[0]);

        if (null === $drug) {
            return null;
        }

        $drug->setPresentationLabel($data[2]);

        if ($data[8]) {
            $drug->setReimbursementRates(explode(";", (string)$data[8]));
        } else {
            $drug->setReimbursementRates(null);
        }

        if ($data[9]) {
            $data[9] = floatval(str_replace(",", "", (string)$data[9]));
            $data[9] /= 100;
            $drug->setPrice($data[9]);
        } else {
            $drug->setPrice(null);
        }

        $drug->importId = $this->getImportId();

        return $drug;
    }


    /**
     *
     *
     * @throws NonUniqueResultException
     */
    protected function processCdpBDPM(array $data): ?Drug
    {
        $drug = $this->repository->find($data[0]);

        if (null === $drug) {
            return null;
        }

        $drug->setPrescriptionConditions($data[1]);
        $drug->importId = $this->getImportId();

        return $drug;
    }


    /**
     *
     *
     * @throws NonUniqueResultException
     */
    protected function processInfoImportantes(array $data): ?Drug
    {
        $drug = $this->repository->find($data[0]);

        if (null === $drug) {
            return null;
        }

        $drug->setSecurityText($data[3]);
        $drug->importId = $this->getImportId();

        return $drug;
    }


}
