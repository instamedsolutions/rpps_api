<?php

namespace App\Tests\Functional;

use Symfony\Component\HttpFoundation\Response;
use \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;


/**
 * @group
 */
class SpecialtyTest extends ApiTestCase
{

    /**
     * @group
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testGetSpecialtyData(): void
    {
        $data = $this->get("specialties");
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertCollectionKeyContains(
            $data['hydra:member'],
            "name",
            ["Allergologie", "Anatomie Et Cytologie Pathologiques", "Biologie", "Cardiologie"]
        );
        $this->assertCollectionKeyContains($data['hydra:member'], "specialistName", ["Cardiologue"]);
        $this->assertCollectionKeyContains($data['hydra:member'], "canonical", ["chirurgie-general"]);
    }

    /**
     * @group
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testGetSpecialtyByName(): void
    {
        $data = $this->get("specialties", ["name" => "Cardiologie"]);
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertEquals(1, count($data['hydra:member']));
        $this->assertCollectionKeyContains($data['hydra:member'], "name", ["Cardiologie"]);

        $data = $this->get("specialties", ["name" => "chirurgie"]);
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertCollectionKeyContains($data['hydra:member'], "name", ["Neuro-Chirurgie", "Chirurgien-Dentiste", "Chirurgie Vasculaire"]);
    }

    /**
     * @group
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testGetSimilarSpecialties(): void
    {
        $specialty = $this->getSpecialty("hematologie");
        $this->assertNotNull($specialty);

        $data = $this->get("specialties/{$specialty->getId()}/similar");
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertEquals(3, $data['hydra:totalItems']);
        $this->assertCollectionKeyContains($data['hydra:member'], "name", ["Médecine Générale", "Anatomie Et Cytologie Pathologiques", "Allergologie"]);


        $specialty = $this->getSpecialty();
        $this->assertNotNull($specialty);

        $data = $this->get("specialties/{$specialty->getId()}/similar");
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertEquals(3, $data['hydra:totalItems']);
        $this->assertCollectionKeyContains($data['hydra:member'], "name", ["Stomatologie", "Anatomie Et Cytologie Pathologiques", "Allergologie"]);
    }
}
