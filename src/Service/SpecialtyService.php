<?php

namespace App\Service;

use App\Entity\Entity;
use App\Entity\Specialty;
use Doctrine\ORM\EntityManagerInterface;
use Cocur\Slugify\Slugify;

class SpecialtyService extends ImporterService
{
    private bool $verbose = false;
    private array $specialtyMap = [];  // Hashmap of specialties ( for the linking process )

    public function __construct(
        FileProcessor $fileProcessor,
        EntityManagerInterface $em
    ) {
        parent::__construct(Specialty::class, $fileProcessor, $em);
    }

    public function setVerbose(bool $verbose): void
    {
        $this->verbose = $verbose;
    }

    public function importData(string $filePath, string $type, string $separator = ','): bool
    {
        if (!file_exists($filePath)) {
            $this->output->writeln('<error>File not found: ' . $filePath . '</error>');
            return false;
        }

        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            $this->output->writeln('<error>Unable to open file: ' . $filePath . '</error>');
            return false;
        }

        // Skip the header row
        fgetcsv($handle, 1000, $separator);

        if ($type === 'link') {
            // Build the specialty map
            $this->buildSpecialtyMap();
        }

        $slugify = new Slugify();
        $lineCounter = 0;

        while (($data = fgetcsv($handle, 1000, $separator)) !== false) {
            if ($type === 'specialties') {
                $this->processSpecialty($data, $slugify);
            } elseif ($type === 'link') {
                $this->processLink($data);
            }

            $lineCounter++;
        }

        fclose($handle);
        $this->em->flush();

        if ($this->verbose) {
            $this->output->writeln("<info>Processed $lineCounter lines in total.</info>");
        }

        return true;
    }

    private function buildSpecialtyMap(): void
    {
        // Load all specialties into the specialtyMap for fast lookup
        $specialties = $this->em->getRepository(Specialty::class)->findAll();
        foreach ($specialties as $specialty) {
            $this->specialtyMap[$specialty->getName()] = $specialty;
        }

        if ($this->verbose) {
            $this->output->writeln("<info>Specialty map built with " . count($this->specialtyMap) . " entries.</info>");
        }
    }

    private function processSpecialty(array $data, Slugify $slugify): void
    {
        [$specialtyName, $specialistName] = $data;

        // Create canonical form
        $canonical = $slugify->slugify($specialistName);

        // Create new specialty entity
        $specialty = new Specialty();
        $specialty->setName($specialtyName);
        $specialty->setCanonical($canonical);
        $specialty->setSpecialistName($specialistName);
        $specialty->importId = $this->getImportId();

        $this->em->persist($specialty);

        if ($this->verbose) {
            $this->output->writeln("<info>Imported specialty: $specialtyName</info>");
        }
    }

    private function processLink(array $data): void
    {
        // The first element is the main specialty, the rest are linked specialties
        $mainSpecialtyName = array_shift($data);

        // Retrieve the main specialty from the preloaded map
        $mainSpecialty = $this->specialtyMap[$mainSpecialtyName] ?? null;

        if (!$mainSpecialty) {
            $this->output->writeln("<error>Main specialty not found: $mainSpecialtyName</error>");
            return;
        }

        if ($this->verbose) {
            $this->output->writeln("<info> > Linking $mainSpecialtyName to : </info>");
        }

        // Process the remaining linked specialties
        foreach ($data as $linkedSpecialtyName) {
            $linkedSpecialty = $this->specialtyMap[$linkedSpecialtyName] ?? null;

            if ($linkedSpecialty) {
                $mainSpecialty->addSpecialty($linkedSpecialty);

                if ($this->verbose) {
                    $this->output->writeln("--- $linkedSpecialtyName");
                }
            } else {
                if ($this->verbose) {
                    $this->output->writeln("<error>Linked specialty not found: $linkedSpecialtyName</error>");
                }
            }
        }

        $this->em->persist($mainSpecialty);
    }

    public function purgeAllSpecialties(): void
    {
        // Set the specialtyEntity field to null for all RPPS entities
        $this->em->createQuery('UPDATE App\Entity\RPPS r SET r.specialtyEntity = NULL')->execute();

        // Clear the specialty_links table
        $this->em->getConnection()->executeStatement('DELETE FROM specialty_links');

        // Delete all specialty records
        $this->em->createQuery('DELETE FROM App\Entity\Specialty')->execute();

        $this->output->writeln('<info>All specialties have been purged, RPPS specialtyEntity fields cleared, and specialty_links table cleared.</info>');
    }

    protected function processData(array $data, string $type): ?Entity
    {
        return null;
    }
}
