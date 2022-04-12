<?php

namespace App\Service;

use App\Entity\Job;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Contains all useful methods to process files and import them into database.
 */
class JobService extends FileParserService
{
    /**
     * @var string
     */
    protected $projectDir;

    public function __construct(string $projectDir, FileProcessor $fileProcessor, EntityManagerInterface $em)
    {
        $this->projectDir = $projectDir;

        parent::__construct(Job::class, $fileProcessor, $em);
    }

    /**
     * @return bool
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function parse() : bool
    {
        return $this->processFile(
            $this->output,
            $this->getFile(),
            "default",
            [
                'delimiter' => ",",
                "utf8" => true,
                "headers" => true
            ]
        );
    }

    /**
     * @param array $data
     * @param string $type
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    protected function processData(array $data, string $type) : ?Job
    {
        [$code, $mode, $class, $name] = $data;
        $job = $this->repository->find($code);

        if (null === $job) {
            $job = new Job();
            $job->setCode($code);
        }

        $job->setMode($mode);
        $job->setClass($class);
        $job->setName($name);

        $this->em->persist($job);
        $this->em->flush();

        return $job;
    }

    /**
     * @return string
     */
    protected function getFile() : string
    {
        return "$this->projectDir/data/jobs.csv";
    }
}
