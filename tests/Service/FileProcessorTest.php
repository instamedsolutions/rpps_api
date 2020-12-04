<?php 

namespace App\tests\Service;

use App\Entity\RPPS;
use App\Service\FileProcessor;
use Doctrine\Common\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;


class FileProcessorTest extends TestCase
{

    public function testCountFileLines()
    {
        $fileName = __DIR__ . '/docs/line-count.csv' ;
        $fileProcessor = new FileProcessor();
        $lineCount = $fileProcessor->getLinesCount($fileName);

        // assert that getLinesCount() method return the good number.
        $this->assertEquals(5, $lineCount);
    }


}