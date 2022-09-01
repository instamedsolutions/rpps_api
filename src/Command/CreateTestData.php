<?php

namespace App\Command;

use App\DataFixtures\LoadRPPS;
use App\Entity\RPPS;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;
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
     * CreateTestData constructor.
     */
    public function __construct(protected EntityManagerInterface $em, protected KernelInterface $kernel)
    {
        parent::__construct(self::$defaultName);
    }


    /**
     *
     */
    protected function configure()
    {
        $this->setDescription('Create test data')
            ->setHelp('This command will create some test data.');

        $this->addOption(
            'legacy',
            'lg',
            InputOption::VALUE_OPTIONAL,
            'If you wish to use the legacy way of creating test users'

        );

        $this->addOption(
            'number',
            'nb',
            InputOption::VALUE_REQUIRED,
            'The number of test account to create'

        );
    }


    /**
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $legacy = $input->getOption("legacy");
        if ($legacy) {
            return $this->legacyLoad($output);
        }

        $number = $input->getOption("number");
        if (!$number) {
            $number = 10;
        }

        for ($i = 0; $i < $number; $i++) {
            $faker = Factory::create('fr_FR');
            $rpps = new RPPS();
            $rpps->setIdRpps("2" . $faker->numberBetween(1_000_000_000_000, 9_999_999_999_999));
            $rpps->setFirstName($faker->firstName);
            $rpps->setLastName($faker->lastName . " Demo");
            $rpps->setEmail(
                strtolower(str_replace(" ", "-", (string)"{$rpps->getFirstName()}.{$rpps->getLastName()}@instamed.fr"))
            );
            $rpps->setTitle(random_int(0, 10) > 5 ? "Docteur" : null);
            $rpps->setCpsNumber(random_int(0, 10) > 5 ? "9" . $faker->numberBetween(100_000_000, 999_999_999) : null);

            if (random_int(0, 10) > 6) {
                $rpps->setAddress($faker->streetAddress);
                $rpps->setCity($faker->city);
                $rpps->setZipcode($faker->postcode);
            }

            if (random_int(0, 10) > 4) {
                $rpps->setFinessNumber("3" . $faker->numberBetween(10_000_000, 99_999_999));
            }

            $rpps->setSpecialty($this->getSpecialty());

            $this->em->persist($rpps);

            $output->writeln("Creating $rpps");
        }

        $this->em->flush();


        return Command::SUCCESS;
    }


    protected function legacyLoad($output): int
    {
        $data = $this->em->getRepository(RPPS::class)->find("111111111111");

        if ($data instanceof RPPS) {
            $output->writeln("Existing data, deletion of the data in progress");

            for ($j = 1; $j <= 6; $j++) {
                $id = "1{$j}{$j}{$j}{$j}{$j}{$j}{$j}{$j}{$j}{$j}{$j}";

                $rpps = $this->em->getRepository(RPPS::class)->find($id);

                if ($rpps instanceof RPPS) {
                    $this->em->remove($rpps);
                }
            }

            $this->em->flush();
        }

        $loader = new ContainerAwareLoader($this->kernel->getContainer());

        $fixture = new LoadRPPS();
        $loader->addFixture($fixture);

        $executor = new ORMExecutor($this->em);
        $executor->execute($loader->getFixtures(), true);

        $output->writeln("Les données ont bien été chargées");

        return Command::SUCCESS;
    }


    protected function getSpecialty(): string
    {
        $specialties = [
            "Pharmacien",
            "Neurologie",
            "Médecine Générale",
            "Chirurgie générale",
            "Médecin",
            "Masseur-Kinésithérapeute",
            "Qualifié en Médecine Générale",
            "Pédicure-Podologue",
            "Radio-diagnostic",
            "Psychiatrie",
            "Spécialiste en Médecine Générale",
            "Chirurgien-Dentiste",
            "Pédiatrie",
            "Pneumologie",
            "Chirurgie urologique",
            "Endocrinologie et métabolisme",
            "Sage-Femme",
            "Stomatologie",
            "Neuro-psychiatrie",
            "Gynécologie-obstétrique",
            "Gastro-entérologie et hépatologie",
            "Anesthesie-réanimation",
            "Chirurgie orthopédique et traumatologie",
            "Ophtalmologie",
            "Allergologie",
            "Médecine du travail",
            "Dermatologie et vénéréologie",
            "Santé publique et médecine sociale",
            "Oto-rhino-laryngologie",
            "Rhumatologie",
            "Biologie médicale",
            "Néphrologie",
            "Anatomie et cytologie pathologiques",
            "Gériatrie",
            "Oncologie option radiothérapie",
            "Chirurgie Orale",
        ];


        return $specialties[array_rand($specialties, 1)];
    }


}
