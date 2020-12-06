<?php

namespace App\Service;

use App\Entity\Drug;
use App\Entity\RPPS;
use App\Repository\DrugRepository;
use App\Repository\RPPSRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpClient\HttpClient;

/**
 * Contains all useful methods to process files and import them into database.
 */
class DrugService
{


    /**
     * @var EntityManagerInterface
     */
    protected $em;


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

    /**
     * @var string
     */
    protected $cpsUrl;

    /**
     * @var FileProcessor
     */
    protected $fileProcessor;


    /**
     * @var DrugRepository
     */
    protected $repository;


    public function __construct(string $DRUGS_URL_CIS_InfoImportantes,string $DRUGS_URL_CIS_GENER_BDPM,string $DRUGS_URL_CIS_CPD_BDPM,string $DRUGS_URL_CIS_BDPM,string $DRUGS_URL_CIS_CIP_BDPM,FileProcessor $fileProcessor,EntityManagerInterface $em)
    {
        $this->DRUGS_URL_CIS_BDPM = $DRUGS_URL_CIS_BDPM;
        $this->DRUGS_URL_CIS_CIP_BDPM = $DRUGS_URL_CIS_CIP_BDPM;
        $this->DRUGS_URL_CIS_CPD_BDPM = $DRUGS_URL_CIS_CPD_BDPM;
        $this->DRUGS_URL_CIS_GENER_BDPM = $DRUGS_URL_CIS_GENER_BDPM;
        $this->DRUGS_URL_CIS_InfoImportantes = $DRUGS_URL_CIS_InfoImportantes;

        $this->fileProcessor = $fileProcessor;
        $this->em = $em;
        $this->repository = $this->em->getRepository(Drug::class);
    }


    /**
     * @param OutputInterface $output
     * @return bool
     * @throws \Exception
     */
    public function importFile(OutputInterface $output,string $type) : bool
    {
        /** Handling File File */
        $file = $this->fileProcessor->getFile($this->$type,$type);

        $process = $this->processFile($output,$file,$type);

        unlink($file);

        return $process;
    }


    /**
     * @param OutputInterface $output
     * @param string $file
     * @param string $type
     * @param int $batchSize
     * @return bool
     * @throws \Exception
     */
    protected function processFile(OutputInterface $output,string $file,string $type,int $batchSize = 20)
    {
        $lineCount = $this->fileProcessor->getLinesCount($file);

        // Showing when the drugs process is launched
        $start = new \DateTime();
        $output->writeln('<comment>Start : ' . $start->format('d-m-Y G:i:s') . ' | You have ' . $lineCount . ' lines to import from your ' . $type . ' file to your database ---</comment>');

        // Will go through file by iterating on each line to save memory
        if (($handle = fopen($file, "r")) !== FALSE) {

            /** @var DrugRepository $repo */
            $repo = $this->em->getRepository(Drug::class);

            $row = 0;

            while (($data = fgetcsv($handle, 0, "\t")) !== FALSE) {

                $data = array_map(function ($d) {
                    return utf8_encode($d);
                },$data);

                $drug = $this->processData($data,$type);

                if($drug instanceof Drug) {
                    $this->em->persist($drug);
                    $this->em->flush();
                }

                //Used to save some memory out of Doctrine every 20 lines
                if (($row % $batchSize) === 0) {
                    // Detaches all objects from Doctrine for memory save
                    $this->em->clear();

                    // Showing progression of the process
                    $end = new \DateTime();
                    $output->writeln($row . ' of lines imported out of ' . $lineCount . ' | ' . $end->format('d-m-Y G:i:s'));
                }

                $row++;
            }

            fclose($handle);

            // Showing when the rpps process is done
            $output->writeln('<comment>End of loading : (Started at ' . $start->format('d-m-Y G:i:s') . ' / Ended at ' . $end->format('d-m-Y G:i:s') . ' | You have imported all datas from your RPPS file to your database ---</comment>');

        }

        return true;
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
