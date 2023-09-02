<?php

namespace App\Service;

use App\Entity\NGAP;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Contains all useful methods to process files and import them into database.
 */
class NGAPService extends FileParserService
{
    public function __construct(protected string $projectDir, FileProcessor $fileProcessor, EntityManagerInterface $em)
    {
        parent::__construct(NGAP::class, $fileProcessor, $em);
    }

    public function parse(): bool
    {
        return $this->processFile(
            $this->output,
            $this->getFile(),
            'default',
            ['delimiter' => ',', 'utf8' => true, 'headers' => true]
        );
    }

    protected function processData(array $data, string $type): ?NGAP
    {
        $ngap = $this->repository->find($data[0]);

        if (null === $ngap) {
            $ngap = new NGAP();
            $ngap->code = $data[0];
        }

        $ngap->description = $data[1];
        $ngap->importId = $this->getImportId();

        $this->em->persist($ngap);
        $this->em->flush();

        return $ngap;
    }

    protected function getFile(): string
    {
        return "$this->projectDir/data/ngap.csv";
    }
}
