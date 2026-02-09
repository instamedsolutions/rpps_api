<?php

namespace App\Command;

use App\Entity\RPPS;
use App\Service\RPPSService;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to import file in empty database.
 */
#[AsCommand(name: 'app:test:create')]
class CreateTestData extends Command
{
    public function __construct(
        private readonly RPPSService $service,
        private readonly EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    protected function configure(): void
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

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $legacy = $input->getOption('legacy');
        if ($legacy) {
            return $this->legacyLoad($output);
        }

        $number = $input->getOption('number');
        if (!$number) {
            $number = 10;
        }

        for ($i = 0; $i < $number; ++$i) {
            $faker = Factory::create('fr_FR');
            $rpps = new RPPS();
            $rpps->setIdRpps('2' . $faker->numberBetween(1_000_000_000_000, 9_999_999_999_999));
            $rpps->setFirstName($faker->firstName);
            $rpps->setLastName($faker->lastName . ' Demo');
            $rpps->setEmail(
                strtolower(str_replace(' ', '-', (string) "{$rpps->getFirstName()}.{$rpps->getLastName()}@instamed.fr"))
            );
            $rpps->setTitle(random_int(0, 10) > 5 ? 'Docteur' : null);
            $rpps->setCpsNumber(random_int(0, 10) > 5 ? '9' . $faker->numberBetween(100_000_000, 999_999_999) : null);

            if (random_int(0, 10) > 6) {
                $rpps->setAddress($faker->streetAddress);
                $rpps->setCity($faker->city);
                $rpps->setZipcode($faker->postcode);
            }

            if (random_int(0, 10) > 4) {
                $rpps->setFinessNumber('3' . $faker->numberBetween(10_000_000, 99_999_999));
            }

            $rpps->setSpecialty($this->getSpecialty());

            $this->em->persist($rpps);

            $output->writeln("Creating $rpps");
        }

        $this->em->flush();

        return Command::SUCCESS;
    }

    protected function legacyLoad(OutputInterface $output): int
    {
        $this->service->setOutput($output);
        $this->service->loadTestData();

        return Command::SUCCESS;
    }

    protected function getSpecialty(): string
    {
        $specialties = [
            'Pharmacien',
            'Neurologie',
            'Médecine Générale',
            'Chirurgie générale',
            'Médecin',
            'Masseur-Kinésithérapeute',
            'Qualifié en Médecine Générale',
            'Pédicure-Podologue',
            'Radio-diagnostic',
            'Psychiatrie',
            'Spécialiste en Médecine Générale',
            'Chirurgien-Dentiste',
            'Pédiatrie',
            'Pneumologie',
            'Chirurgie urologique',
            'Endocrinologie et métabolisme',
            'Sage-Femme',
            'Stomatologie',
            'Neuro-psychiatrie',
            'Gynécologie-obstétrique',
            'Gastro-entérologie et hépatologie',
            'Anesthesie-réanimation',
            'Chirurgie orthopédique et traumatologie',
            'Ophtalmologie',
            'Allergologie',
            'Médecine du travail',
            'Dermatologie et vénéréologie',
            'Santé publique et médecine sociale',
            'Oto-rhino-laryngologie',
            'Rhumatologie',
            'Biologie médicale',
            'Néphrologie',
            'Anatomie et cytologie pathologiques',
            'Gériatrie',
            'Oncologie option radiothérapie',
            'Chirurgie Orale',
        ];

        return $specialties[array_rand($specialties, 1)];
    }
}
