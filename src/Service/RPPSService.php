<?php

namespace App\Service;

use App\Entity\RPPS;
use App\Repository\RPPSRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpClient\HttpClient;

/**
 * Contains all useful methods to process files and import them into database.
 */
class RPPSService
{


    /**
     * @var EntityManagerInterface
     */
    protected $em;


    /**
     * @var string
     */
    protected $rppsUrl;

    /**
     * @var string
     */
    protected $cpsUrl;

    /**
     * @var FileProcessor
     */
    protected $fileProcessor;

    /**
     * RPPSService constructor.
     * @param string $cpsUrl
     * @param string $rppsUrl
     * @param FileProcessor $fileProcessor
     * @param EntityManagerInterface $em
     */
    public function __construct(string $cpsUrl,string $rppsUrl,FileProcessor $fileProcessor,EntityManagerInterface $em)
    {
        $this->rppsUrl = $rppsUrl;
        $this->cpsUrl = $cpsUrl;
        $this->fileProcessor = $fileProcessor;
        $this->em = $em;
    }


    /**
     * @param OutputInterface $output
     * @return bool
     * @throws \Exception
     */
    public function importRPPSData(OutputInterface $output) : bool
    {
        /** Handling RPPS File */
        $input_rpps_file = $this->fileProcessor->getFile($this->rppsUrl,"rpps",true);

        return $this->processRppsFile($output,$input_rpps_file);

    }


    /**
     * @param OutputInterface $output
     * @return bool
     * @throws \Exception
     */
    public function importCPSData(OutputInterface $output) : bool
    {
        /** Handling RPPS File */
        $file = $this->fileProcessor->getFile($this->cpsUrl,"cps",true);

        return $this->processCpsFile($output,$file);

    }


    /**
     * Parses a CSV file with ";" separator into a PHP array
     * and persistsAdnFlushes them into the database.
     *
     * @param OutputInterface $output
     * The output instance used to display message to the user.
     *
     * @param string $file
     * The path of the file to be processed.
     *
     * @param int $batchSize
     * The amount of data to pass before emptying doctrice cache
     *
     * @return integer
     * Returns 0 if the whole process worked.
     */
    protected function processRppsFile(OutputInterface $output,string $file,int $batchSize = 20): int
    {

        $lineCount = $this->fileProcessor->getLinesCount($file);

        // Showing when the rpps process is launched
        $start = new \DateTime();
        $output->writeln('<comment>Start : ' . $start->format('d-m-Y G:i:s') . ' | You have ' . $lineCount . ' lines to import from your RPPS file to your database ---</comment>');

        // Will go through file by iterating on each line to save memory
        if (($handle = fopen($file, "r")) !== FALSE) {

            /** @var RPPSRepository rppsRepository */
            $rppsRepository = $this->em->getRepository(RPPS::class);

            $row = 0;

            while (($data = fgetcsv($handle, 0, ";")) !== FALSE) {

                if ($row > 0) { //Exits header of csv file

                    $rpp = $rppsRepository->find($data[2]);

                    if (null === $rpp) {

                        $newRpps = new RPPS();
                        $newRpps->setIdRpps($data[2]);
                        $newRpps->setTitle($data[4]);
                        $newRpps->setLastName($data[5]);
                        $newRpps->setFirstName($data[6]);
                        $newRpps->setSpecialty($data[8]);
                        $newRpps->setAddress($data[24] . " " . $data[25] . " " . $data[27] . " " . $data[28] . " " . $data[29]);
                        $newRpps->setZipcode($data[31]);
                        $newRpps->setCity($data[30]);
                        $newRpps->setPhoneNumber(str_replace(' ', '', $data[36]));
                        $newRpps->setEmail($data[39]);
                        $newRpps->setFinessNumber($data[18]);

                        try {
                            $this->em->persist($newRpps);
                            $this->em->flush();
                        }catch (\Exception $exception) {

                            // Some rows have multiple entries
                            if(false !== strpos($exception->getMessage()," Duplicate entry")) {
                                continue;
                            }

                            throw $exception;

                        }
                    }
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
     * Parses a CSV file with "|" separator into a PHP array
     * and check existing datas in database, to match an additionnal data.
     *
     * @param OutputInterface $output
     * The output instance used to display message to the user.
     *
     * @param string $file
     * The path of the file to be processed.
     *
     * @param int $batchSize
     * The amount of data to pass before emptying doctrice cache
     *
     * @return integer
     * Returns 0 if the whole process worked.
     */
    protected function processCpsFile(OutputInterface $output,string $file,int $batchSize = 20): int
    {

        $lineCount = $this->fileProcessor->getLinesCount($file);

        // Showing when the cps process is launched
        $start = new \DateTime();
        $output->writeln('<comment>Start : ' . $start->format('d-m-Y G:i:s') . ' | You have ' . $lineCount . ' lines to go through on your CPS ---</comment>');

        // Will go through file by iterating on each line to save memory
        if (($handle = fopen($file, "r")) !== FALSE) {

            /** @var RPPSRepository rppsRepository */
            $rppsRepository = $this->em->getRepository(RPPS::class);

            $row = 0;

            while (($data = fgetcsv($handle, 1000, "|")) !== FALSE) {

                if ($row > 0) { //Exits header of csv file

                    //Checking if there is a match on the rpps number on both file
                    //if so, we set the CPS number to the matching line, then
                    //persistAndFlush
                    if ($existingRpps = $rppsRepository->find($data[1])) {
                        $existingRpps->setCpsNumber($data[11]);
                        $this->em->persist($existingRpps);
                        $this->em->flush();
                    }
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

            // Showing when the cps process is done
            $output->writeln('<comment>End of loading :  (Started at ' . $start->format('d-m-Y G:i:s') . ' / Ended at ' . $end->format('d-m-Y G:i:s') . ' | You have imported all needed datas from your CPS file to your database ---</comment>');

        }

        unlink($file);

        return true;
    }


}
