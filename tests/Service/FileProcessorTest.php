<?php

namespace App\Tests\Service;

use App\Service\FileProcessor;
use Liip\FunctionalTestBundle\Test\WebTestCase;


class FileProcessorTest extends WebTestCase
{


    /**
     * @var object|null
     */
    protected $em;

    public function setUp(): void
    {
        self::bootKernel();

        $container = self::$container;

        $this->em = $container->get('doctrine.orm.entity_manager');
    }


    public function testCountFileLines()
    {
        $fileName = __DIR__ . '/docs/line-count.csv';
        $fileProcessor = new FileProcessor(__DIR__, $this->em);
        $lineCount = $fileProcessor->getLinesCount($fileName);

        $this->assertEquals(5, $lineCount);
    }


}
