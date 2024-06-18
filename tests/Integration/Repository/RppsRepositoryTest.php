<?php

namespace App\Tests\Integration\Repository;

use App\DataFixtures\LoadRPPS;
use App\Entity\RPPS;
use Doctrine\ORM\EntityManager;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class RppsRepositoryTest extends KernelTestCase
{
    use FixturesTrait;

    private EntityManager $entityManager;

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
    public function testRppsImportToDatabase(): void
    {
        $this->loadFixtures([
            LoadRPPS::class
        ]);


        $data = $this->entityManager->getRepository(RPPS::class)->findAll();

        $this->assertCount(11, $data);
    }

}
