<?php

namespace App\Service;

use App\Entity\RPPS;
use App\Repository\RPPSRepository;
use Doctrine\ORM\EntityManagerInterface;
use http\Client;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Component\HttpClient\HttpClient;
use \ZipArchive;

/**
 * Contains all useful methods to process files and import them into database.
 */
class FileProcessor
{


    /**
     * @var EntityManagerInterface
     */
    protected $em;


    /**
     * @var string
     */
    protected $projectDir;


    /**
     * FileProcessor constructor.
     * @param string $projectDir
     * @param EntityManagerInterface $em
     */
    public function __construct(string $projectDir,EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->projectDir = $projectDir;
    }


    /**
     * Counts how much line there is in a file.
     *
     * @param string $file
     * The path of the file we want to process.
     *
     * @return integer
     * The number of lines in a file.
     */
    public function getLinesCount(string $file): int
    {
        $linecount = 0;

        // Will go through file by iterating on each line to save memory
        $handle = fopen($file, "r");
        while (!feof($handle)) {
            fgets($handle);
            $linecount++;
        }

        fclose($handle);

        return $linecount - 1;
    }

    /**
     * Downloads zip file from url, extracts files.
     *
     * @param string $url
     * The url from which we can recover the file
     *
     * @param string $name
     * The name of the file to store
     *
     * @param bool $isZip
     * If the file you're getting is a zip and needs to be unzipped
     *
     * @return string
     */
    public function getFile(string $url,$name = "file",$isZip = false) : string
    {

        $ext = $isZip ? "zip" : "txt";

        $filePath = $this->projectDir . "/var/{$name}.$ext";

        $fileHandler = fopen($filePath,"w+");

        $client = HttpClient::create(array(
            'timeout' => null,
            'verify_peer' => false,
            'verify_host' => false,
        ));


        $response = $client->request("GET",$url);
        foreach ($client->stream($response) as $chunk) {
            fwrite($fileHandler, $chunk->getContent());
        }

        fclose($fileHandler);

        if(!$isZip) {
            return $filePath;
        }

        $zip = new \ZipArchive();

        $zip->open($filePath);
        $zip->extractTo($this->projectDir."/var/$name");
        $fileName = $this->projectDir . "/var/$name/" . $zip->getNameIndex(0);
        $zip->close();

        // Delete zip
        unlink($filePath);

        return $fileName;

    }


    /**
     * @param OutputInterface $output
     * @param $file
     * @param $lineCount
     * @param $batchSize
     * @return int
     */
    public function updateRppsFile(OutputInterface $output, $file, $lineCount, $batchSize): int
    {
        /**
         * knonwing if the file has been modified :
         * True : Start the script
         * otherwise, block the script
         *
         */

        //Retrieves old timestamp from file
        //To check if RPPS file have been updated
        //  if (($handle = fopen($file_old_timestamp, "r")) !== FALSE) {

        //     /** @var RPPSRepository rppsRepository */
        //     $rppsRepository = $this->em->getRepository(RPPS::class);

        //     $row = 0;

        //     while (($data = fgetcsv($handle, 1000, "|")) !== FALSE) {

        //         $old_timestamp = date("F d Y H:i:s", ($data[0]));

        //         $row++;
        //     }
        // }

        // $new_timestamp = filemtime($file);

        // //defines whether or not we need to update the database
        // if(date("F d Y H:i:s.", $new_timestamp) > date("F d Y H:i:s.", $old_timestamp))
        // {
        //     $modified = true;
        //     $old_timestamp = $new_timestamp;
        // }
        // else
        // {
        //     $modified = false;

        // }

        // Showing when the rpps process is launched
        $start = new \DateTime();
        $output->writeln('<comment>Start : ' . $start->format('d-m-Y G:i:s') . ' | You have ' . $lineCount . ' lines to import from your RPPS file to your database ---</comment>');


        /** @var RPPSRepository rppsRepository */
        $rppsRepository = $this->em->getRepository(RPPS::class);

        $rppsDatas = $rppsRepository->findAll();

        //if($modified) // If the timestamp's file has been modified
        //{

        // Will go through file by iterating on each line to save memory
        if (($handle = fopen($file, "r")) !== FALSE) {

            $row = 0;

            while (($data = fgetcsv($handle, 0, ";")) !== FALSE) {

                if ($row > 0) { //Exits header of csv file

                    //Only persisting data if it's no a duplicate of previously created datas
                    if (!$rppsRepository->find($data[2])) {

                        $output->writeln("New data to insert into the database");
                        //$output->writeln("New PP ID : " . $data[2]);
                        //$output->writeln($old_timestamp);

                        //Creating an RPPS instance to set all datas
                        //as we're going through each line, then
                        //persistAndFlush
                        $newRpps = new RPPS();

                        $newRpps->setIdRpps($data[2]);
                        $newRpps->setTitle($data[4]);
                        $newRpps->setFirstName($data[5]);
                        $newRpps->setLastName($data[6]);
                        $newRpps->setSpecialty($data[8]);
                        $newRpps->setAddress($data[24] . " " . $data[25] . " " . $data[27] . " " . $data[28] . " " . $data[29]);
                        $newRpps->setZipcode($data[31]);
                        $newRpps->setCity($data[30]);
                        $newRpps->setPhoneNumber(str_replace(' ', '', $data[36]));
                        $newRpps->setEmail($data[39]);
                        $newRpps->setFinessNumber($data[18]);

                        $this->em->persist($newRpps);
                        $this->em->flush();
                    }
                    else if(!$rppsRepository->find($data[2])) {
                        $output->writeln("This data doesn't exist in the RPPS file : need to delete into the database");
                        //$output->writeln($data[2]);
                    }
                    else
                    {
                        $output->writeln("Data already exists");
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

            //Delete the file
            //unlink($file);

            // Showing when the rpps process is done
            $output->writeln('<comment>End of loading : (Started at ' . $start->format('d-m-Y G:i:s') . ' / Ended at ' . $end->format('d-m-Y G:i:s') . ' | You have imported all datas from your RPPS file to your database ---</comment>');
        }

        // }
        // else
        // {
        //     $output->writeln($new_timestamp);
        //     $output->writeln("The file has not been modified");
        // }

        return 0;
    }

}
