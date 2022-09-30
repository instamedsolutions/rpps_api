<?php

namespace App\tests\Integration\Repository;

use App\DataFixtures\LoadRPPS;
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


    /**
     * @return void
     */
    public function testRppsImportToDatabase()
    {
        $this->loadFixtures([
            LoadRPPS::class
        ]);


        $data = $this->entityManager->getRepository(RPPS::class)->findAll();

        $this->assertCount(8, $data);
    }

}
