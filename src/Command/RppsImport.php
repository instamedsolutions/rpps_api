<?php

namespace App\Command;

use App\Service\FileProcessor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


/**
 * Command to import file in empty database.
 */
class RppsImport extends Command
{

    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:rpps:import';


    /**
     * @var EntityManagerInterface
     */
    private $em;


    /**
     * @var FileProcessor
     */
    private $fileProcessor;

    /**
     * @var string
     */
    protected $projectDir;

    /**
     * @var string
     */
    protected $rppsUrl;


    /**
     * @var string
     */
    protected $cpsUrl;

    /**
     * CPS Import constructor.
     * @param string $cpsUrl
     * @param string $rppsUrl
     * @param string $projectDir
     * @param EntityManagerInterface $entityManager
     * @param FileProcessor $fileProcessor
     */
    public function __construct(string $cpsUrl,string $rppsUrl,string $projectDir, EntityManagerInterface $entityManager, FileProcessor $fileProcessor)
    {

        parent::__construct(self::$defaultName);

        $this->rppsUrl = $rppsUrl;
        $this->cpsUrl = $cpsUrl;
        $this->projectDir = $projectDir;
        $this->em = $entityManager;
        $this->fileProcessor = $fileProcessor;
    }


    /**
     *
     */
    protected function configure()
    {
        $this->setDescription('Import RPPS File into databse')
            ->setHelp('This command will import a RPPS CSV file into your database.');
    }


    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            // Turning off doctrine default logs queries for saving memory
            $this->em->getConnection()->getConfiguration()->setSQLLogger(null);

            // Showing when the cps process is launched
            $start = new \DateTime();
            $output->writeln('<comment>' . $start->format('d-m-Y G:i:s') . ' Start processing :---</comment>');

            /** Handling RPPS File */
            $input_rpps_file = $this->fileProcessor->getFile($this->rppsUrl);

            $rpps = $this->fileProcessor->processRppsFile($output,$input_rpps_file);

            /** * Handling CPS File */
            $input_cps_file = $this->fileProcessor->getFile($this->cpsUrl);
            $cps = $this->fileProcessor->processCpsFile($output,$input_cps_file);

            //Checking failure
            if ($rpps !== true) {
                $output->writeln("RPPS Load Failed");
                return Command::FAILURE;
            }

            if($rpps !== true) {
                $output->writeln("CPS Load Failed");
                return Command::FAILURE;
            }

            return Command::SUCCESS;


        } catch(\Exception $e){

            error_log($e->getMessage());
            $output->writeln($e->getMessage());
            return Command::FAILURE;

        }
    }
}
