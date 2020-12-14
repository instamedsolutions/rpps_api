<?php

namespace App\tests\Repository;

use App\Entity\RPPS;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class RppsRepositoryTest extends KernelTestCase
{
    use FixturesTrait;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }


    public function testRppsImportToDB()
    {
        // add all your fixtures classes that implement
        // Doctrine\Common\DataFixtures\FixtureInterface
        $this->loadFixtures(array(
            'App\DataFixtures\LoadRPPS'
        ));

        $rppsDatas = new RPPS();

        $rppsDatas =  $this->entityManager
            ->getRepository(RPPS::class)
            ->findAll();

            $count = 0;
            foreach($rppsDatas as $rppsData) {
                $count++;
            }

            $this->assertEquals(5, $count);

    }

}
