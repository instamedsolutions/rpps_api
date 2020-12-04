<?php

namespace App\DataFixtures;

use App\Entity\RPPS;
use App\Service\FileProcessor;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

class RppsFixtures extends Fixture
{
    public function __construct(FileProcessor $fileProcessor)
    {
        $this->fileProcessor = $fileProcessor;
    }

    public function load(ObjectManager $manager)
    {
        $output = new ConsoleOutput();

        $fileName = __DIR__ . '/docs/line-count.csv' ;

        $this->fileProcessor->processRppsFile($output, $manager, $fileName, 5, 1);
        // Will go through file by iterating on each line to save memory 
        /*if (($handle = fopen($fileName, "r")) !== FALSE) {

           
            $rppsRepository = $manager->getRepository(RPPS::class);

            $row = 0;

            while (($data = fgetcsv($handle, 0, ";")) !== FALSE) {

                if ($row > 0) { //Exits header of csv file
                    if (!$rppsRepository->findOneBy(["id_rpps" => $data[1]])) { //Only persisting data if it's no a duplicate of previously created datas
                        
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

                        $manager->persist($newRpps);
                        $manager->flush();
                    }
                }

                $row++;
            }

            fclose($handle);
        }*/

    }
}
