<?php

namespace App\Tests\Unit\Service;

use App\Service\FileProcessor;
use Doctrine\ORM\EntityManagerInterface;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;


class FileProcessorTest extends KernelTestCase
{

    use ProphecyTrait;

    public function testCountFileLines() : void
    {
        $prophecy = $this->prophesize(EntityManagerInterface::class);

        $logger = $this->prophesize(LoggerInterface::class);

        $fileName = __DIR__ . '/docs/line-count.csv';
        $fileProcessor = new FileProcessor(__DIR__, $logger->reveal(),$prophecy->reveal());
        $lineCount = $fileProcessor->getLinesCount($fileName);

        $this->assertEquals(5, $lineCount);
    }


}
