<?php

namespace App\Command;

use App\Entity\RPPS;
use App\Service\FileProcessor;
use App\Repository\RPPSRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class UpdateRPPSCommand extends Command
{
    protected static $defaultName = 'app:rpps:update';
    private $entityManager;

    public function __construct($projectDir, EntityManagerInterface $entityManager, FileProcessor $fileProcessor )
    {
        $this->projectDir = $projectDir;
        $this->entityManager = $entityManager;
        $this->fileProcessor = $fileProcessor;
        
        parent::__construct();
    }

    protected function configure()
    {
        $this
            //->setName('app:rpps:update')
            ->setDescription('Update RPPS Data in the database')
            ->setHelp('This command will update a RPPS CSV file into your database.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $url="https://annuaire.sante.fr/web/site-pro/extractions-publiques?p_p_id=abonnementportlet_WAR_Inscriptionportlet_INSTANCE_gGMT6fhOPMYV&p_p_lifecycle=2&p_p_state=normal&p_p_mode=view&p_p_cacheability=cacheLevelPage&_abonnementportlet_WAR_Inscriptionportlet_INSTANCE_gGMT6fhOPMYV_nomFichier=ExtractionMonoTable_CAT18_ToutePopulation_202011241543.zip";
        $fileName ="ExtractionMonoTable_CAT18_ToutePopulation_202011241543";
        
        $input_rpps_file = $this->fileProcessor->getFile($this->projectDir, $url ,$fileName);
        $batchSize = 20;
        $lineCount = $this->fileProcessor->getLinesCount($input_rpps_file);

        $rpps = $this->fileProcessor->updateRppsFile($output, $this->entityManager, $input_rpps_file, $lineCount, $batchSize);


        return 0;

    }


}