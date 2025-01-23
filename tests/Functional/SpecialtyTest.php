<?php

namespace App\Tests\Functional;

use App\Entity\RPPS;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class SpecialtyTest extends ApiTestCase
{
    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testGetSpecialtyData(): void
    {
        $data = $this->get('specialties');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertCollectionKeyContains(
            $data['hydra:member'],
            'name',
            ['Allergologie', 'Anatomie Et Cytologie Pathologiques', 'Biologie', 'Cardiologie']
        );
        $this->assertCollectionKeyContains($data['hydra:member'], 'specialistName', ['Cardiologue']);
        $this->assertCollectionKeyContains($data['hydra:member'], 'canonical', ['chirurgie-general']);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testGetSpecialtiesWithLimit(): void
    {
        $data = $this->get('specialties', ['_per_page' => 5]);
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertCount(5, $data['hydra:member']);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testGetSpecialtyByName(): void
    {
        $data = $this->get('specialties', ['search' => 'Cardiologie']);
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->assertEquals(1, count($data['hydra:member']));
        $this->assertCollectionKeyContains($data['hydra:member'], 'name', ['Cardiologie']);

        $data = $this->get('specialties', ['search' => 'chirurgie']);
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertCollectionKeyContains(
            $data['hydra:member'],
            'name',
            ['Neuro-Chirurgie', 'Chirurgien-Dentiste', 'Chirurgie Vasculaire']
        );
    }

    /**
     *
     * @group test
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testGetSpecialtyTranslated(): void
    {

        $data = $this->get('specialties/medecine-generale',[],false,[
            'Accept-Language' => 'en'
        ]);
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->assertEquals('General Medecine', $data['name']);
        $this->assertEquals('General Practitioner', $data['specialistName']);

    }

    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testGetSimilarSpecialties(): void
    {
        $specialty = $this->getSpecialty('hematologie');
        $this->assertNotNull($specialty);

        $data = $this->get("specialties/{$specialty->getId()}/similar");
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertEquals(3, $data['hydra:totalItems']);
        $this->assertCollectionKeyContains(
            $data['hydra:member'],
            'name',
            ['Médecine Générale', 'Anatomie Et Cytologie Pathologiques', 'Allergologie']
        );

        $specialty = $this->getSpecialty();
        $this->assertNotNull($specialty);

        $data = $this->get("specialties/{$specialty->getId()}/similar");
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertEquals(3, $data['hydra:totalItems']);
        $this->assertCollectionKeyContains(
            $data['hydra:member'],
            'name',
            ['Stomatologie', 'Anatomie Et Cytologie Pathologiques', 'Allergologie']
        );
    }

    /**
     * @group
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testGetSpecialtiesSortedByRppsCount(): void
    {
        $data = $this->get('specialties', ['by_rpps' => 'true', '_per_page' => 10]);
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $previousCount = null;

        foreach ($data['hydra:member'] as $specialty) {
            $rppsCount = $this->em->getRepository(RPPS::class)->getNbRppsForSpecialty($specialty['id']);

            // Afficher le nom de la spécialité et le nombre de médecins
            // $specialtyName = $specialty['name'];
            // dump("Specialty: {$specialtyName}, RPPS Count: {$rppsCount}");

            // Si ce n'est pas la première itération, vérifier que le nombre est décroissant
            if (null !== $previousCount) {
                $this->assertGreaterThanOrEqual($rppsCount, $previousCount);
            }

            $previousCount = $rppsCount;
        }
    }
}
