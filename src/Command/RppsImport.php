<?php

namespace App\Command;

use App\Service\FileProcessor;
use App\Service\RPPSService;
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
     * @var RPPSService
     */
    protected $rppsService;


    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * RppsImport constructor.
     * @param RPPSService $RPPSService
     */
    public function __construct(RPPSService $RPPSService,EntityManagerInterface $em)
    {

        $this->em = $em;
        parent::__construct(self::$defaultName);

        $this->rppsService = $RPPSService;

    }


    /**
     *
     */
    protected function configure()
    {
        $this->setDescription('Import RPPS File into database')
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


            $rpps = $this->rppsService->importFile($output,"rpps",true);

            //Checking failure
            if ($rpps !== true) {
                $output->writeln("RPPS Load Failed");
                return Command::FAILURE;
            }

            $cps = $this->rppsService->importFile($output,"cps",true);

            if($cps !== true) {
                $output->writeln("CPS Load Failed");
                return Command::FAILURE;
            }

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
