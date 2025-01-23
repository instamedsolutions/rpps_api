<?php

namespace App\Command;

use App\Entity\Cim11;
use App\Entity\Cim11ModifierValue;
use App\Entity\TranslatableEntityInterface;
use App\Entity\Translation;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'app:cim11:populate-translations',
    description: 'Fetch translations for CIM-11 entities from the English API and populate the database.'
)]
class Cim11TranslationCommand extends Command
{
    private OutputInterface $output;
    private int $successNameCount = 0;
    private int $successSynonymsCount = 0;
    private int $nbSynonyms = 0;
    private int $failureCount = 0;
    private array $failedIds = [];
    private array $unspecifiedTranslations = [];
    private array $otherIds = [];
    private bool $verbose = false;

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly HttpClientInterface $cim11Client,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'purge',
                null,
                InputOption::VALUE_NONE,
                'Purge existing translations for Cim11 entities before processing'
            )
            ->addOption(
                'v',
                null,
                InputOption::VALUE_NONE,
                'Show detailed logs for each API call and result'
            );
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->output = $output;
        $this->verbose = $input->getOption('v');
        $purge = $input->getOption('purge');
        $startTime = microtime(true);

        $output->writeln('<info>Starting CIM-11 translation population...</info>');

        // Purge translations if the purge option is set
        if ($purge) {
            $this->purgeTranslations();
            $output->writeln('<info>Purged existing translations for CIM-11 entities.</info>');
        }

        // Step 1: Retrieve all Cim11 entities
        $repository = $this->em->getRepository(Cim11::class);
        $cim11Entities = $repository->findAll();

        $output->writeln('<info>Processing WHO IDs...</info>');

        // First pass: Process all WHO IDs
        foreach ($cim11Entities as $entity) {
            $whoId = $entity->getWhoId();
            if (str_ends_with($whoId, '/other')) {
                // Track `/other` WHO IDs to handle later
                $this->otherIds[] = $entity;
            } elseif (str_ends_with($whoId, '/unspecified')) {
                // Process `/unspecified` WHO IDs
                $this->processTranslation($entity, '/unspecified');
            } else {
                // Process normal WHO IDs
                $this->processTranslation($entity);
            }
        }

        $this->em->flush();

        // Second pass: Handle `/other` WHO IDs
        $output->writeln('<info>Handling /OTHER WHO IDs...</info>');
        foreach ($this->otherIds as $entity) {
            $this->handleOtherTranslation($entity);
        }

        $this->em->flush();

        // Log failures
        if (!empty($this->failedIds)) {
            $output->writeln('<error>The following WHO IDs failed:</error>');
            foreach ($this->failedIds as $failedId) {
                $output->writeln("<error>$failedId</error>");
            }
        }

        // Step 1: Retrieve all Cim11 entities
        $repository = $this->em->getRepository(Cim11ModifierValue::class);
        $cim11Modifiers = $repository->findAll();

        foreach ($cim11Modifiers as $modifier) {
            $whoId = $modifier->getWhoId();
            if (str_ends_with($whoId, '/other')) {
                // Track `/other` WHO IDs to handle later
                $this->otherIds[] = $modifier;
            } elseif (str_ends_with($whoId, '/unspecified')) {
                // Process `/unspecified` WHO IDs
                $this->processTranslation($modifier, '/unspecified');
            } else {
                // Process normal WHO IDs
                $this->processTranslation($modifier);
            }
        }

        // Second pass: Handle `/other` WHO IDs
        $output->writeln('<info>Handling /OTHER WHO IDs...</info>');
        foreach ($this->otherIds as $entity) {
            $this->handleOtherTranslation($entity);
        }

        $this->em->flush();

        $endTime = microtime(true);
        $totalTime = round($endTime - $startTime, 2);

        $output->writeln('');
        $output->writeln('<info>Translation process completed.</info>');
        $output->writeln(sprintf('<info>Total time: %s seconds</info>', $totalTime));
        $output->writeln(sprintf('<info>Number of successful NAME translations: %d</info>', $this->successNameCount));
        $output->writeln(sprintf(
            '<info>Number of successful SYNONYMS translations: %d/%d</info>',
            $this->successSynonymsCount,
            $this->nbSynonyms
        ));
        $output->writeln(sprintf('<info>Number of failed translations: %d</info>', $this->failureCount));

        return Command::SUCCESS;
    }

    private function processTranslation(Cim11ModifierValue|Cim11 $entity, ?string $suffix = null): void
    {
        $whoId = $entity->getWhoId();
        if ($suffix) {
            // Remove the suffix /other or /unspecified to get only the WHO ID
            $whoId = str_replace($suffix, '', $whoId);
        }

        try {
            if ($this->verbose) {
                $this->output->writeln("<info>Fetching translation for WHO ID: $whoId</info>");
            }

            $endpoint = "/icd/entity/$whoId";

            $response = $this->cim11Client->request('GET', $endpoint, [
                'headers' => [
                    'API-Version' => 'v2',
                    'Accept-Language' => 'en',
                ],
            ]);

            $data = $response->toArray();
            $name = $data['title']['@value'] ?? null;
            $synonyms = $this->extractSynonyms($data);

            if ($name) {
                if ('/unspecified' === $suffix) {
                    // Save the translation for reuse with `/other`
                    $this->unspecifiedTranslations[$whoId] = [
                        'name' => $name,
                        'synonyms' => $synonyms,
                    ];
                }

                // Save name translation
                $this->createTranslation($entity, $name, 'name');
                ++$this->successNameCount;
            }

            if ($synonyms) {
                if (!empty($entity->getSynonyms())) {
                    ++$this->nbSynonyms;
                }

                // Save synonyms translation
                $this->createTranslation($entity, $synonyms, 'synonyms');
                ++$this->successSynonymsCount;
            }

            if (!$name && !$synonyms) {
                $this->logFailure($whoId);
            }
        } catch (Exception $e) {
            $this->logFailure($whoId, $e->getMessage());
        }
    }

    private function handleOtherTranslation(Cim11ModifierValue|Cim11 $entity): void
    {
        $whoId = $entity->getWhoId();
        $parentWhoId = str_replace('/other', '', $whoId);

        // Check if we already have the `/unspecified` translation
        if (isset($this->unspecifiedTranslations[$parentWhoId])) {
            $unspecifiedData = $this->unspecifiedTranslations[$parentWhoId];

            // Handle the `name` field
            $nameTranslation = 'Other ' . lcfirst($unspecifiedData['name']);
            $this->createTranslation($entity, $nameTranslation, 'name');
            ++$this->successNameCount;

            // Handle the `synonyms` field
            if (!empty($unspecifiedData['synonyms'])) {
                $this->createTranslation($entity, $unspecifiedData['synonyms'], 'synonyms');
                ++$this->successSynonymsCount;
                ++$this->nbSynonyms;
            }
        } else {
            // Handle orphan `/other` by querying the parent WHO ID
            if ($this->verbose) {
                $this->output->writeln("<info>Handling orphan /OTHER WHO ID: $whoId</info>");
            }
            $this->processTranslation($entity, '/other');
        }
    }

    private function createTranslation(TranslatableEntityInterface $entity, string $value, string $field): void
    {
        $translation = new Translation();
        $translation->setLang('en');
        $translation->setField($field);
        $translation->setTranslation($value);
        $entity->addTranslation($translation);
        $this->em->persist($translation);
    }

    private function extractSynonyms(array $data): ?string
    {
        if (!isset($data['synonym']) || !is_array($data['synonym'])) {
            return null;
        }

        $synonyms = array_map(function ($synonym) {
            return $synonym['label']['@value'] ?? null;
        }, $data['synonym']);

        // Filter out any null values and join the synonyms into a single string
        return implode(', ', array_filter($synonyms));
    }

    private function logFailure(string $whoId, string $message = ''): void
    {
        ++$this->failureCount;
        $this->failedIds[] = $whoId;
        $this->output->writeln("<error>Failed WHO ID: $whoId. $message</error>");
    }

    private function purgeTranslations(): void
    {
        try {
            $this->output->writeln(
                '<info>Purging existing translations (lang: en, fields: name and synonyms)...</info>'
            );

            $cim11Entities = $this->em->getRepository(Cim11::class)->findAll();

            foreach ($cim11Entities as $cim11) {
                $translations = $cim11->getTranslations()->filter(function (Translation $translation) {
                    return 'en' === $translation->getLang() && in_array($translation->getField(), ['name', 'synonyms']);
                });

                foreach ($translations as $translation) {
                    $cim11->getTranslations()->removeElement($translation);
                    $this->em->remove($translation);
                }
            }

            $cim11Modifiers = $this->em->getRepository(Cim11ModifierValue::class)->findAll();

            foreach ($cim11Modifiers as $cim11) {
                $translations = $cim11->getTranslations()->filter(function (Translation $translation) {
                    return 'en' === $translation->getLang() && in_array($translation->getField(), ['name', 'synonyms']);
                });

                foreach ($translations as $translation) {
                    $cim11->getTranslations()->removeElement($translation);
                    $this->em->remove($translation);
                }
            }

            $this->em->flush();
        } catch (Exception $e) {
            $this->output->writeln('<error>Failed to purge translations: ' . $e->getMessage() . '</error>');
        }
    }
}
