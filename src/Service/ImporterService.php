<?php

namespace App\Service;

use App\Entity\Drug;
use App\Repository\DrugRepository;
use App\Repository\RPPSRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\Entity;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpClient\HttpClient;

/**
 * Contains all useful methods to process files and import them into database.
 */
abstract class ImporterService
{


    /**
     * @var EntityManagerInterface
     */
    protected $em;


    /**
     * @var FileProcessor
     */
    protected $fileProcessor;


    /**
     * @var EntityRepository
     */
    protected $repository;

    /**
     * @var string
     */
    protected $entity;

    /**
     * ImporterService constructor.
     * @param string $entity
     * @param FileProcessor $fileProcessor
     * @param EntityManagerInterface $em
     */
    public function __construct(string $entity,FileProcessor $fileProcessor,EntityManagerInterface $em)
    {

        $this->fileProcessor = $fileProcessor;
        $this->em = $em;
        $this->repository = $this->em->getRepository($entity);
        $this->entity = $entity;
    }


    /**
     * @param OutputInterface $output
     * @param string $type
     * @param false $isZip
     * @return bool
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function importFile(OutputInterface $output,string $type,$isZip = false) : bool
    {
        /** Handling File File */
        $file = $this->fileProcessor->getFile($this->$type,$type,$isZip);

        $process = $this->processFile($output,$file,$type);

        unlink($file);

        return $process;
    }


    /**
     * @param OutputInterface $output
     * @param string $file
     * @param string $type
     * @param array $options
     * @return bool
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    protected function processFile(OutputInterface $output,string $file,string $type,array $options = array('delimiter' => "\t","utf8" => false,"headers" => false))
    {

        $batchSize = 20;

        $lineCount = $this->fileProcessor->getLinesCount($file);

        // Showing when the drugs process is launched
        $start = new \DateTime();
        $output->writeln('<comment>Start : ' . $start->format('d-m-Y G:i:s') . ' | You have ' . $lineCount . ' lines to import from your ' . $type . ' file to your database ---</comment>');

        // Will go through file by iterating on each line to save memory
        if (($handle = fopen($file, "r")) !== FALSE) {

            $row = 0;

            while (($data = fgetcsv($handle, 0, $options['delimiter'])) !== FALSE) {

                if($options['headers'] && 0 === $row) {
                    $row++;
                    continue;
                }

                if(!$options['utf8']) {
                    $data = array_map(function ($d) {
                        return utf8_encode($d);
                    }, $data);
                }

                $entity = $this->processData($data,$type);

                if($entity instanceof $this->entity) {
                    $this->em->persist($entity);
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
     * @return Entity|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    abstract protected function processData(array $data,string $type);


}
