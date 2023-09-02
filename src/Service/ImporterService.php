<?php

namespace App\Service;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * Contains all useful methods to process files and import them into database.
 */
abstract class ImporterService extends FileParserService
{
    public function importFile(OutputInterface $output, string $type, int $start = 0, int $limit = 0): bool
    {
        /** Handling File */
        $file = $this->fileProcessor->getFile($this->$type, $type);

        $process = $this->processFile($output, $file, $type);

        unlink($file);

        return $process;
    }
}
