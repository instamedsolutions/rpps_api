<?php

namespace App\Command;

use App\DataFixtures\LoadRPPS;
use App\Entity\RPPS;
use App\Service\DrugService;
use App\Service\FileProcessor;
use App\Service\RPPSService;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\DataFixtures\ContainerAwareLoader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;


/**
 * Command to import file in empty database.
 */
class CreateTestData extends Command
{

    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:test:create';


    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var KernelInterface
     */
    protected $kernel;



    /**
     * CreateTestData constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager,KernelInterface $kernel)
    {

        parent::__construct(self::$defaultName);

        $this->em = $entityManager;
        $this->kernel = $kernel;
    }


    /**
     *
     */
    protected function configure()
    {
        $this->setDescription('Create test data')
            ->setHelp('This command will create some test data.');

    }


    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {


        $data = $this->em->getRepository(RPPS::class)->find("111111111111");

        if($data instanceof RPPS) {
            $output->writeln("Vous avez déjà des données de test chargées");

            return Command::FAILURE;
        }

        $loader = new ContainerAwareLoader($this->kernel->getContainer());

        $fixture = new LoadRPPS();
        $loader->addFixture($fixture);

        $executor = new ORMExecutor($this->em);
        $executor->execute($loader->getFixtures(), true);

        $output->writeln("Les données ont bien été chargées");

        return Command::SUCCESS;


    }
}
