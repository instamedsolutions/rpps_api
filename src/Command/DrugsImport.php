<?php

namespace App\Command;

use App\Service\DrugService;
use App\Service\FileProcessor;
use App\Service\RPPSService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


/**
 * Command to import file in empty database.
 */
class DrugsImport extends Command
{

    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:drugs:import';


    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var string
     */
    protected $projectDir;

    /**
     * @var DrugService
     */
    protected $drugService;


    /**
     * RppsImport constructor.
     * @param RPPSService $RPPSService
     * @param string $projectDir
     * @param EntityManagerInterface $entityManager
     * @param FileProcessor $fileProcessor
     */
    public function __construct(DrugService $drugService, EntityManagerInterface $entityManager)
    {

        parent::__construct(self::$defaultName);

        $this->drugService = $drugService;
        $this->em = $entityManager;
    }


    /**
     *
     */
    protected function configure()
    {
        $this->setDescription('Import Drugs File into database')
            ->setHelp('This command will import all drugs data.');
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

            $this->drugService->importFile($output,"DRUGS_URL_CIS_BDPM");
            $this->drugService->importFile($output,"DRUGS_URL_CIS_CIP_BDPM");
            $this->drugService->importFile($output,"DRUGS_URL_CIS_CPD_BDPM");
            $this->drugService->importFile($output,"DRUGS_URL_CIS_GENER_BDPM");
            $this->drugService->importFile($output,"DRUGS_URL_CIS_InfoImportantes");

            // Showing when the cps process is launched
            $end = new \DateTime();
            $output->writeln('<comment>' . $end->format('d-m-Y G:i:s') . ' Stop processing :---</comment>');


            return Command::SUCCESS;


        } catch(\Exception $e){
            error_log($e->getMessage());
            $output->writeln($e->getMessage());
            return Command::FAILURE;

        }
    }
}
