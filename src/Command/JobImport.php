<?php

namespace App\Command;

use App\Service\JobService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to import file in empty database.
**/
class JobImport extends Command
{
    protected static $defaultName = 'app:job:import';

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var JobService
     */
    protected $jobService;

    public function __construct(JobService $jobService, EntityManagerInterface $entityManager)
    {
        parent::__construct(self::$defaultName);

        $this->jobService = $jobService;
        $this->em = $entityManager;
    }

    /**
     *
     */
    protected function configure()
    {
        $this->setDescription('Import Job File into database')
            ->setHelp('This command will import all jobs data.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->jobService->setOutput($output);

        try {
            // Turning off doctrine default logs queries for saving memory
            $this->em->getConnection()->getConfiguration()->setSQLLogger(null);

            // Showing when the cps process is launched
            $start = new \DateTime();
            $output->writeln('<comment>' . $start->format('d-m-Y G:i:s') . ' Start processing :---</comment>');

            $this->jobService->parse();

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
