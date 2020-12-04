<?php

namespace App\Command;

use App\Service\FileProcessor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to import file in empty database.
 */
class RppsImport extends Command {

        // the name of the command (the part after "bin/console")
        protected static $defaultName = 'app:rpps:import';
        private $entityManager;
        private $fileProcessor;

        public function __construct($projectDir, EntityManagerInterface $entityManager, FileProcessor $fileProcessor)
        {
            $this->projectDir = $projectDir;
            $this->entityManager = $entityManager;
            $this->fileProcessor = $fileProcessor;
            parent::__construct();
        }

        protected function configure()
        {
            $this->setDescription('Import RPPS File into databse')
                ->setHelp('This command will import a RPPS CSV file into your database.');
        }
    
        protected function execute(InputInterface $input, OutputInterface $output)
        {
            try {
                // Turning off doctrine default logs queries for saving memory
                $this->entityManager->getConnection()->getConfiguration()->setSQLLogger(null);

                // Showing when the cps process is launched
                $start = new \DateTime();
                $output->writeln('<comment>' . $start->format('d-m-Y G:i:s') . ' Start processing :---</comment>');

                /**
                 * Handling RPPS File
                 */
               
                $url="https://service.annuaire.sante.fr/annuaire-sante-webservices/V300/services/extraction/ExtractionMonoTable_CAT18_ToutePopulation";
                
                $input_rpps_file = $this->fileProcessor->getFile($this->projectDir, $url);

                $batchSize = 20;
                $lineCount = $this->fileProcessor->getLinesCount($input_rpps_file);

                $rpps = $this->fileProcessor->processRppsFile($output, $this->entityManager, $input_rpps_file, $lineCount, $batchSize);

                unlink($input_rpps_file);

                /**
                 * Handling CPS File
                 */
                $url = "https://service.annuaire.sante.fr/annuaire-sante-webservices/V300/services/extraction/Porteurs_CPS_CPF";
                $input_cps_file = $this->fileProcessor->getFile($this->projectDir, $url);
                
                $batchSize = 20;
                $lineCount = $this->fileProcessor->getLinesCount($input_cps_file);
                
                $cps = $this->fileProcessor->processCpsFile($output, $this->entityManager, $input_cps_file, $lineCount, $batchSize);
            
                unlink($cps);

                //Checking failure
                if (!$rpps == 0) {
                    echo 'Rpps load failed';
                    return Command::FAILURE;
                }

                if(!$cps == 0) {
                    echo 'Cps load failed';
                    return Command::FAILURE;
                }
                    
                return Command::SUCCESS;
                

            } catch(\Exception $e){

                error_log($e->getMessage());
                
            }
        }
}