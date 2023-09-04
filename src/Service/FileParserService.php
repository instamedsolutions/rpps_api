<?php

namespace App\Service;

use App\Entity\Entity;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Contains all useful methods to process files and import them into database.
 */
abstract class FileParserService
{
    protected ObjectRepository $repository;

    protected OutputInterface $output;

    protected bool $clearable = true;

    public function __construct(
        protected string $entity,
        protected readonly FileProcessor $fileProcessor,
        protected readonly EntityManagerInterface $em
    ) {
        $this->init($entity);
    }

    private ?string $importId = null;

    public function getImportId(): string
    {
        if (!$this->importId) {
            $this->importId = uniqid('import_');
        }

        return $this->importId;
    }

    protected function init(string $entity): void
    {
        /* @phpstan-ignore-next-line */
        $this->repository = $this->em->getRepository($entity);
        $this->entity = $entity;
    }

    protected function processFile(
        OutputInterface $output,
        string $file,
        string $type = 'default',
        array $options = ['delimiter' => "\t", 'utf8' => false, 'headers' => false],
        int $start = 0,
        int $limit = 0
    ): bool {
        $batchSize = 20;

        $lineCount = $this->fileProcessor->getLinesCount($file);

        // Showing when the drugs process is launched
        $startTime = new DateTime();
        $end = new DateTime();

        $output->writeln(
            '<comment>Start : ' . $startTime->format(
                'd-m-Y G:i:s'
            ) . ' | You have ' . $lineCount . ' lines to import from your ' . $type . ' file to your database ---</comment>'
        );

        if ($start && $limit) {
            $output->writeln(
                '<comment>Start : ' . $startTime->format(
                    'd-m-Y G:i:s'
                ) . ' | Importing ' . $limit . ' lines starting at linee ' . $start . ' ---</comment>'
            );
        }

        // Will go through file by iterating on each line to save memory
        if (($handle = fopen($file, 'r')) !== false) {
            $row = 0;

            while (($data = fgetcsv($handle, 0, $options['delimiter'])) !== false) {
                if ($options['headers'] && 0 === $row) {
                    ++$row;
                    continue;
                }

                if (isset($options['first_line']) && $row < $options['first_line']) {
                    ++$row;
                    continue;
                }

                // https://stackoverflow.com/questions/20124630/strange-characters-in-first-row-of-array-after-fgetcsv
                // Remove BOM
                if (0 === $row) {
                    $data[0] = preg_replace('/\x{EF}\x{BB}\x{BF}/', '', $data[0]);
                }

                if (0 !== $start && $row < $start) {
                    if (0 === $row % 50000) {
                        $output->writeln("Skipped to row $row");
                    }
                    ++$row;
                    continue;
                }

                if (($row > $start + $limit) && $limit) {
                    break;
                }

                if (!$options['utf8']) {
                    $data = array_map(fn ($d) => utf8_encode((string) $d), $data);
                }

                $entity = $this->processData($data, $type);

                if ($entity instanceof $this->entity) {
                    $this->em->persist($entity);
                    $this->em->flush();
                }

                // Used to save some memory out of Doctrine every 20 lines
                if (($row % $batchSize) === 0) {
                    if ($this->isClearable()) {
                        // Detaches all objects from Doctrine for memory save
                        $this->em->clear();
                    }

                    // Showing progression of the process
                    $end = new DateTime();
                    $output->writeln(
                        ($row - $start) . ' of lines imported out of ' . ($limit ? $limit : $lineCount) . ' | ' . $end->format('d-m-Y G:i:s')
                    );
                }

                ++$row;
            }

            fclose($handle);

            // Showing when the rpps process is done
            $output->writeln(
                '<comment>End of loading : (Started at ' . $startTime->format(
                    'd-m-Y G:i:s'
                ) . ' / Ended at ' . $end->format(
                    'd-m-Y G:i:s'
                ) . ' | You have imported all datas from your file to your database ---</comment>'
            );
        }

        //  $this->removeOldData();

        return true;
    }

    public function removeOldData(): void
    {
        $metadata = $this->em->getClassMetadata($this->entity);

        $this->em->getConnection()->executeQuery(
            "DELETE FROM {$metadata->getTableName()} WHERE import_id <> ?",
            [$this->getImportId()]
        );

        $this->output->writeln("Old data removed, keeping {$this->getImportId()} only");
    }

    public function setClearable(bool $clearable): void
    {
        $this->clearable = $clearable;
    }

    public function isClearable(): bool
    {
        return $this->clearable;
    }

    public function getOutput(): OutputInterface
    {
        return $this->output;
    }

    public function setOutput(OutputInterface $output): void
    {
        $this->output = $output;
    }

    abstract protected function processData(array $data, string $type): ?Entity;
}
