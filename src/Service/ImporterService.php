<?php

namespace App\Service;

use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Contains all useful methods to process files and import them into database.
 */
abstract class ImporterService extends FileParserService
{

    /**
     * @return bool
     * @throws NonUniqueResultException
     */
    public function importFile(OutputInterface $output, string $type): bool
    {
        /** Handling File */
        $file = $this->fileProcessor->getFile($this->$type, $type);

        $process = $this->processFile($output, $file, $type);

        unlink($file);

        return $process;
    }

}
