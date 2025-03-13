<?php

namespace App\Service;

use App\Entity\Entity;
use App\Entity\InseeCommune;
use App\Entity\InseeCommune1943;
use App\Entity\InseeCommuneEvent;
use App\Entity\InseePays;
use App\Entity\InseePays1943;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class InseeService extends ImporterService
{
    private bool $verbose = false;
    private int $nbSuccessCommune = 0;
    private int $nbSuccessCommune1943 = 0;
    private int $nbSuccessCommuneEvents = 0;
    private int $nbSuccessPays = 0;
    private int $nbSuccessPays1943 = 0;

    public function __construct(
        FileProcessor $fileProcessor,
        EntityManagerInterface $em,
    ) {
        parent::__construct(InseeCommune::class, $fileProcessor, $em);
    }

    public function setVerbose(bool $verbose): void
    {
        $this->verbose = $verbose;
    }

    public function importData(string $filePath, string $type, string $separator = ',', ?int $startLine = 0): bool
    {
        if (!file_exists($filePath)) {
            $this->output->writeln('<error>File not found: ' . $filePath . '</error>');

            return false;
        }

        if (($handle = fopen($filePath, 'rb')) !== false) {
            // Skip the header row
            fgetcsv($handle, 1000, $separator);

            $lineCounter = 0;

            while (($data = fgetcsv($handle, 1000, $separator)) !== false) {
                if (0 !== $startLine && $lineCounter < $startLine) {
                    if (0 === $lineCounter % 2000) {
                        $this->output->writeln("Skipped to row $lineCounter in type $type");
                    }
                    ++$lineCounter;
                    continue;
                }

                $this->processLine($data, $type);

                ++$lineCounter;
                if (0 === $lineCounter % 500 && !$this->verbose) {
                    // Output a progress message every 500 lines
                    $this->output->writeln("<comment>Processed $lineCounter lines...</comment>");
                }
            }

            fclose($handle);
        } else {
            $this->output->writeln('<error>Unable to open file: ' . $filePath . '</error>');

            return false;
        }

        $this->em->flush();

        $this->printFinalStats();

        return true;
    }

    /**
     * Process a single CSV row based on the dataset type.
     */
    private function processLine(array $data, string $datasetType): void
    {
        if ('commune' === $datasetType) {
            $this->processInseeCommuneData($data);
        } elseif ('commune1943' === $datasetType) {
            $this->processInseeCommune1943Data($data);
        } elseif ('event' === $datasetType) {
            $this->processInseeCommuneEventData($data);
        } elseif ('pays' === $datasetType) {
            $this->processInseePaysData($data);
        } elseif ('pays1943' === $datasetType) {
            $this->processInseePays1943Data($data);
        } else {
            $this->output->writeln("<error>Unknown dataset type: $datasetType</error>");
        }
    }

    private function printFinalStats(): void
    {
        $this->output->writeln([
            '',
            '  <info>=========================================</info>',
            '  <info>         I M P O R T   S U M M A R Y         </info>',
            '  <info>=========================================</info>',
            '',
            "  <comment>- Communes (current)</comment>:        <info>$this->nbSuccessCommune</info>",
            "  <comment>- Communes (1943)</comment>:          <info>$this->nbSuccessCommune1943</info>",
            "  <comment>- Commune events</comment>:           <info>$this->nbSuccessCommuneEvents</info>",
            "  <comment>- Pays (current)</comment>:           <info>$this->nbSuccessPays</info>",
            "  <comment>- Pays (1943)</comment>:              <info>$this->nbSuccessPays1943</info>",
            '',
            '  <info>=========================================</info>',
            '  <info>         I M P O R T   C O M P L E T E D         </info>',
            '  <info>=========================================</info>',
            '',
        ]);
    }

    /**
     * Process a single line (array of data) from the INSEE 'Communes existantes' CSV file
     * and create/update an InseeCommune entity accordingly.
     *
     * Example CSV line:
     * [
     *   "COM",
     *   "01001",
     *   "84",
     *   "01",
     *   "01D",
     *   "012",
     *   "5",
     *   "ABERGEMENT CLEMENCIAT",
     *   "Abergement-Clémenciat",
     *   "L'Abergement-Clémenciat",
     *   "0108",
     *   ""
     * ]
     */
    private function processInseeCommuneData(array $data): void
    {
        // "TYPECOM","COM","REG","DEP","CTCD","ARR","TNCC","NCC","NCCENR","LIBELLE","CAN","COMPARENT"
        [
            $typeCommune,
            $codeCommune,
            $codeRegion,
            $codeDepartement,
            $codeCollectivite,
            $codeArrondissement,
            $typeNomEnClair,
            $nomEnClair,
            $nomEnClairTypo,
            $nomEnClairAvecArticle,
            $codeCanton,
            $codeCommuneParente,
        ] = $data;

        $inseeCommune = new InseeCommune();
        $inseeCommune->setTypeCommune($typeCommune);
        $inseeCommune->setCodeCommune($codeCommune);
        $inseeCommune->setCodeRegion($codeRegion);
        $inseeCommune->setCodeDepartement($codeDepartement);
        $inseeCommune->setCodeCollectivite($codeCollectivite);
        $inseeCommune->setCodeArrondissement($codeArrondissement);
        $inseeCommune->setTypeNomEnClair($typeNomEnClair);
        $inseeCommune->setNomEnClair($nomEnClair);
        $inseeCommune->setNomEnClairTypo($nomEnClairTypo);
        $inseeCommune->setNomEnClairAvecArticle($nomEnClairAvecArticle);
        $inseeCommune->setCodeCanton($codeCanton);
        $inseeCommune->setCodeCommuneParente($codeCommuneParente);

        $inseeCommune->setImportId($this->getImportId());

        $this->em->persist($inseeCommune);
        ++$this->nbSuccessCommune;

        if ($this->verbose) {
            $this->output->writeln("<info>Commune '{$inseeCommune->getNomEnClair()}' saved.</info>");
        }
    }

    private function processInseeCommune1943Data(array $data): void
    {
        // COM, TNCC, NCC, NCCENR, LIBELLE, DATE_DEBUT, DATE_FIN
        [
            $rawCodeCommune,
            $rawTypeNomEnClair,
            $rawNomMajuscule,
            $rawNomTypographie,
            $rawNomAvecArticle,
            $rawDateDebut,
            $rawDateFin,
        ] = $data;

        $commune1943 = new InseeCommune1943();
        $commune1943->setCodeCommune($rawCodeCommune);
        $commune1943->setTypeNomEnClair($rawTypeNomEnClair);
        $commune1943->setNomMajuscule($rawNomMajuscule);
        $commune1943->setNomTypographie($rawNomTypographie);
        $commune1943->setNomAvecArticle($rawNomAvecArticle);

        $dateDebut = null;
        if (!empty($rawDateDebut)) {
            try {
                $dateDebut = new DateTime($rawDateDebut);
            } catch (Exception) {
                if ($this->verbose) {
                    $this->output->writeln(
                        "<error>Invalid DATE_DEBUT $rawDateDebut for commune $rawNomMajuscule (COM: $rawCodeCommune). Using null.</error>"
                    );
                }
            }
        }
        $commune1943->setDateDebut($dateDebut);

        $dateFin = null;
        if (!empty($rawDateFin)) {
            try {
                $dateFin = new DateTime($rawDateFin);
            } catch (Exception) {
                if ($this->verbose) {
                    $this->output->writeln(
                        "<error>Invalid DATE_FIN $rawDateFin for commune $rawNomMajuscule (COM: $rawCodeCommune). Using null.</error>"
                    );
                }
            }
        }
        $commune1943->setDateFin($dateFin);

        // If using ImportIdTrait
        $commune1943->setImportId($this->getImportId());

        $this->em->persist($commune1943);
        ++$this->nbSuccessCommune1943;

        if ($this->verbose) {
            $this->output->writeln(
                "<info>InseeCommune1943 $rawNomMajuscule saved (COM: $rawCodeCommune).</info>"
            );
        }
    }

    /**
     * Process a single line from the "Évènements sur les communes" CSV
     * and create/update an InseeCommuneEvent entity accordingly.
     *
     * Example line:
     * [
     *   "32",
     *   "2024-01-01",
     *   "COM",
     *   "08053",
     *   "0",
     *   "BAZEILLES",
     *   "Bazeilles",
     *   "Bazeilles",
     *   "COM",
     *   "08053",
     *   "0",
     *   "BAZEILLES",
     *   "Bazeilles",
     *   "Bazeilles"
     * ]
     */
    private function processInseeCommuneEventData(array $data): void
    {
        [
            $rawMod,
            $rawDateEff,
            $rawTypeCommuneAv,
            $rawCodeCommuneAv,
            $rawTnccAv,
            $rawNomMajusculeAv,
            $rawNomTypoAv,
            $rawNomArticleAv,
            $rawTypeCommuneAp,
            $rawCodeCommuneAp,
            $rawTnccAp,
            $rawNomMajusculeAp,
            $rawNomTypoAp,
            $rawNomArticleAp,
        ] = $data;

        $eventEntity = new InseeCommuneEvent();
        $eventEntity->setModEvent($rawMod);

        // DATE_EFF
        $dateEff = null;
        if (!empty($rawDateEff)) {
            try {
                $dateEff = new DateTime($rawDateEff);
            } catch (Exception) {
                if ($this->verbose) {
                    $this->output->writeln("<error>Invalid DATE_EFF format $rawDateEff. Using null.</error>");
                }
            }
        }
        $eventEntity->setDateEff($dateEff);

        // BEFORE EVENT
        $eventEntity->setTypeCommuneAv($rawTypeCommuneAv);
        $eventEntity->setCodeCommuneAv($rawCodeCommuneAv);
        $eventEntity->setTnccAv($rawTnccAv);
        $eventEntity->setNomMajusculeAv($rawNomMajusculeAv);
        $eventEntity->setNomTypoAv($rawNomTypoAv);
        $eventEntity->setNomArticleAv($rawNomArticleAv);

        // AFTER EVENT
        $eventEntity->setTypeCommuneAp($rawTypeCommuneAp);
        $eventEntity->setCodeCommuneAp($rawCodeCommuneAp);
        $eventEntity->setTnccAp($rawTnccAp);
        $eventEntity->setNomMajusculeAp($rawNomMajusculeAp);
        $eventEntity->setNomTypoAp($rawNomTypoAp);
        $eventEntity->setNomArticleAp($rawNomArticleAp);

        $eventEntity->setImportId($this->getImportId());

        $this->em->persist($eventEntity);
        ++$this->nbSuccessCommuneEvents;

        if ($this->verbose) {
            $this->output->writeln(
                "<info>Event COM_AV=$rawCodeCommuneAv → COM_AP=$rawCodeCommuneAp saved (MOD=$rawMod).</info>"
            );
        }
    }

    /**
     * Process a single line (array of data) from the INSEE 'Pays et territoires étrangers' CSV file
     * and create/update an InseePays entity accordingly.
     *
     * Example CSV line:
     * [
     *   "99100",
     *   "1",
     *   null,
     *   null,
     *   "France",
     *   "République française",
     *   "FR",
     *   "FRA",
     *   "250"
     * ]
     */
    private function processInseePaysData(array $data): void
    {
        // "COG","ACTUAL","CRPAY","ANI","LIBCOG","LIBENR","CODEISO2","CODEISO3","CODENUM3"
        [
            $codePays,
            $codeActualite,
            $codeRattachement,
            $anneeApparition,
            $libelleCog,
            $libelleOfficiel,
            $codeIso2,
            $codeIso3,
            $codeIsoNum3,
        ] = $data;

        $inseePays = new InseePays();
        $inseePays->setCodePays($codePays);
        $inseePays->setCodeActualite($codeActualite);
        $inseePays->setCodeRattachement($codeRattachement);
        $inseePays->setAnneeApparition($anneeApparition);
        $inseePays->setLibelleCog($libelleCog);
        $inseePays->setLibelleOfficiel($libelleOfficiel);
        $inseePays->setCodeIso2($codeIso2);
        $inseePays->setCodeIso3($codeIso3);
        $inseePays->setCodeIsoNum3($codeIsoNum3);

        $inseePays->setImportId($this->getImportId());

        $this->em->persist($inseePays);
        ++$this->nbSuccessPays;

        if ($this->verbose) {
            $this->output->writeln("<info>Pays '{$inseePays->getLibelleCog()}' saved.</info>");
        }
    }

    /**
     * Process a single CSV line from the "Pays et territoires étrangers depuis 1943" dataset
     * and create/update an InseePays1943 entity accordingly.
     *
     * Example of CSV line:
     * [
     *   "99102",      // COG
     *   "99101",      // CRPAY
     *   "Islande",    // LIBCOG
     *   "Royaume d’Islande",  // LIBENR
     *   "1943-01-01", // DATE_DEBUT
     *   "1944-06-17", // DATE_FIN (could be empty)
     * ]
     */
    private function processInseePays1943Data(array $data): void
    {
        // COG,CRPAY,LIBCOG,LIBENR,DATE_DEBUT,DATE_FIN
        [
            $codePays,
            $codeRattachement,
            $libelleCog,
            $libelleOfficiel,
            $rawDateDebut,
            $rawDateFin,
        ] = $data;

        $pays1943 = new InseePays1943();
        $pays1943->setCodePays($codePays);
        $pays1943->setCodeRattachement($codeRattachement);
        $pays1943->setLibelleCog($libelleCog);
        $pays1943->setLibelleOfficiel($libelleOfficiel);

        $dateDebut = null;
        if (!empty($rawDateDebut)) {
            try {
                $dateDebut = new DateTime($rawDateDebut);
            } catch (Exception) {
                if ($this->verbose) {
                    $this->output->writeln(
                        "<error>Invalid DATE_DEBUT format $rawDateDebut for $libelleCog (COG: $codePays). Using null instead.</error>"
                    );
                }
            }
        }
        $pays1943->setDateDebut($dateDebut);

        $dateFin = null;
        if (!empty($rawDateFin)) {
            try {
                $dateFin = new DateTime($rawDateFin);
            } catch (Exception) {
                if ($this->verbose) {
                    $this->output->writeln(
                        "<error>Invalid DATE_FIN format $rawDateFin for $libelleCog (COG: $codePays). Using null instead.</error>"
                    );
                }
            }
        }
        $pays1943->setDateFin($dateFin);

        $pays1943->setImportId($this->getImportId());

        $this->em->persist($pays1943);
        ++$this->nbSuccessPays1943;

        if ($this->verbose) {
            $infoLabel = $pays1943->getLibelleCog() ?: $codePays;
            $this->output->writeln("<info>InseePays1943 $infoLabel saved (COG: $codePays).</info>");
        }
    }

    /**
     * Purges all the INSEE-related tables. Adapt as needed if references to exist in other entities.
     */
    public function purgeAllData(): void
    {
        // Communes
        $this->em->createQuery('DELETE FROM App\Entity\InseeCommune')->execute();
        $this->output->writeln('<info>All communes have been purged.</info>');

        // Communes 1943
        $this->em->createQuery('DELETE FROM App\Entity\InseeCommune1943')->execute();
        $this->output->writeln('<info>All 1943 communes have been purged.</info>');

        // Commune Events
        $this->em->createQuery('DELETE FROM App\Entity\InseeCommuneEvent')->execute();
        $this->output->writeln('<info>All commune events have been purged.</info>');

        // Pays
        $this->em->createQuery('DELETE FROM App\Entity\InseePays')->execute();
        $this->output->writeln('<info>All countries have been purged.</info>');

        // Pays 1943
        $this->em->createQuery('DELETE FROM App\Entity\InseePays1943')->execute();
        $this->output->writeln('<info>All historical countries (1943) have been purged.</info>');
    }

    protected function processData(array $data, string $type): ?Entity
    {
        return null;
    }
}
