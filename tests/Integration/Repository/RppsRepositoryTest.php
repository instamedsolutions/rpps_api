<?php

namespace App\Tests\Integration\Repository;

use App\DataFixtures\LoadRPPS;
use App\Entity\RPPS;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\KernelInterface;

class RppsRepositoryTest extends KernelTestCase
{

    private KernelInterface $symfonyKernel;

    private EntityManager $entityManager;

    protected function setUp(): void
    {
        $this->symfonyKernel = self::bootKernel();

        $this->entityManager = $this->symfonyKernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }


    /**
     * @return void
     */
    public function testRppsImportToDatabase(): void
    {
        $loader = new Loader();

        $purger = new ORMPurger($this->entityManager);
        $purger->purge();

        $fixture = new LoadRPPS();
        $loader->addFixture($fixture);

        $executor = new ORMExecutor($this->entityManager);
        $executor->execute($loader->getFixtures(), true);


        $data = $this->entityManager->getRepository(RPPS::class)->findAll();

        $this->assertCount(11, $data);
    }

}
