<?php

namespace App\Tests\Unit\Service;

use App\Service\FileProcessor;
use Doctrine\ORM\EntityManagerInterface;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;


class FileProcessorTest extends KernelTestCase
{

    use ProphecyTrait;

    public function testCountFileLines()
    {
        $prophecy = $this->prophesize(EntityManagerInterface::class);

        $fileName = __DIR__ . '/docs/line-count.csv';
        $fileProcessor = new FileProcessor(__DIR__, $prophecy->reveal());
        $lineCount = $fileProcessor->getLinesCount($fileName);

        $this->assertEquals(5, $lineCount);
    }


}
